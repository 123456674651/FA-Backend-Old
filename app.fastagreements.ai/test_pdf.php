<?php

require 'vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$htmlContent = '
<span style="font-family: LMG-Arun, Shruti, Noto Sans Gujarati, sans-serif;">આ એક ગુજરાતી ભાષાનું ટેસ્ટ છે. જોડાક્ષર: પ્રસ્તાવના, ક્ષત્રિય.</span>
<br>
<span style="font-family: Mangal, Nirmala, Devanagari, sans-serif;">यह एक हिंदी भाषा का परीक्षण है। युक्ताक्षर: प्रस्तावना, क्षत्रिय।</span>
<br>
<span style="font-family: Times New Roman, Times, serif;">This is an English test.</span>
';

try {
    $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
    $fontDirs = $defaultConfig['fontDir'];

    $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
    $fontData = $defaultFontConfig['fontdata'];

    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'autoScriptToLang' => true,
        'autoLangToFont' => true,
        'useOTL' => 0xFF,
        'useKashida' => 75,
        'languageToFont' => new \App\Services\CustomLanguageToFont(),
        'tempDir' => storage_path('app/tmp'),
        'fontDir' => array_merge($fontDirs, [
            public_path('fonts'),
        ]),
        'fontdata' => $fontData + [
            'times new roman' => ['R' => 'times.ttf', 'B' => 'timesbd.ttf', 'I' => 'timesi.ttf', 'BI' => 'timesbi.ttf'],
            'mangal' => ['R' => 'Nirmala.ttf', 'B' => 'NirmalaB.ttf'],
            'lmg-arun' => ['R' => 'shruti.ttf', 'B' => 'shrutib.ttf'],
            'shruti' => ['R' => 'shruti.ttf', 'B' => 'shrutib.ttf'],
            'notosansgujarati' => ['R' => 'NotoSansGujarati-Regular.ttf']
        ],
        'default_font' => 'times'
    ]);

    $mpdf->WriteHTML($htmlContent);
    $mpdf->Output(public_path('test_preview.pdf'), 'F');
    echo "PDF generated successfully at public/test_preview.pdf\n";
} catch (\Throwable $e) {
    echo "Error generating PDF: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
