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
    public function __construct(string $provider = self::PROVIDER_VIES_SOAP)
    {
        $this->provider = new $this->providerClass[$provider];
    }

    public function simpleValidate(string $vatId, string $requesterVatId = '')
    {
        // todo what if service unavialable
        $this->provider->setVatId($vatId);
        $this->provider->setRequesterVatId($requesterVatId);
        return $this->provider->simpleValidate();
    }

    public function qualifiedValidation(string $vatId,
                                        string $requesterVatId,
                                        string $companyName,
                                        string $companyStreet,
                                        string $companyPostcode,
                                        string $companyCity,
                                        string $companyType = '',
    )
    {
        $this->provider->setVatId($vatId);
        $this->provider->setRequesterVatId($requesterVatId);
        $this->provider->setCompanyName($companyName);
        $this->provider->setCompanyType($companyType);
        $this->provider->setStreet($companyStreet);
        $this->provider->setPostcode($companyPostcode);
        $this->provider->setCity($companyCity);
        return $this->provider->qualifiedValidate();
    }
 }