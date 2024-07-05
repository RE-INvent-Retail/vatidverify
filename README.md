# VatIdVerify
VAT ID validation

## Requirements
- PHP 8.0+

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


## Dependencies
The part of VIES soap request is using [vies library](https://github.com/dragonbe/vies/) by DragonBe. For VIES rest request guzzle is used. For using EVatR part of this library phpxmlrpc is required.