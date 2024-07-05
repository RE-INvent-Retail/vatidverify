<?php

namespace VatValidate\Provider;

use PhpXmlRpc\Client;
use PhpXmlRpc\Request;
use PhpXmlRpc\Value;
use VatValidate\AbstractProvider;
use VatValidate\Exceptions\RequiredValuesException;
use VatValidate\Helper\EVatRResponse;

class EVatR extends AbstractProvider
{
    /**
     * Evatr url for the XML RPC.
     */
    const EVATRINTERFACEURL = 'https://evatr.bff-online.de';

    private Client $client;
    private EVatRResponse $response;

    public function __construct()
    {
        $this->client = new Client(self::EVATRINTERFACEURL);
    }

    public function simpleValidate() : bool
    {
        // exception if vatid and ownvatid are not set
        if (empty($this->getVatId()) || empty($this->getRequesterVatId())) {
            throw new RequiredValuesException('Required values "vat id" or "requester vat id" are not set.');
        }
        $this->response = $this->sendRequest();
        return $this->response->isValid();
    }

    public function qualifiedValidate(): array
    {
        if (empty($this->getVatId()) ||
            empty($this->getRequesterVatId()) ||
            empty($this->getCompanyName()) ||
            empty($this->getStreet()) ||
            empty($this->getPostcode()) ||
            empty($this->getCity())
        ) {
            throw new RequiredValuesException('One of required values "vat id", "requester vat id" or company address including company name are not set.');
        }
        $this->response = $this->sendRequest();
        return $this->response->toArray();
    }

    private function sendRequest()
    {
        // todo check country
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

    public function getResponse() : EVatRResponse
    {
        if (!isset($this->response)) {
            $this->simpleValidate();
        }
        return $this->response;
    }
}