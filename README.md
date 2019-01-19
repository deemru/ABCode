# ABCode

[![packagist](https://img.shields.io/packagist/v/deemru/abcode.svg)](https://packagist.org/packages/deemru/abcode) [![php-v](https://img.shields.io/packagist/php-v/deemru/abcode.svg)](https://packagist.org/packages/deemru/abcode)  [![travis](https://img.shields.io/travis/deemru/ABCode.svg?label=travis)](https://travis-ci.org/deemru/ABCode) [![codacy](https://img.shields.io/codacy/grade/d3b953106fa141c7a85c5a369fe2062b.svg?label=codacy)](https://app.codacy.com/project/deemru/ABCode/dashboard) [![license](https://img.shields.io/packagist/l/deemru/abcode.svg)](https://packagist.org/packages/deemru/abcode)

[ABCode](https://github.com/deemru/ABCode) is a universal (single byte per character) alphabet converter for PHP.

- Built in base58
- Convert strings to your alphabet
- Convert between alphabets

## Usage

```php
// Built in base58
$data = ABCode::base58()->encode( 'Hello, world!' );
if( $data !== '72k1xXWG59wUsYv7h2' )
    exit( 1 );

// Convert strings to your alphabet
$abcode = new ABCode( 'my_ABC' );
$data = $abcode->encode( $data );
if( $data !== 'BAAy_Cmm_BA_AC_BCA_A_ymymCCmyyBBABBACCyBm___mA_BAm_yA__' )
    exit( 1 );

// Convert between alphabets
$abcode = new ABCode( 'my_ABC', 'another_ABC-123' );
$data = $abcode->decode( $data );
if( $data !== 'otah2_en3_o22ABhhrroA1eCAC3ronBn3t2-o' )
    exit( 1 );
```

## Requirements

- [PHP](http://php.net) >=5.4
- [GMP](http://php.net/manual/en/book.gmp.php)

## Installation

Require through Composer:

```json
{
    "require": {
        "deemru/abcode": "1.0.*"
    }
}
```
