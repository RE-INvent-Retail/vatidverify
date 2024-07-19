<?php

namespace VatValidate;

abstract class AbstractProvider
{
    private string $requesterVatId = '';
    private string $vatId = '';
    private string $companyName = '';
    private string $companyType = '';
    private string $street = '';
    private string $postcode = '';
    private string $city = '';
    private bool $testServiceActive = false;
    private bool $activeCountryCheck = true;
    private bool $getResponse = false;

    /**
     * @return bool|Response
     */
    abstract public function simpleValidate() : bool|Response;

    /**
     * @return Response
     */
    abstract public function qualifiedValidate() : Response;

    public function getRequesterVatId(): string
    {
        return $this->requesterVatId;
    }

    public function setRequesterVatId(string $requesterVatId): void
    {
        $this->requesterVatId = $requesterVatId;
    }

    public function getVatId(): string
    {
        return $this->vatId;
    }

    public function setVatId(string $vatId): void
    {
        $this->vatId = $vatId;
    }

    public function getCompanyName(): string
    {
        return $this->companyName;
    }

    public function setCompanyName(string $companyName): void
    {
        $this->companyName = $companyName;
    }

    public function getCompanyType(): string
    {
        return $this->companyType;
    }

    public function setCompanyType(string $companyType): void
    {
        $this->companyType = $companyType;
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function setStreet(string $street): void
    {
        $this->street = $street;
    }

    public function getPostcode(): string
    {
        return $this->postcode;
    }

    public function setPostcode(string $postcode): void
    {
        $this->postcode = $postcode;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): void
    {
        $this->city = $city;
    }

    public function setTestServiceActive(bool $testServiceActive = true): void
    {
        $this->testServiceActive = $testServiceActive;
    }

    public function isTestServiceActive(): bool
    {
        return $this->testServiceActive;
    }

    public function isActiveCountryCheck(): bool
    {
        return $this->activeCountryCheck;
    }

    public function setActiveCountryCheck(bool $activeCountryCheck): void
    {
        $this->activeCountryCheck = $activeCountryCheck;
    }

    public function setGetResponse(bool $getResponse): void
    {
        $this->getResponse = $getResponse;
    }

    public function isGetResponse(): bool
    {
        return $this->getResponse;
    }
}