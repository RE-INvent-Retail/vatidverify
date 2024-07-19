<?php

namespace VatValidate\Helper;

class CountryCheck
{
    public static function isValidPattern(string $vatId, string $country) : bool {
        if (!array_key_exists($country, self::getCountriesPatterns())) {
            return false;
        }
        $pattern = self::getCountriesPatterns()[$country];
        return preg_match( '/^(' . $pattern . ')$/', $vatId ) === 1;
    }

    private static function getCountriesPatterns() : array
    {
        $d9 = '\d{9}';
        $d8 = '\d{8}';
        $d11 = '\d{11}';
        return [
            'AT' => 'U[A-Z\d]{8}',
            'BE' => '[01]{1}'.$d9,
            'BG' => '\d{9,10}',
            'CH' => 'E(-| ?)(\d{3}(\.)\d{3}(\.)\d{3}|\d{9})( ?)(MWST|TVA|IVA)?',
            'CY' => $d8.'[A-Z]',
            'CZ' => '\d{8,10}',
            'DE' => $d9,
            'DK' => '(\d{2} ?){3}\d{2}',
            'EE' => $d9,
            'EL' => $d9,
            'ES' => '[A-Z]\d{7}[A-Z]|\d{8}[A-Z]|[A-Z]' . $d8,
            'FI' => $d8,
            'FR' => '([A-Z0-9]{2})\d{9}',
            'GB' => $d9.'|\d{12}|(GD|HA)\d{3}',
            'HR' => $d11,
            'HU' => $d8,
            'IE' => '[A-Z\d]{8}|[A-Z\d]{9}',
            'IT' => $d11,
            'LT' => '(\d{9}|\d{12})',
            'LU' => $d8,
            'LV' => $d11,
            'MT' => $d8,
            'NL' => $d9.'B\d{2}',
            'NO' => $d9.'(MVA){0,1}',
            'PL' => '\d{10}',
            'PT' => $d9,
            'RO' => '\d{2,10}',
            'SE' => '\d{12}',
            'SI' => $d8,
            'SK' => '\d{10}',
            'XI' => '\d{9,12}|(GD|HA)\d{3}'
        ];
    }
}