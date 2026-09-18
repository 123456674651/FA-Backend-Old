<?php

namespace App\Services;

use Mpdf\Language\LanguageToFont;

class CustomLanguageToFont extends LanguageToFont
{
    /**
     * Override font mapping for Gujarati and Indic languages.
     *
     * @param string $llcc Language tag (e.g. 'gu', 'hi', 'en')
     * @param bool $adobeCJK
     * @return array [bool $coreSuitable, string $unifont]
     */
    public function getLanguageOptions($llcc, $adobeCJK)
    {
        $tags = explode('-', $llcc);
        $lang = strtolower($tags[0]);

        // Map Gujarati Unicode text to 'lmg-arun' / 'shruti' font instead of mPDF's default 'freeserif'
        if ($lang === 'gu' || $lang === 'guj') {
            return [false, 'lmg-arun'];
        }

        // Map Hindi Unicode text to 'mangal' / 'nirmala'
        if ($lang === 'hi' || $lang === 'hin') {
            return [false, 'mangal'];
        }

        return parent::getLanguageOptions($llcc, $adobeCJK);
    }
}
