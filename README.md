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
Can return either a boolean or a whole response object (see section [response](#response)).
You can skip pre-check of vat id over regex if applicable, but it is not recommended. 
- `qualifiedValidation`: Performs qualified validation, where the name and address of the company is also checked.
Returns response object (see section [response](#response)). You can skip pre-check of vat id over regex if applicable,
but it is not recommended.
  Note: only `EVatR` provider can do a sufficient qualified validation as of now.
- `setService`: Sets whether a test service should be used. Only viable for `VIES` providers.
- `getViesCountryAvailability`: Gets all available countries and their connectivity for `VIES` providers.

### Providers
#### VIES SOAP
Default provider that is build on top of DragonBe VIES library (see [dependencies](#dependencies))
and has its own pre-check of vat id per country. That means skipping the pre-check is not possible for this provider.
It is possible that qualified validation may not be available for some countries. Germany is among those countries.
For testing purposes you can use test service with predetermined vat ids and address (details on the VIES website).

#### VIES REST
By default, VIES REST uses a pre-check of the vat id from `CountryCheck` helper class.

The two VIES providers may also not be available in some countries on certain times of day. 
For example, among them is germany not available around 11 pm till 1:30 am daily. More details for it [here](https://ec.europa.eu/taxation_customs/vies/#/help).
Unlike SOAP provider, this one can also get the availability of the service and the countries.
Over the main class `VatValidate` you can do that with `getViesCountryAvailability()` or call directly the function:
```php
    $statusArray = (new \VatValidate\Provider\ViesRest())->checkStatus(); 
```

Just like VIES SOAP provider, qualified validation may not be available for some countries.
Furthermore, a test service is also provided with predetermined vat ids and address (details on the VIES website).

#### EVatR
EVatR has no testing service and must always use real data. Normally the service is always available for both simple and qualified validation.
Though there might be maintenance time between 11 pm and 5 am.

### Response
// todo

## Dependencies
VIES SOAP and partially REST request are using [vies library](https://github.com/dragonbe/vies/) by DragonBe. For VIES REST request guzzle is needed.
For using EVatR part of this library phpxmlrpc is required.