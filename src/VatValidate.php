<?php

namespace VatValidate;

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
     * @param string $vatId
     * @param string $requesterVatId Only required in EVatR provider.
     * @param bool $skipCountryValidation
     * @param bool $getResponse If set to true, return response object instead of bool.
     * @return bool|Response
     */
    public function simpleValidate(string $vatId,
                                   string $requesterVatId = '',
                                   bool $skipCountryValidation = false,
                                   bool $getResponse = false): bool|Response
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
     * @param string $vatId
     * @param string $requesterVatId
     * @param string $companyName
     * @param string $companyStreet
     * @param string $companyPostcode
     * @param string $companyCity
     * @param string $companyType Optional, only important for vies provider.
     * @param bool $skipCountryValidation
     * @return Response
     */
    public function qualifiedValidation(string $vatId,
                                        string $requesterVatId,
                                        string $companyName,
                                        string $companyStreet,
                                        string $companyPostcode,
                                        string $companyCity,
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