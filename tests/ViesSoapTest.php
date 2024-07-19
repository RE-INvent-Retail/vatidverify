<?php
declare (strict_types=1);

use PHPUnit\Framework\TestCase;
use VatValidate\Response;
use VatValidate\VatValidate;

class ViesSoapTest extends TestCase
{
    private VatValidate $vatValidate;
    private string $vatId = 'DE131832937';
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
     */
    public function testExceptionIsRaisedForRequest()
    {
        $this->expectException(\VatValidate\Exceptions\RequestErrorException::class);
        $this->vatValidate->simpleValidate('AB123456789', '', true);
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
}