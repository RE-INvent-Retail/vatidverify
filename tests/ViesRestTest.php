<?php
declare (strict_types=1);

use PHPUnit\Framework\TestCase;
use VatValidate\Exceptions\RequestErrorException;
use VatValidate\Response;
use VatValidate\VatValidate;

class ViesRestTest extends TestCase
{
    /**
     * @var VatValidate
     */
    private VatValidate $vatValidate;
    private string $vatId = 'DE100';
    private string $failedVatId = 'DE200';

    protected function setUp(): void
    {
        parent::setUp();
        $this->vatValidate = new VatValidate(VatValidate::PROVIDER_VIES_REST, true);
    }

    /**
     * @return void
     */
    public function testSuccessVatNumberValidation()
    {
        $response = $this->vatValidate->simpleValidate($this->vatId, '', true);
        $this->assertTrue($response);
    }

    /**
     * @return void
     */
    public function testSuccessVatNumberValidationResponse()
    {
        $response = $this->vatValidate->simpleValidate($this->vatId, '', true, true);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->isValid());
    }

    /**
     * @return void
     */
    public function testSuccessQualifiedVatNumberValidation()
    {
        $response = $this->vatValidate->qualifiedValidation(
            $this->vatId,
            $this->vatId,
            'John Doe',
            '123 Main St',
            '1000',
            'Anytown',
            '000',
            true
        );

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->isValid());
        $this->assertTrue($response->getMatchCompanyName());
        $this->assertTrue($response->getMatchCompanyStreet());
        $this->assertTrue($response->getMatchCompanyZipCode());
        $this->assertTrue($response->getMatchCompanyCity());
        $this->assertNotEmpty($response->getRawResponse());
        $this->assertSame($this->vatId, $response->getVatId());
    }

    /**
     * @return void
     */
    public function testFailureVatNumberValidation()
    {
        $response = $this->vatValidate->simpleValidate($this->failedVatId, '', true);
        $this->assertFalse($response);
    }

    /**
     * @return void
     */
    public function testFailureQualifiedVatNumberValidation()
    {
        $response = $this->vatValidate->qualifiedValidation(
            $this->failedVatId,
            $this->failedVatId,
            'Test',
            'Test street',
            '123456',
            'Test city',
            '',
            true
        );

        $this->assertInstanceOf(Response::class, $response);
        $this->assertFalse($response->isValid());
        $this->assertNull($response->getMatchCompanyName());
        $this->assertNull($response->getMatchCompanyStreet());
        $this->assertNull($response->getMatchCompanyZipCode());
        $this->assertNull($response->getMatchCompanyCity());
        $this->assertSame(201, $response->getResponseCode());
    }

    /**
     * @return void
     * @throws RequestErrorException
     * @throws \VatValidate\Exceptions\InvalidArgumentException
     */
    public function testExceptionIsRaisedForRequiredValues()
    {
        $this->expectException(\VatValidate\Exceptions\InvalidArgumentException::class);
        (new \VatValidate\Provider\ViesRest())->simpleValidate();
    }

    /**
     * @return void
     */
    public function testExceptionIsRaisedForRequest()
    {
        $this->expectException(RequestErrorException::class);
        $this->vatValidate->simpleValidate('AB123456789', '', true);
    }

    public function testSuccessCheckStatus()
    {
        $result = $this->vatValidate->getViesCountryAvailability();
        $this->assertIsArray($result);
    }
}