<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;
class LibreOfficeController extends Controller
{
    public function convertWordToPdf()
{
    
    // $result = Process::run('ls -la');
    
    // print_r($result->output());
    
    // die;
    // Input DOCX file
    $inputPath = storage_path('app/outputs/filled_template2_shaurbh_mistra_20251118_175308.docx');

    // dd($inputPath);
    // Output folder
    $outputDir = storage_path('app/pdfs');

    if (!is_dir($outputDir)) {
        mkdir($outputDir, 0777, true);
    }

    // Build command for Windows
    $soffice = '"/home/u271047674/domains/gnhub.net/public_html/liberOffice/LibreOfficePortablePrevious.exe"';
    // dd($soffice);
    $command = $soffice . ' --headless --convert-to pdf --outdir "' . $outputDir . '" "' . $inputPath . '"';

    $result = Process::fromShellCommandline($command);

$result->start();

dd($result);
     print_r($result->output());

die;


    // Execute conversion
    exec($command, $output, $returnCode);
    
    exec("ls -la", $output, $returnCode);
    
    print_r($output);

    if ($returnCode !== 0) {
        return response()->json([
            'status' => false,
            'message' => 'Conversion failed',
            'output' => $output,
            'return' => $returnCode

        ]);
    }

    // Converted PDF file path
    // $pdfFile = $outputDir . '/' . pathinfo($inputPath, PATHINFO_FILENAME) . '.pdf';

    // return response()->download($pdfFile);
}
    
}
