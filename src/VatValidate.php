<?php

namespace VatValidate;

use VatValidate\Exceptions\InvalidArgumentException;
use VatValidate\Exceptions\RequestErrorException;
use VatValidate\Provider\EVatR;
use VatValidate\Provider\ViesRest;
use VatValidate\Provider\ViesSoap;

class VatValidate
{
     const PROVIDER_VIES_SOAP = 'ViesSoap';
     const PROVIDER_VIES_REST = 'ViesRest';
     const PROVIDER_EVATR = 'EVatR';

     private array $providerClass = [
         self::PROVIDER_EVATR => EVatR::class,
         self::PROVIDER_VIES_SOAP => ViesSoap::class,
         self::PROVIDER_VIES_REST => ViesRest::class
     ];

     protected AbstractProvider $provider;
    public function __construct(string $provider = self::PROVIDER_VIES_SOAP, bool $useTestService = false)
    {
        $this->provider = new $this->providerClass[$provider];
        if ($useTestService) {
            $this->provider->setTestServiceActive();
        }
    }

    /**
     * Gets boolean or response for simple validation.
     * @param string $vatId
     * @param string $requesterVatId Only required in EVatR provider.
     * @param bool $getResponse If set to true, return response object instead of bool.
     * @param bool $skipCountryValidation
     * @return bool|Response
     * @throws InvalidArgumentException
     * @throws RequestErrorException
     */
    public function simpleValidate(string $vatId,
                                   string $requesterVatId = '',
                                   bool $getResponse = false,
                                   bool $skipCountryValidation = false): bool|Response
    {
        $this->provider->setVatId($vatId);
        $this->provider->setRequesterVatId($requesterVatId);
        if ($skipCountryValidation) {
            $this->provider->setActiveCountryCheck(false);
        }
        if ($getResponse) {
            $this->provider->setGetResponse(true);
        }
        return $this->provider->simpleValidate();
    }

    /**
     * Gets response for qualified validation.
     * @param string $vatId
     * @param string $companyName
     * @param string $companyCity Optional for EVatR, but recommended.
     * @param string $companyStreet
     * @param string $companyPostcode Optional for EVatR, but recommended.
     * @param string $requesterVatId
     * @param string $companyType Optional, only important for vies provider for some countries, e.g. spain or greek.
     * @param bool $skipCountryValidation
     * @return Response
     * @throws InvalidArgumentException
     * @throws RequestErrorException
     */
    public function qualifiedValidation(string $vatId,
                                        string $companyName,
                                        string $companyCity,
                                        string $companyStreet = '',
                                        string $companyPostcode = '',
                                        string $requesterVatId = '',
                                        string $companyType = '',
                                        bool $skipCountryValidation = false
    ): Response
    {
        $this->provider->setVatId($vatId);
        $this->provider->setRequesterVatId($requesterVatId);
        $this->provider->setCompanyName($companyName);
        $this->provider->setCompanyType($companyType);
        $this->provider->setStreet($companyStreet);
        $this->provider->setPostcode($companyPostcode);
        $this->provider->setCity($companyCity);
        if ($skipCountryValidation) {
            $this->provider->setActiveCountryCheck(false);
        }
        return $this->provider->qualifiedValidate();
    }

    /**
     * Sets whether a test service should be used.
     * NOTE: This service might not be available.
     * @param bool $useTestService
     * @return void
     */
    public function setService(bool $useTestService = true) : void
    {
        $this->provider->setTestServiceActive($useTestService);
    }

    /**
     * Gets availability of countries per country code if service available, otherwise returns an empty array.
     * * True for available, False for not available country and Null for disabled monitoring, example:
     * * ['AT' => true, ...]
     * @return array
     * @throws Exceptions\RequestErrorException
     */
    public function getViesCountryAvailability() : array
    {
        $provider = new ViesRest();
        return $provider->checkStatus();
    }
}