<?php

namespace VatValidate;

use DragonBe\Vies\CheckVatResponse;
use VatValidate\Helper\EVatRResponse;

class Response
{
    /**
     * @var bool
     */
    private bool $valid;
    /**
     * @var string
     */
    private string $vatId;
    /**
     * @var string
     */
    private string $requesterVatId = '';
    /**
     * @var string
     */
    private string $companyName;
    /**
     * @var string
     */
    private string $companyAddress;
    /**
     * @var \DateTime
     */
    private \DateTime $requestDate;
    /**
     * @var bool|null
     */
    private ?bool $matchCompanyName;
    /**
     * @var bool|null
     */
    private ?bool $matchCompanyStreet;
    /**
     * @var bool|null
     */
    private ?bool $matchCompanyZipCode;
    /**
     * @var bool|null
     */
    private ?bool $matchCompanyCity;
    /**
     * @var int
     */
    private int $responseCode;
    /**
     * @var string
     */
    private string $raw;
    /**
     * @var array
     */
    private array $array;

    /**
     * Result mapping of vies matches.
     * @var array
     */
    private array $matchResult = [
        'VALID' => true,
        'INVALID' => false,
        'NOT_PROCESSED' => null
    ];
    public function __construct(CheckVatResponse $viesResponse = null, EVatRResponse $evatResponse = null)
    {
        if (!empty($viesResponse)) {
            $this->raw = json_encode($viesResponse->toArray());
            $this->valid = $viesResponse->isValid();
            $this->vatId = $viesResponse->getCountryCode() . $viesResponse->getVatNumber();
            $this->companyName = $viesResponse->getName();
            $this->companyAddress = $viesResponse->getAddress();
            $this->requestDate = $viesResponse->getRequestDate();
            $this->matchCompanyName = $this->matchConvertToBool($viesResponse->getNameMatch());
            $this->matchCompanyStreet = $this->matchConvertToBool($viesResponse->getStreetMatch());
            $this->matchCompanyZipCode = $this->matchConvertToBool($viesResponse->getPostcodeMatch());
            $this->matchCompanyCity = $this->matchConvertToBool($viesResponse->getCityMatch());
            $this->responseCode = $viesResponse->isValid() ? 200 : 201;
            $this->array = $viesResponse->toArray();
        } else {
            $this->raw = $evatResponse->getRaw();
            $this->valid = $evatResponse->isValid();
            $this->vatId = $evatResponse->getForeignVatId();
            $this->requesterVatId = $evatResponse->getOwnVatId();
            $this->companyName = $evatResponse->getCompanyName();
            $this->companyAddress = $evatResponse->getAddress();
            $this->requestDate = \DateTime::createFromFormat(
                'd.m.Y H:i:s',
                $evatResponse->getDate() . ' ' . $evatResponse->getTime());
            $this->matchCompanyName = $evatResponse->getResponseCompanyName();
            $this->matchCompanyStreet = $evatResponse->getResponseStreet();
            $this->matchCompanyZipCode = $evatResponse->getResponseZipCode();
            $this->matchCompanyCity = $evatResponse->getResponseCity();
            $this->responseCode = $evatResponse->getResponseCode();
            $this->array = $evatResponse->toArray();
        }
    }

    /**
     * Get source response as array. Keys might differ depending on source response.
     * @return array
     */
    public function getArray(): array
    {
        return $this->array;
    }

    /**
     * @param string $match
     * @return bool|null
     */
    private function matchConvertToBool(string $match) : ?bool
    {
        return !empty($match) ? $this->matchResult[$match] : null;
    }

    /**
     * @return string
     */
    public function getRawResponse(): string
    {
        return $this->raw;
    }

    /**
     * @param string $raw
     * @return void
     */
    public function setRaw(string $raw): void
    {
        $this->raw = $raw;
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->valid;
    }

    /**
     * @return string
     */
    public function getVatId(): string
    {
        return $this->vatId;
    }

    /**
     * @return string
     */
    public function getRequesterVatId(): string
    {
        return $this->requesterVatId;
    }

    /**
     * @return string
     */
    public function getCompanyName(): string
    {
        return $this->companyName;
    }

    /**
     * @return string
     */
    public function getCompanyAddress(): string
    {
        return $this->companyAddress;
    }

    /**
     * @return \DateTime
     */
    public function getRequestDate(): \DateTime
    {
        return $this->requestDate;
    }

    /**
     * @return bool|null
     */
    public function getMatchCompanyName(): ?bool
    {
        return $this->matchCompanyName;
    }

    /**
     * @return bool|null
     */
    public function getMatchCompanyStreet(): ?bool
    {
        return $this->matchCompanyStreet;
    }

    /**
     * @return bool|null
     */
    public function getMatchCompanyZipCode(): ?bool
    {
        return $this->matchCompanyZipCode;
    }

    /**
     * @return bool|null
     */
    public function getMatchCompanyCity(): ?bool
    {
        return $this->matchCompanyCity;
    }

    /**
     * @return int
     */
    public function getResponseCode(): int
    {
        return $this->responseCode;
    }
}