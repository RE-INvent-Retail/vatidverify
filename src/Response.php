<?php

namespace VatValidate;

use DragonBe\Vies\CheckVatResponse;
use VatValidate\Helper\EVatRResponse;

class Response
{
    private bool $valid;
    private string $vatId;
    private string $requesterVatId = '';
    private string $companyName;
    private string $companyAddress;
    private \DateTime $requestDate;
    private ?bool $matchCompanyName;
    private ?bool $matchCompanyStreet;
    private ?bool $matchCompanyZipCode;
    private ?bool $matchCompanyCity;
    private int $responseCode;
    private string $raw;
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
            $this->vatId = $viesResponse->getVatNumber();
            $this->companyName = $viesResponse->getName();
            $this->companyAddress = $viesResponse->getAddress();
            $this->requestDate = $viesResponse->getRequestDate();
            $this->matchCompanyName = $this->matchConvertToBool($viesResponse->getNameMatch());
            $this->matchCompanyStreet = $this->matchConvertToBool($viesResponse->getStreetMatch());
            $this->matchCompanyZipCode = $this->matchConvertToBool($viesResponse->getPostcodeMatch());
            $this->matchCompanyCity = $this->matchConvertToBool($viesResponse->getCityMatch());
            $this->responseCode = $viesResponse->isValid() ? 200 : 201;
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
        }
    }

    private function matchConvertToBool(string $match) : ?bool
    {
        return !empty($match) ? $this->matchResult[$match] : null;
    }

    public function getRawResponse(): string
    {
        return $this->raw;
    }

    public function setRaw(string $raw): void
    {
        $this->raw = $raw;
    }

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function getVatId(): string
    {
        return $this->vatId;
    }

    public function getRequesterVatId(): string
    {
        return $this->requesterVatId;
    }

    public function getCompanyName(): string
    {
        return $this->companyName;
    }

    public function getCompanyAddress(): string
    {
        return $this->companyAddress;
    }

    public function getRequestDate(): \DateTime
    {
        return $this->requestDate;
    }

    public function getMatchCompanyName(): ?bool
    {
        return $this->matchCompanyName;
    }

    public function getMatchCompanyStreet(): ?bool
    {
        return $this->matchCompanyStreet;
    }

    public function getMatchCompanyZipCode(): ?bool
    {
        return $this->matchCompanyZipCode;
    }

    public function getMatchCompanyCity(): ?bool
    {
        return $this->matchCompanyCity;
    }

    public function getResponseCode(): int
    {
        return $this->responseCode;
    }
}