# Laravel Multi-language

[![Latest Version on Packagist](https://img.shields.io/packagist/v/bruno-fernandes/laravel-multi-language.svg?style=flat-square)](https://packagist.org/packages/bruno-fernandes/laravel-multi-language)
[![Build Status](https://img.shields.io/travis/bruno-fernandes/laravel-multi-language/master.svg?style=flat-square)](https://travis-ci.org/bruno-fernandes/laravel-multi-language)
[![Quality Score](https://img.shields.io/scrutinizer/g/bruno-fernandes/laravel-multi-language.svg?style=flat-square)](https://scrutinizer-ci.com/g/bruno-fernandes/laravel-multi-language)
[![Total Downloads](https://img.shields.io/packagist/dt/bruno-fernandes/laravel-multi-language.svg?style=flat-square)](https://packagist.org/packages/bruno-fernandes/laravel-multi-language)

Simple approach to eloquent models translation. There are other packages that provide translation functionality, this is a different approach with some trade-offs. Priority is simplicity.

TODO: add example database schema here.

###Key points:

- easy to get up running on existing applications
- all eloquent model fields are translable
- original can be created in any language
- translations can be copied from original
- translations can be associated later on

## Installation

You can install the package via composer:

```bash
composer require bruno-fernandes/laravel-multi-language
```

If you want to set custom column names, publish the config file and overide the defaults:

```bash
php artisan vendor:publish --provider="BrunoFernandes\LaravelMultiLanguage\LaravelMultiLanguageServiceProvider"
```

## Usage

``` php
// Usage description here
```

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email bruno@brunofernandes.com instead of using the issue tracker.

## Credits

- [Bruno Fernandes](https://github.com/bruno-fernandes)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
