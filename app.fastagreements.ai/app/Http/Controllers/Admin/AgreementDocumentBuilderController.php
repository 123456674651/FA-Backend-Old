<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Mpdf\Mpdf;
use Barryvdh\DomPDF\Facade\Pdf as DomPDF;

class AgreementDocumentBuilderController extends Controller
{
    /**
     * Show CKEditor page.
     */
    public function index(Request $request)
    {
        return view('admin.agreement_builder.index');
    }

    /**
     * Generate PDF Preview from CKEditor HTML content using mPDF (Full Unicode & Indic script support).
     */
    public function previewPdf(Request $request)
    {
        $htmlContent = $request->input('content', '');

        if (trim($htmlContent) === '') {
            $htmlContent = '<div style="text-align:center; color:#666; margin-top:50px;"><h3>Document is empty</h3><p>Please type your agreement content in the CKEditor.</p></div>';
        }

        $fullHtml = $this->wrapInPdfLayout($htmlContent);

        try {
            $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
            $fontDirs = $defaultConfig['fontDir'];

            $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
            $fontData = $defaultFontConfig['fontdata'];

            $mpdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'margin_left' => 15,
                'margin_right' => 15,
                'margin_top' => 15,
                'margin_bottom' => 15,
                'autoScriptToLang' => true,
                'autoLangToFont' => true,
                'useOTL' => 0xFF, // Critical for Indic script (Gujarati/Hindi) conjuncts
                'useKashida' => 75,
                'languageToFont' => new \App\Services\CustomLanguageToFont(),
                'tempDir' => storage_path('app/tmp'),
                'fontDir' => array_merge($fontDirs, [
                    public_path('fonts'),
                ]),
                'fontdata' => $fontData + [
                    'times new roman' => [
                        'R' => 'times.ttf',
                        'B' => 'timesbd.ttf',
                        'I' => 'timesi.ttf',
                        'BI' => 'timesbi.ttf',
                    ],
                    'times' => [
                        'R' => 'times.ttf',
                        'B' => 'timesbd.ttf',
                        'I' => 'timesi.ttf',
                        'BI' => 'timesbi.ttf',
                    ],
                    'timesnewroman' => [
                        'R' => 'times.ttf',
                        'B' => 'timesbd.ttf',
                        'I' => 'timesi.ttf',
                        'BI' => 'timesbi.ttf',
                    ],
                    'mangal' => [
                        'R' => 'Nirmala.ttf',
                        'B' => 'NirmalaB.ttf',
                    ],
                    'nirmala' => [
                        'R' => 'Nirmala.ttf',
                        'B' => 'NirmalaB.ttf',
                    ],
                    'lmg-arun' => [
                        'R' => 'shruti.ttf',
                        'B' => 'shrutib.ttf',
                    ],
                    'lmg arun' => [
                        'R' => 'shruti.ttf',
                        'B' => 'shrutib.ttf',
                    ],
                    'lmgarun' => [
                        'R' => 'shruti.ttf',
                        'B' => 'shrutib.ttf',
                    ],
                    'shruti' => [
                        'R' => 'shruti.ttf',
                        'B' => 'shrutib.ttf',
                    ],
                    'notosansgujarati' => [
                        'R' => 'NotoSansGujarati-Regular.ttf',
                    ],
                ],
                'default_font' => 'times'
            ]);

            $mpdf->WriteHTML($fullHtml);
            $pdfOutput = $mpdf->Output('', 'S');

            return response($pdfOutput, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="document_preview.pdf"');
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'PDF Generation Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Wrap raw HTML into A4 PDF layout.
     */
    private function wrapInPdfLayout(string $content): string
    {
        return '<!DOCTYPE html>
<html lang="gu">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Agreement Document</title>
    <style>
        body {
            font-size: 12pt;
            line-height: 1.8;
            color: #111827;
        }
        h1, h2, h3, h4, h5, h6 {
            margin-top: 12px;
            margin-bottom: 12px;
        }
        p {
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 15px;
        }
        table, th, td {
            border: 1px solid #94a3b8;
        }
        th, td {
            padding: 6px 10px;
            text-align: left;
            vertical-align: top;
        }
    </style>
</head>
<body>
' . $content . '
</body>
</html>';
    }
}
