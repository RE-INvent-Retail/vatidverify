<?php

namespace VatValidate;

use VatValidate\Exceptions\InvalidArgumentException;
use VatValidate\Exceptions\RequestErrorException;

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
     * @throws InvalidArgumentException
     * @throws RequestErrorException
     */
    abstract public function simpleValidate() : bool|Response;

    /**
     * @return Response
     * @throws InvalidArgumentException
     * @throws RequestErrorException
     */
    abstract public function qualifiedValidate() : Response;

    /**
     * @return string
     */
    public function getRequesterVatId(): string
    {
        return $this->requesterVatId;
    }

    /**
     * @param string $requesterVatId
     * @return void
     */
    public function setRequesterVatId(string $requesterVatId): void
    {
        $this->requesterVatId = $requesterVatId;
    }

    /**
     * @return string
     */
    public function getVatId(): string
    {
        return $this->vatId;
    }

    /**
     * @param string $vatId
     * @return void
     */
    public function setVatId(string $vatId): void
    {
        $this->vatId = $vatId;
    }

    /**
     * @return string
     */
    public function getCompanyName(): string
    {
        return $this->companyName;
    }

    /**
     * @param string $companyName
     * @return void
     */
    public function setCompanyName(string $companyName): void
    {
        $this->companyName = $companyName;
    }

    /**
     * @return string
     */
    public function getCompanyType(): string
    {
        return $this->companyType;
    }

    /**
     * @param string $companyType
     * @return void
     */
    public function setCompanyType(string $companyType): void
    {
        $this->companyType = $companyType;
    }

    /**
     * @return string
     */
    public function getStreet(): string
    {
        return $this->street;
    }

    /**
     * @param string $street
     * @return void
     */
    public function setStreet(string $street): void
    {
        $this->street = $street;
    }

    /**
     * @return string
     */
    public function getPostcode(): string
    {
        return $this->postcode;
    }

    /**
     * @param string $postcode
     * @return void
     */
    public function setPostcode(string $postcode): void
    {
        $this->postcode = $postcode;
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * @param string $city
     * @return void
     */
    public function setCity(string $city): void
    {
        $this->city = $city;
    }

    /**
     * @param bool $testServiceActive
     * @return void
     */
    public function setTestServiceActive(bool $testServiceActive = true): void
    {
        $this->testServiceActive = $testServiceActive;
    }

    /**
     * @return bool
     */
    public function isTestServiceActive(): bool
    {
        return $this->testServiceActive;
    }

    /**
     * @return bool
     */
    public function isActiveCountryCheck(): bool
    {
        return $this->activeCountryCheck;
    }

    /**
     * @param bool $activeCountryCheck
     * @return void
     */
    public function setActiveCountryCheck(bool $activeCountryCheck): void
    {
        $this->activeCountryCheck = $activeCountryCheck;
    }

    /**
     * @param bool $getResponse
     * @return void
     */
    public function setGetResponse(bool $getResponse): void
    {
        $this->getResponse = $getResponse;
    }

    /**
     * @return bool
     */
    public function isGetResponse(): bool
    {
        return $this->getResponse;
    }
}