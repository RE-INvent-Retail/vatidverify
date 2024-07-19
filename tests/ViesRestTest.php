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
    private string $vatId = 'DE131832937';

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
        // catch if test service is unavailable
        try {
            $response = $this->vatValidate->simpleValidate($this->vatId);
        } catch (\Throwable $th) {
            $this->vatValidate->setService(false);
            $response = $this->vatValidate->simpleValidate($this->vatId);
        }

        $this->assertTrue($response);
    }

    /**
     * @return void
     */
    public function testSuccessVatNumberValidationResponse()
    {
        // catch if test service is unavailable
        try {
            $response = $this->vatValidate->simpleValidate($this->vatId, '', false, true);
        } catch (\Throwable $th) {
            $this->vatValidate->setService(false);
            $response = $this->vatValidate->simpleValidate($this->vatId, '', false, true);
        }

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->isValid());
    }

    /**
     * @return void
     */
    public function testSuccessQualifiedVatNumberValidation()
    {
        // catch if test service is unavailable
        try {
            $response = $this->vatValidate->qualifiedValidation(
                $this->vatId,
                'DE309928677',
                'Conrad Electronic SE',
                'Klaus-Conrad-Str. 1',
                '92242',
                'Hirschau'
            );
        } catch (\Throwable $th) {
            $this->vatValidate->setService(false);
            $response = $this->vatValidate->qualifiedValidation(
                $this->vatId,
                'DE309928677',
                'Conrad Electronic SE',
                'Klaus-Conrad-Str. 1',
                '92242',
                'Hirschau'
            );
        }

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->isValid());
        $this->assertNull($response->getMatchCompanyName());
        $this->assertNull($response->getMatchCompanyStreet());
        $this->assertNull($response->getMatchCompanyZipCode());
        $this->assertNull($response->getMatchCompanyCity());
        $this->assertNotEmpty($response->getRawResponse());
        $this->assertSame($this->vatId, $response->getVatId());
    }

    /**
     * @return void
     */
    public function testFailureVatNumberValidation()
    {
        $response = $this->vatValidate->simpleValidate('DE0123.ABC.456');
        $this->assertFalse($response);
    }

    /**
     * @return void
     */
    public function testFailureQualifiedVatNumberValidation()
    {
        $response = $this->vatValidate->qualifiedValidation(
            'DE0123.ABC.456',
            'DE0123.ABC.456',
            'Test',
            'Test street',
            '123456',
            'Test city'
        );

        $this->assertInstanceOf(Response::class, $response);
        $this->assertFalse($response->isValid());
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