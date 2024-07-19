<?php

namespace VatValidate\Helper;

class EVatRResponse
{
    private \SimpleXMLElement $xml;
    private array $data = [];
    private int $responseCode;
    private string $ownUstId;
    private string $foreignUstId;
    private string $companyName = '';
    private ?bool $responseCompanyName = null;
    private string $street = '';
    private ?bool $responseStreet = null;
    private string $city = '';
    private ?bool $responseCity = null;
    private string $zipCode = '';
    private ?bool $responseZipCode = null;
    private string $date = '';
    private string $time = '';
    private string $validFrom = '';
    private string $validUntil = '';
    private bool $fromXML = false;

    private array $responseResults = [
        'A' => true,
        'B' => false,
        'C' => null,
        'D' => null
    ];

    public function __construct(string $xmlResponse = null, array $data = [])
    {
        if (!empty($xmlResponse)) {
            $this->xml = new \SimpleXMLElement($xmlResponse);
            $this->fromXML = true;
        } else {
            $this->data = $data;
        }
        $this->process();
    }

    /**
     * @return void
     */
    private function process(): void
    {
        if ($this->fromXML){
            $data = $this->xml->children();
        } else {
            $data = $this->data;
        }
        foreach ($data as $index => $child) {
            if ($this->fromXML) {
                $key = $child->value->array->data->value[0]->string->__toString();
                $value = $child->value->array->data->value[1]->string->__toString();
            } else {
                $key = $index;
                $value = $child;
            }

            switch ($key) {
                case 'UstId_1':
                    $this->ownUstId = $value;
                    break;
                case 'UstId_2':
                    $this->foreignUstId = $value;
                    break;
                case 'Firmenname':
                    $this->companyName = $value;
                    break;
                case 'Erg_Name':
                    $this->responseCompanyName = $this->responseResultConvertToBool($value);
                    break;
                case 'Strasse':
                    $this->street = $value;
                    break;
                case 'Erg_Str':
                    $this->responseStreet = $this->responseResultConvertToBool($value);
                    break;
                case 'Ort':
                    $this->city = $value;
                    break;
                case 'Erg_Ort':
                    $this->responseCity = $this->responseResultConvertToBool($value);
                    break;
                case 'PLZ':
                    $this->zipCode = $value;
                    break;
                case 'Erg_PLZ':
                    $this->responseZipCode = $this->responseResultConvertToBool($value);
                    break;
                case 'Datum':
                    // dd.mm.yyyy
                    $this->date = $value;
                    break;
                case 'Uhrzeit':
                    // hh:mm:ss
                    $this->time = $value;
                    break;
                case 'Gueltig_ab':
                    $this->validFrom = $value;
                    break;
                case 'Gueltig_bis':
                    $this->validUntil = $value;
                    break;
                case 'ErrorCode':
                    $this->responseCode = (int)$value;
                    break;
                default:
                    break;
            }
        }
    }

    /**
     * @return bool
     */
    public function isValid() : bool
    {
        return $this->responseCode === 200;
    }

    /**
     * @return string[]
     */
    public static function getResponseCodeTexts() : array
    {
        return [
            200 => 'The requested VAT ID is valid.',
            201 => 'The requested VAT ID is invalid.',
            202 => 'The requested VAT ID is invalid. It is not registered in the EU member state.
          Note: Your business partner can request its valid VAT ID at the responsible Ministry of Finance responsible.',
            203 => 'The requested VAT ID is invalid.
             It is valid from ... (see "valid from" field).',
            204 => 'The requested VAT ID is invalid.
             It was valid between ... and ... (see "valid from" and "valid till" fields).',
            205 => 'Your request cannot  be processed by the EU member or due to other reasons.
             Please try again later. If problems persist please contact the Federal Central Tax Office.',
            206 => 'Your German VAT ID is invalid. Therefore your request cannot be processed.
             Please contact the Federal Central Tax Office.',
            207 => 'Your German VAT ID is only valid for taxation of intra-Community acquisitions.
             You are not permitted to file any requests.',
            208 => 'There is already a request running for the VAT ID requested by you.
             Your request cannot be processed. Please try again later.',
            209 => 'The requested VAT ID is invalid. It does not comply with the format of that EU member state.',
            210 => 'The requested VAT ID is invalid.
             It does not comply with the checksum rules of that EU member state.',
            211 => 'The requested VAT ID is invalid. It contains invalid characters (i. e. spaces, dashes etc.).',
            212 => 'The requested VAT ID is invalid. It contains an invalid country code.',
            213 => 'A German VAT ID cannot be requested.',
            214 => 'Your German VAT ID is invalid. It starts with "DE" followed by 9 digits.',
            215 => 'Your request does not contain all necessary data for a qualified request.
             Your request cannot be processed.',
            216 => 'Your request does not contain all necessary data for a qualified request.
             A simple request has been made instead with the following result: The requested VAT ID is valid.',
            217 => 'While processing the data from the EU member state an error occurred.
             Your request cannot be processed.',
            218 => 'A qualified request is currently not possible.
             A simple request has been made instead with the following result: The requested VAT ID is valid.',
            219 => 'While running a qualified request an error occurred.
             A simple request has been made instead with the following result: The requested VAT ID is valid.',
            220 => 'When requesting an official confirmation an error occurred.
             No official confirmation will be sent.',
            221 => 'The requested data does not contain all necessary parameters or an illegal data type.
             Please check the documentation how to call the interface.',
            999 => 'The request cannot be processed at the moment. Please try later again.',
        ];
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return  [
            'vatNumber1'    => $this->getForeignVatId(),
            'requestDate'   => $this->getDate() . ' - ' . $this->getTime(),
            'valid_time'    => $this->getValidFrom() . ' - ' . $this->getValidUntil(),
            'name'          => $this->getCompanyName(),
            'address'       => $this->getAddress(),
            'nameMatch'     => $this->getResponseCompanyName(),
            'streetMatch'   => $this->getResponseStreet(),
            'postcodeMatch' => $this->getResponseZipCode(),
            'cityMatch'     => $this->getResponseCity(),
            'error_code'    => $this->getResponseCode()
        ];
    }

    /**
     * @return int
     */
    public function getResponseCode(): int
    {
        return $this->responseCode;
    }

    /**
     * @return string
     */
    public function getOwnVatId(): string
    {
        return $this->ownUstId;
    }

    /**
     * @return string
     */
    public function getForeignVatId(): string
    {
        return $this->foreignUstId;
    }

    /**
     * @return string
     */
    public function getCompanyName(): string
    {
        return $this->companyName;
    }

    /**
     * @return bool|null
     */
    public function getResponseCompanyName(): ?bool
    {
        return $this->responseCompanyName;
    }

    /**
     * @return string
     */
    public function getStreet(): string
    {
        return $this->street;
    }

    /**
     * @return bool|null
     */
    public function getResponseStreet(): ?bool
    {
        return $this->responseStreet;
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * @return bool|null
     */
    public function getResponseCity(): ?bool
    {
        return $this->responseCity;
    }

    /**
     * @return string
     */
    public function getZipCode(): string
    {
        return $this->zipCode;
    }

    /**
     * @return bool|null
     */
    public function getResponseZipCode(): ?bool
    {
        return $this->responseZipCode;
    }

    /**
     * @return string
     */
    public function getDate(): string
    {
        return $this->date;
    }

    /**
     * @return string
     */
    public function getTime(): string
    {
        return $this->time;
    }

    /**
     * @return string
     */
    public function getValidFrom(): string
    {
        return $this->validFrom;
    }

    /**
     * @return string
     */
    public function getValidUntil(): string
    {
        return $this->validUntil;
    }

    /**
     * @return string
     */
    public function getAddress(): string
    {
        return $this->getStreet() . ' '. $this->getZipCode(). ' '.$this->getCity();
    }

    /**
     * @return string
     */
    public function getRaw(): string
    {
        if ($this->fromXML) {
            return $this->xml->asXML();
        }
        return json_encode($this->data);
    }

    /**
     * @param string $value
     * @return bool|null
     */
    private function responseResultConvertToBool(string $value) : ?bool
    {
        return !empty($value) ? $this->responseResults[$value] : null;
    }
}