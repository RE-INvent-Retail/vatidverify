<?php

namespace VatValidate\Provider;

use PhpXmlRpc\Client;
use PhpXmlRpc\Request;
use PhpXmlRpc\Value;
use VatValidate\AbstractProvider;
use VatValidate\Exceptions\InvalidArgumentException;
use VatValidate\Exceptions\RequestErrorException;
use VatValidate\Helper\CountryCheck;
use VatValidate\Helper\EVatRResponse;
use VatValidate\Response;

class EVatR extends AbstractProvider
{
    /**
     * Evatr url for the XML RPC.
     */
    const EVATR_URL = 'https://evatr.bff-online.de';

    private Client $client;

    public function __construct()
    {
        $this->client = new Client(self::EVATR_URL);
    }

    /**
     * Performs simple validation request.
     * Vat id and requester vat id are required values.
     * @return bool|Response
     * @throws InvalidArgumentException|RequestErrorException
     */
    public function simpleValidate() : bool|Response
    {
        // exception if vat id and requester vat id are not set
        if (empty($this->getVatId()) || empty($this->getRequesterVatId())) {
            throw new InvalidArgumentException('Required values "vat id" or "requester vat id" are not set.');
        }
        $result = $this->sendRequest();
        if ($this->isGetResponse()) {
            return new Response(null, $result);
        }
        return $result->isValid();
    }

    /**
     * Performs qualified validation request.
     * VatId, RequesterVatId, CompanyName and City are mandatory values.
     *
     * @return Response
     * @throws InvalidArgumentException|RequestErrorException
     */
    public function qualifiedValidate(): Response
    {
        if (empty($this->getVatId()) ||
            empty($this->getRequesterVatId()) ||
            empty($this->getCompanyName()) ||
            empty($this->getCity())
        ) {
            throw new InvalidArgumentException('One of required values vat id, requester vat id, company name or city are not set.');
        }
        $response = $this->sendRequest();
        return new Response(null, $response);
    }

    /**
     * Performs request call, unless pre-check fails.
     * @return EVatRResponse
     * @throws RequestErrorException
     */
    private function sendRequest() : EVatRResponse
    {
        if ($this->isActiveCountryCheck() && !CountryCheck::isValidPattern(
            substr($this->getVatId(), 2), substr($this->getVatId(), 0, 2))) {
            $now = new \DateTime();
            $data = [
                'UstId_1' => $this->getRequesterVatId(),
                'UstId_2' => $this->getVatId(),
                'ErrorCode' => 201,
                'Datum' => $now->format('d.m.Y'),
                'Uhrzeit' => $now->format('H:i:s')
            ];
            return new EVatRResponse(null, $data);
        }

        $xmlResponse = $this->client->send(new Request(
            'evatrRPC', [
                new Value($this->getRequesterVatId()),
                new Value($this->getVatId()),
                new Value($this->getCompanyName()),
                new Value($this->getCity()),
                new Value($this->getPostcode()),
                new Value($this->getStreet()),
            ]
        ));

        // throw exception if error occurs
        if ($xmlResponse->faultCode() !== 0) {
            throw new RequestErrorException($xmlResponse->faultString());
        }

        $response = new EVatRResponse($xmlResponse->value()->me['string']);

        // throw exception if error code is in retry error codes
        if (in_array($response->getResponseCode(), EVatRResponse::RETRY_ERROR_CODES)) {
            throw new RequestErrorException(\VatValidate\Helper\EVatRResponse::getResponseCodeTexts()[$response->getResponseCode()]);
        }

        return $response;
    }

}