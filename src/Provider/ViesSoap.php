<?php

namespace VatValidate\Provider;

use DragonBe\Vies\Vies;
use VatValidate\AbstractProvider;
use VatValidate\Exceptions\InvalidArgumentException;
use VatValidate\Response;

class ViesSoap extends AbstractProvider
{
    private Vies $vies;

    public function __construct()
    {
        $this->vies = new Vies();
    }

    /**
     * Performs simple validation request.
     * @return bool|Response
     * @throws InvalidArgumentException
     * @throws \DragonBe\Vies\ViesException
     * @throws \DragonBe\Vies\ViesServiceException
     */
    public function simpleValidate(): bool|Response
    {
        // exception vatid not set
        if (empty($this->getVatId())) {
            throw new InvalidArgumentException('Required value "vat id" is not set.');
        }

        if ($this->isTestServiceActive()) {
            $this->vies->allowTestCodes();
        } else {
            $this->vies->disallowTestCodes();
        }

        $requestArray = $this->vies->splitVatId($this->getVatId());
        $result = $this->vies->validateVat($requestArray['country'], $requestArray['id']);
        if ($this->isGetResponse()) {
            return new Response($result);
        }
        return $result->isValid();
    }

    /**
     * Performs qualified validation request.
     * @return Response
     * @throws InvalidArgumentException
     * @throws \DragonBe\Vies\ViesException
     * @throws \DragonBe\Vies\ViesServiceException
     */
    public function qualifiedValidate(): Response
    {
        if (empty($this->getVatId()) ||
            empty($this->getCompanyName()) ||
            empty($this->getStreet()) ||
            empty($this->getPostcode()) ||
            empty($this->getCity())) {
            throw new InvalidArgumentException('Required values are not set.');
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
        return new Response($result);
    }
}