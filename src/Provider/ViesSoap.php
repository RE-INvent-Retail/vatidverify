<?php

namespace VatValidate\Provider;

use DragonBe\Vies\Vies;
use VatValidate\AbstractProvider;
use VatValidate\Exceptions\RequiredValuesException;

class ViesSoap extends AbstractProvider
{
    private Vies $vies;

    public function __construct()
    {
        $this->vies = new Vies();
    }

    public function simpleValidate(): bool
    {
        // exception vatid not set
        if (empty($this->getVatId())) {
            throw new RequiredValuesException('Required value "vat id" is not set.');
        }

        if ($this->isTestServiceActive()) {
            $this->vies->allowTestCodes();
        } else {
            $this->vies->disallowTestCodes();
        }

        $requestArray = $this->vies->splitVatId($this->getVatId());
        $result = $this->vies->validateVat($requestArray['country'], $requestArray['id']);
        return $result->isValid();
    }

    public function qualifiedValidate(): array
    {
        if (empty($this->getVatId()) ||
            empty($this->getRequesterVatId()) ||
            empty($this->getCompanyName()) ||
            empty($this->getStreet()) ||
            empty($this->getPostcode()) ||
            empty($this->getCity())) {
            throw new RequiredValuesException('Required values are not set.');
        }

        if ($this->isTestServiceActive()) {
            $this->vies->allowTestCodes();
        } else {
            $this->vies->disallowTestCodes();
        }

        $vatIdArray = $this->vies->splitVatId($this->getVatId());
        $vatIdArrayTwo = $this->vies->splitVatId($this->getRequesterVatId());
        $result = $this->vies->validateVat(
            $vatIdArray['country'],
            $vatIdArray['id'],
            $vatIdArrayTwo['country'],
            $vatIdArrayTwo['id'],
            $this->getCompanyName(),
            $this->getCompanyType(),
            $this->getStreet(),
            $this->getPostcode(),
            $this->getCity()
        );
        return $result->toArray();
    }
}