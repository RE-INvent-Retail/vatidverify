<?php
declare (strict_types=1);

use PHPUnit\Framework\TestCase;
use VatValidate\Response;
use VatValidate\VatValidate;

class ViesSoapTest extends TestCase
{
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
     * @throws \DragonBe\Vies\ViesException
     * @throws \DragonBe\Vies\ViesServiceException
     * @throws \VatValidate\Exceptions\InvalidArgumentException
     */
    public function testExceptionIsRaisedForRequiredValues()
    {
        $this->expectException(\VatValidate\Exceptions\InvalidArgumentException::class);
        (new \VatValidate\Provider\ViesSoap())->simpleValidate();
    }

    /**
     * @return void
     */
    public function testFailureVatNumberValidation()
    {
        $response = $this->vatValidate->simpleValidate($this->failedVatId, '', false,true);
        $this->assertFalse($response);
    }

    /**
     * @return void
     */
    public function testFailureQualifiedVatNumberValidation()
    {
        $response = $this->vatValidate->qualifiedValidation(
            $this->failedVatId,
            'Test',
            'Test city',
            'Test street',
            '123456',
            $this->vatId,
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
     */
    public function testExceptionIsRaisedForRequest()
    {
        $this->expectException(\VatValidate\Exceptions\RequestErrorException::class);
        $this->vatValidate->simpleValidate('AB123456789', '', false,true);
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
}