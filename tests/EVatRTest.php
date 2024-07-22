<?php
declare (strict_types=1);

use PHPUnit\Framework\TestCase;
use VatValidate\Response;
use VatValidate\VatValidate;

class EVatRTest extends TestCase
{
    private VatValidate $vatValidate;
    private string $vatId = 'DE131832937';
    private string $failedVatId = 'DE0123.ABC.456';
    private string $requesterVatId = 'DE309928677';
    protected function setUp(): void
    {
        parent::setUp();
        $this->vatValidate = new VatValidate(VatValidate::PROVIDER_EVATR);
    }

    public function testExceptionIsRaisedForRequiredValues()
    {
        $this->expectException(\VatValidate\Exceptions\InvalidArgumentException::class);
        (new \VatValidate\Provider\EVatR())->simpleValidate();
    }

    public function testFailureVatNumberValidation()
    {
        $response = $this->vatValidate->simpleValidate($this->failedVatId, $this->requesterVatId);
        $this->assertFalse($response);
    }

    public function testFailureQualifiedVatNumberValidation()
    {
        $response = $this->vatValidate->qualifiedValidation(
            $this->failedVatId,
            $this->failedVatId,
            'Test',
            'Test street',
            '123456',
            'Test city'
        );

        $this->assertInstanceOf(Response::class, $response);
        $this->assertFalse($response->isValid());
        $this->assertSame(201, $response->getResponseCode());
        $this->assertSame($this->failedVatId, $response->getVatId());
        $this->assertSame($this->failedVatId, $response->getRequesterVatId());
    }

    public function testSimpleValidation()
    {
        $response = $this->vatValidate->simpleValidate($this->vatId, $this->requesterVatId, false, true);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->isValid());
    }
    public function testQualifiedValidation()
    {
        $company = 'Conrad Electronic SE';
        $response = $this->vatValidate->qualifiedValidation(
            $this->vatId,
            $this->requesterVatId,
            $company,
        'Klaus-Conrad-Str. 1',
        '92242',
        'Hirschau');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->isValid());
        $this->assertTrue($response->getMatchCompanyName());
        $this->assertTrue($response->getMatchCompanyStreet());
        $this->assertTrue($response->getMatchCompanyZipCode());
        $this->assertTrue($response->getMatchCompanyCity());
        $this->assertNotEmpty($response->getRawResponse());
        $this->assertSame('The requested VAT ID is valid.', \VatValidate\Helper\EVatRResponse::getResponseCodeTexts()[$response->getResponseCode()]);
        $this->assertSame($this->vatId, $response->getVatId());
        $this->assertSame($company, $response->getCompanyName());
        $this->assertSame('Klaus-Conrad-Str. 1 92242 Hirschau', $response->getCompanyAddress());
    }
}