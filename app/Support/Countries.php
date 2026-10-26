<?php

namespace App\Support;

/** Countries of residence offered to overseas citizens (ISO code => English, Urdu). */
class Countries
{
    public const ALL = [
        'GB' => ['United Kingdom', 'برطانیہ'],
        'SA' => ['Saudi Arabia', 'سعودی عرب'],
        'AE' => ['United Arab Emirates', 'متحدہ عرب امارات'],
        'QA' => ['Qatar', 'قطر'],
        'KW' => ['Kuwait', 'کویت'],
        'OM' => ['Oman', 'عمان'],
        'BH' => ['Bahrain', 'بحرین'],
        'US' => ['United States', 'امریکہ'],
        'CA' => ['Canada', 'کینیڈا'],
        'AU' => ['Australia', 'آسٹریلیا'],
        'NZ' => ['New Zealand', 'نیوزی لینڈ'],
        'IE' => ['Ireland', 'آئرلینڈ'],
        'DE' => ['Germany', 'جرمنی'],
        'FR' => ['France', 'فرانس'],
        'IT' => ['Italy', 'اٹلی'],
        'ES' => ['Spain', 'اسپین'],
        'GR' => ['Greece', 'یونان'],
        'BE' => ['Belgium', 'بیلجیم'],
        'NL' => ['Netherlands', 'نیدرلینڈز'],
        'NO' => ['Norway', 'ناروے'],
        'SE' => ['Sweden', 'سویڈن'],
        'DK' => ['Denmark', 'ڈنمارک'],
        'CH' => ['Switzerland', 'سوئٹزرلینڈ'],
        'AT' => ['Austria', 'آسٹریا'],
        'PT' => ['Portugal', 'پرتگال'],
        'TR' => ['Turkey', 'ترکی'],
        'MY' => ['Malaysia', 'ملائیشیا'],
        'JP' => ['Japan', 'جاپان'],
        'KR' => ['South Korea', 'جنوبی کوریا'],
        'CN' => ['China', 'چین'],
        'HK' => ['Hong Kong', 'ہانگ کانگ'],
        'ZA' => ['South Africa', 'جنوبی افریقہ'],
        'XX' => ['Other country', 'کوئی اور ملک'],
    ];

    public static function codes(): array
    {
        return array_keys(self::ALL);
    }

    public static function name(?string $code, ?string $locale = null): ?string
    {
        $row = self::ALL[$code] ?? null;

        if (! $row) {
            return $code;
        }

        return ($locale ?? app()->getLocale()) === 'ur' ? $row[1] : $row[0];
    }

    /** @return array<string, string> code => name in the current locale, in display order */
    public static function options(): array
    {
        return collect(self::ALL)->map(fn ($row, $code) => self::name($code))->all();
    }
}
