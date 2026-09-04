<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;
use ZipArchive;

/**
 * Turns whatever Word file an admin uploads into a real .docx.
 *
 * Everything downstream of a template upload assumes OOXML: DocumentController
 * reads placeholders out of `word/document.xml` with ZipArchive, the admin
 * preview parses the same XML, and PhpWordController::assertValidDocx refuses
 * to render an agreement from anything that is not a zip. A Word 97-2003 .doc
 * is an OLE2 binary, so storing one under a .docx name — which is what the
 * upload used to do — produced a template that extracted zero variables,
 * previewed blank, and blew up with "ZipArchive error: 19" at generation time.
 *
 * PhpWord ships an MsDoc reader, but round-tripping these templates through it
 * mangles the text into byte-swapped UTF-16 and loses every placeholder, so
 * conversion goes through LibreOffice — the same binary the agreement PDF
 * export already relies on.
 */
class WordDocumentConverter
{
    /**
     * Long enough for LibreOffice's first-run start-up on a cold server,
     * short enough that a wedged conversion cannot hold a web worker open.
     */
    private const TIMEOUT_SECONDS = 120;

    private readonly ?string $binary;

    public function __construct(?string $binary = null)
    {
        $binary ??= config('services.libreoffice.binary');

        $this->binary = is_string($binary) && $binary !== '' ? $binary : null;
    }

    /**
     * Is this already the OOXML container the rest of the pipeline needs?
     *
     * The `word/document.xml` check mirrors PhpWordController::assertValidDocx —
     * a plain zip that happens to be named .docx must not slip through here and
     * fail later, in the middle of a customer's agreement.
     */
    public function isDocx(string $path): bool
    {
        if (! is_file($path)) {
            return false;
        }

        $zip = new ZipArchive();

        if ($zip->open($path) !== true) {
            return false;
        }

        $hasDocumentXml = $zip->locateName('word/document.xml') !== false;
        $zip->close();

        return $hasDocumentXml;
    }

    /**
     * A path to a valid .docx for this upload, converting a legacy .doc first.
     *
     * When the source is already OOXML the same path comes back, so callers
     * must pair this with discard() rather than assuming a temporary file.
     */
    public function ensureDocx(string $path): string
    {
        return $this->isDocx($path) ? $path : $this->convertToDocx($path);
    }

    /**
     * @throws RuntimeException when LibreOffice is unavailable or the upload is
     *                          not a Word document it can read
     */
    public function convertToDocx(string $sourcePath): string
    {
        if (! is_file($sourcePath)) {
            throw new RuntimeException('The uploaded document could not be read from disk.');
        }

        // LibreOffice's plain-text fallback filter will "convert" literally any
        // file it is handed, so a corrupt upload would otherwise become a
        // well-formed .docx full of mojibake and quietly replace a working
        // template. Only formats Word itself writes get past here.
        if (! $this->isLegacyWordDocument($sourcePath)) {
            throw new RuntimeException(
                'This file could not be read as a Word document. '
                . 'Open it in Word, choose File > Save As > Word Document (.docx), and upload that file.'
            );
        }

        if ($this->binary === null || ! is_file($this->binary)) {
            throw new RuntimeException(
                'This is an older Word (.doc) file and the server cannot convert it right now. '
                . 'Open it in Word, choose File > Save As > Word Document (.docx), and upload that file.'
            );
        }

        $workDir = storage_path('app/doc-conversions/' . Str::uuid());
        File::ensureDirectoryExists($workDir, 0775, true);

        // LibreOffice keys its lock file to the user profile, so two admins
        // uploading at the same moment would otherwise have one conversion
        // silently refused. A profile per run keeps them independent.
        $process = new Process([
            $this->binary,
            '--headless',
            '--nologo',
            '--nofirststartwizard',
            '-env:UserInstallation=' . self::profileUri($workDir . '/profile'),
            '--convert-to',
            'docx:MS Word 2007 XML',
            '--outdir',
            $workDir,
            $sourcePath,
        ]);
        $process->setTimeout(self::TIMEOUT_SECONDS);
        $process->run();

        $converted = $workDir . DIRECTORY_SEPARATOR . pathinfo($sourcePath, PATHINFO_FILENAME) . '.docx';

        // LibreOffice reports success even when it declines to convert, so the
        // output file — not the exit code — is what decides.
        if (! $this->isDocx($converted)) {
            Log::warning('Legacy .doc to .docx conversion failed.', [
                'source' => $sourcePath,
                'exit_code' => $process->getExitCode(),
                'output' => trim($process->getOutput() . "\n" . $process->getErrorOutput()),
            ]);

            File::deleteDirectory($workDir);

            throw new RuntimeException(
                'This file could not be read as a Word document. '
                . 'Open it in Word, choose File > Save As > Word Document (.docx), and upload that file.'
            );
        }

        return $converted;
    }

    /**
     * The two containers a .doc from Word can actually be: an OLE2 compound
     * file (Word 97-2003) or RTF, which Word also writes under a .doc name.
     */
    private function isLegacyWordDocument(string $path): bool
    {
        $handle = @fopen($path, 'rb');

        if ($handle === false) {
            return false;
        }

        $header = (string) fread($handle, 8);
        fclose($handle);

        return str_starts_with($header, "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1")
            || str_starts_with($header, '{\rtf');
    }

    /**
     * Clean up after ensureDocx(). Files this class did not create are left
     * alone, so passing an untouched upload path through is harmless.
     */
    public function discard(string $path): void
    {
        // storage_path() mixes separators on Windows, so compare normalised.
        $normalise = static fn (string $value): string => rtrim(str_replace('\\', '/', $value), '/');

        $root = $normalise(storage_path('app/doc-conversions'));
        $directory = $normalise(dirname($path));

        if ($directory !== $root && str_starts_with($directory, $root . '/')) {
            File::deleteDirectory($directory);
        }
    }

    /**
     * LibreOffice wants the profile as a file:// URI on every platform, and on
     * Windows that means forward slashes behind a third slash: file:///C:/...
     */
    public static function profileUri(string $absolutePath): string
    {
        return 'file:///' . ltrim(str_replace('\\', '/', $absolutePath), '/');
    }
}
