<?php

namespace VatValidate\Provider;

use PhpXmlRpc\Client;
use PhpXmlRpc\Request;
use PhpXmlRpc\Value;
use VatValidate\AbstractProvider;
use VatValidate\Exceptions\InvalidArgumentException;
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
     * @return bool|Response
     * @throws InvalidArgumentException
     */
    public function simpleValidate() : bool|Response
    {
        // exception if vatid and ownvatid are not set
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
     * @return Response
     * @throws InvalidArgumentException
     */
    public function qualifiedValidate(): Response
    {
        if (empty($this->getVatId()) ||
            empty($this->getRequesterVatId()) ||
            empty($this->getCompanyName()) ||
            empty($this->getStreet()) ||
            empty($this->getPostcode()) ||
            empty($this->getCity())
        ) {
            throw new InvalidArgumentException('One of required values "vat id", "requester vat id" or company address including company name are not set.');
        }
        $response = $this->sendRequest();
        return new Response(null, $response);
    }

    private function sendRequest() : EVatRResponse
    {
        if ($this->isActiveCountryCheck() && !CountryCheck::isValidPattern(substr($this->getVatId(), 2), substr($this->getVatId(), 0, 2))) {
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

        return new EVatRResponse($xmlResponse->value()->me['string']);
    }

}