<?php

namespace VatValidate\Provider;

use DragonBe\Vies\CheckVatResponse;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use VatValidate\AbstractProvider;
use VatValidate\Exceptions\RequestErrorException;
use VatValidate\Exceptions\RequiredValuesException;

class ViesRest extends AbstractProvider
{

    const VIES_BASE_URI = 'https://ec.europa.eu/taxation_customs/vies/rest-api/';
    const VIES_REST = '/check-vat-number';
    const VIES_REST_TEST = '/check-vat-test-service';
    const VIES_REST_STATUS = '/check-status';
    private Client $client;

    private array $availabilityResults = [
        'Available' => true,
        'Unavailable' => false,
        'Monitoring Disabled' => null
    ];
    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * @return bool
     * @throws RequestErrorException
     * @throws RequiredValuesException
     */
    public function simpleValidate(): bool
    {
        if (empty($this->getVatId())) {
            throw new RequiredValuesException('Required value "vat id" is not set.');
        }

        $result = $this->validateVat();
        return $result->isValid();
    }

    /**
     * @return array
     * @throws RequestErrorException
     * @throws RequiredValuesException
     */
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

        $result = $this->validateVat();
        return $result->toArray();
    }

    /**
     * @param string $vatId
     * @return array
     */
    private function splitVatId(string $vatId) : array
    {
        return [
            'country' => substr($vatId, 0, 2),
            'id' => substr($vatId, 2),
        ];
    }

    /**
     * @return CheckVatResponse
     * @throws RequestErrorException
     */
    private function validateVat() : CheckVatResponse
    {
        // todo check vat number per country according to shop?

        $vatIdArray = $this->splitVatId($this->getVatId());
        $vatIdArrayTwo = $this->splitVatId($this->getRequesterVatId());
        $request = [
            "countryCode" => $vatIdArray['country'],
            "vatNumber" => $vatIdArray['id'],
            "requesterMemberStateCode" => $vatIdArrayTwo['country'],
            "requesterNumber" => $vatIdArrayTwo['id'],
            "traderName" => $this->getCompanyName(),
            "traderStreet" => $this->getStreet(),
            "traderPostalCode" => $this->getPostcode(),
            "traderCity" => $this->getCity(),
            "traderCompanyType" => $this->getCompanyType()
        ];

        $headers = ["Content-Type" => "application/json"];
        $body = json_encode($request);

        $uri = self::VIES_BASE_URI . self::VIES_REST;
        if ($this->isTestServiceActive()) {
            $uri = self::VIES_BASE_URI . self::VIES_REST_TEST;
        }

        $request = new Request('POST', $uri, $headers, $body);
        $response = $this->client->sendAsync($request)->wait();
        $content = json_decode($response->getBody()->getContents());

        if ($response->getStatusCode() === 200) {
            // need to create date time object since in soap request the date format differs
            $date = date_create_from_format(
                'Y-m-d\TH:i:s.vp',
                $content->requestDate
            )->setTime(0, 0, 0, 0);
            $content->requestDate = $date;
        } else {
            // error handling
            throw new RequestErrorException($content->errorWrappers['message'], $content->errorWrappers['error']);
        }

        return new CheckVatResponse($content);
    }

    /**
     * Gets availability of countries per country code if service available.
     * True for available, False for not available and Null for disabled monitoring
     * ['AT' => true, ...]
     * @return array
     * @throws RequestErrorException
     */
    public function checkStatus() : array
    {
        $availabilities = [];
        $response = $this->client->getAsync(self::VIES_BASE_URI . self::VIES_REST_STATUS)->wait();
        $content = json_decode($response->getBody()->getContents(), true);

        if ($response->getStatusCode === 200) {
            if ($content['vow']['available']) {
                foreach ($content['countries'] as $country) {
                    $availabilities[$country['countryCode']] = $this->availabilityResults[$country['availability']];
                }
            }
        } else {
            throw new RequestErrorException($content->errorWrappers['message'], $content->errorWrappers['error']);
        }

        return $availabilities;
    }
}