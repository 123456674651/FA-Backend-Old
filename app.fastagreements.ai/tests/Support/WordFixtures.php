<?php

namespace Tests\Support;

use App\Services\WordDocumentConverter;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use Symfony\Component\Process\Process;

/**
 * Builds real Word files for tests.
 *
 * A genuine Word 97-2003 binary cannot be hand-written and committing one as a
 * binary fixture hides what it contains, so the legacy .doc is produced by
 * round-tripping a generated .docx through LibreOffice — the same trip an
 * admin's template makes when they save an old format from Word.
 */
class WordFixtures
{
    public function __construct(private readonly string $workDir)
    {
        File::ensureDirectoryExists($this->workDir, 0775, true);
    }

    public function docx(string $text): string
    {
        $path = $this->workDir . '/' . uniqid('fixture_', false) . '.docx';

        $word = new PhpWord();
        $word->addSection()->addText($text);
        IOFactory::createWriter($word, 'Word2007')->save($path);

        return $path;
    }

    /**
     * @return string|null the .doc path, or null when LibreOffice is absent
     */
    public function legacyDoc(string $text): ?string
    {
        $binary = config('services.libreoffice.binary');

        if (! is_string($binary) || ! is_file($binary)) {
            return null;
        }

        $docx = $this->docx($text);
        $outDir = $this->workDir . '/legacy';
        File::ensureDirectoryExists($outDir, 0775, true);

        $process = new Process([
            $binary, '--headless', '--nologo', '--nofirststartwizard',
            '-env:UserInstallation=' . WordDocumentConverter::profileUri($outDir . '/profile'),
            '--convert-to', 'doc:MS Word 97', '--outdir', $outDir, $docx,
        ]);
        $process->setTimeout(180);
        $process->run();

        $legacy = $outDir . '/' . pathinfo($docx, PATHINFO_FILENAME) . '.doc';

        return is_file($legacy) ? $legacy : null;
    }

    public function cleanup(): void
    {
        File::deleteDirectory($this->workDir);
    }
}
