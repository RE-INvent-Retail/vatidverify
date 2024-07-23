# VatIdVerify
Component using the European Commission (EC) VAT Information Exchange System (VIES) and XML RPC API (EVatR) provided by
German Federal Central Tax Office (GFCTO) to verify and validate VAT registration numbers in the EU, using PHP.

More information on EVatR API at the [GFCTO website](https://evatr.bff-online.de/eVatR/xmlrpc/) (german version) and
on VIES APIs at the [EC website](https://ec.europa.eu/taxation_customs/vies/#/technical-information).

## Requirements
- PHP 8.0+
- Guzzle
- Phpxmlrpc
- Extension: soap
- Extension: simplexml

## Installation
The library can be installed with [Composer](http://getcomposer.org) by adding it as a dependency to your composer.json file.

Via the command line run:
`composer require wdo/vatidverify`

Here is a minimal example of a composer.json file after installation:
```json
{
    "require": {
        "wdo/vatidverify": "^1.0"
    }
}
```

## Usage
To use the vat validation create the VatValidate instance with appropriate provider, default is at the VIES SOAP provider.
Call the constants from VatValidate to use any other provider like in the example:
```php
$vatValidate = new \VatValidate\VatValidate(\VatValidate\VatValidate::PROVIDER_VIES_REST); 
```
Following methods are provided for the instance:
- `simpleValidate`: Performs a validation for a given vat id. Only `EVatR` requires requester vat id.
Can return either a boolean or a whole response object.
You can skip pre-check of vat id over regex if applicable, but it is not recommended. 
- `qualifiedValidation`: Performs qualified validation, where the name and address of the company is also checked.
Returns response object. You can skip pre-check of vat id over regex if applicable,
but it is not recommended.
  Note: only `EVatR` provider can do a sufficient qualified validation as of now.
- `setService`: Sets whether a test service should be used. Only viable for `VIES` providers.
- `getViesCountryAvailability`: Gets all available countries and their connectivity for `VIES` providers.
Example using simple validate with EVatR provider and return a response object:
```php
$vatValidate = new \VatValidate\VatValidate(\VatValidate\VatValidate::PROVIDER_EVATR);
$response = $vatValidate->simpleValidate('DE123456789', 'DE987654321', true);
echo $response->isValid();
echo $response->getArray();
```

### Providers
#### VIES SOAP and VIES REST
VIES SOAP and VIES REST are very similar in regard of the required values and request behaviour. Both of them do not
require requester vat id to validate.

The two VIES providers may also not be available in some countries on certain times of day.
For example, among them is germany not available around 11 pm till 1:30 am daily. More details for it [here](https://ec.europa.eu/taxation_customs/vies/#/help).
It is also possible that qualified validation may not be available for some countries. Germany is among those countries.
Some countries require company type for the qualified validation.
You can look what can be added here: https://ec.europa.eu/taxation_customs/vies/#/faq#Q25.

For testing purposes you can use test service with predetermined vat ids and address (details on the VIES website).
For that you need to deactivate the pre-check of the vat id. Example:
```php
$vatValidate = new \VatValidate\VatValidate(\VatValidate\VatValidate::PROVIDER_VIES_REST, true);
$valid = $vatValidate->simpleValidate('DE100', '', false, true); // should always return true
```
VIES SOAP is a default provider that is build on top of DragonBe VIES library (see [dependencies](#dependencies))
and has its own pre-check of vat id per country. That means skipping the pre-check is not possible for this provider.

By default, VIES REST uses a pre-check of the vat id from `CountryCheck` helper class.

Unlike SOAP provider, this one can also get the availability of the service and the countries.
Over the main class `VatValidate` you can do that with `getViesCountryAvailability()` or call directly the function:
```php
$statusArray = (new \VatValidate\Provider\ViesRest())->checkStatus();
```

#### EVatR
EVatR has no testing service and must always use real data. Normally the service is always available for both simple
and qualified validation. Though there might be maintenance between 11 pm and 5 am. According to GFCTO some requests
might take between seconds and minutes, depending on the country request is sent to.

For both validations requester vat id is a mandatory field to be set.
For qualified validation postal code and street are optional field values.

### Response
The common response object for all providers is a response type of qualified validation and optionally can be a response 
type of simple validation. Raw response can be retrieved from this. Unfortunately for VIES SOAP it would NOT be raw data
but only return JSON string from a modified array.

## Dependencies
VIES SOAP and partially REST request are using [vies library](https://github.com/dragonbe/vies/) by DragonBe. For VIES REST request guzzle is needed.
For using EVatR part of this library phpxmlrpc is required.