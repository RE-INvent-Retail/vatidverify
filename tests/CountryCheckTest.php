<?php

use PHPUnit\Framework\TestCase;

class CountryCheckTest extends TestCase
{
    public function testCountryFailure()
    {
        // not valid country
        $result = \VatValidate\Helper\CountryCheck::isValidPattern('123456789', 'AA');
        $this->assertFalse($result);
        // not valid pattern
        $result = \VatValidate\Helper\CountryCheck::isValidPattern('123456', 'DE');
        $this->assertFalse($result);
    }

    public function testCountrySuccess()
    {
        $result = \VatValidate\Helper\CountryCheck::isValidPattern('123456789', 'DE');
        $this->assertTrue($result);
    }
}