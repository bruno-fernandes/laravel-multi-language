# Laravel Multi-language

**IMPORTANT:** Under active development. Do not use in production, api might change.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/bruno-fernandes/laravel-multi-language.svg?style=flat-square)](https://packagist.org/packages/bruno-fernandes/laravel-multi-language)
[![CircleCI](https://circleci.com/gh/bruno-fernandes/laravel-multi-language.svg?style=svg&circle-token=204e4d6fde62b9ba380ef4d513a568e20ace4090)](https://circleci.com/gh/bruno-fernandes/laravel-multi-language)
[![Quality Score](https://img.shields.io/scrutinizer/g/bruno-fernandes/laravel-multi-language.svg?style=flat-square)](https://scrutinizer-ci.com/g/bruno-fernandes/laravel-multi-language)
[![Total Downloads](https://img.shields.io/packagist/dt/bruno-fernandes/laravel-multi-language.svg?style=flat-square)](https://packagist.org/packages/bruno-fernandes/laravel-multi-language)

Simple approach to eloquent models translation. There are other packages that provide translation functionality, this is a different approach with some trade-offs. Priority is simplicity.

// TODO: add example database schema here.

### Key points:

- easy to get up running on existing applications
- all eloquent model fields are translatable
- original can be created in any language
- translations can be copied from original
- translations can be easily associated later on

## Installation

You can install the package via composer:

```bash
composer require bruno-fernandes/laravel-multi-language
```

If you want to set custom column names, publish the config file and override the defaults:

```bash
php artisan vendor:publish --provider="BrunoFernandes\LaravelMultiLanguage\LaravelMultiLanguageServiceProvider"
```

## Usage

``` php
// Import the Translatable trait into the eloquent model
use BrunoFernandes\LaravelMultiLanguage\Translatable;

class Page extends Model
{
    use Translatable;
}

// Create a migration to add the required columns to the model's table
// Example:
class AddMultilanguageFieldsToPagesTable extends Migration
{
    public function up()
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->string('lang', 6)->default('en')->index()->after('id');
            $table->integer('original_id')->unsigned()->nullable()->index()->after('id');
        });

        // TODO: if there are already records on the table, create a migration to update
        // all of them and set the lang and the original_id with the correct values
    }
}

//
// Usage
// 
$page = Page::create(['title' => 'English title']);
$englishPage = $page->translateTo('es', ['title' => 'Spanish title']);

$originalPages = Page::onlyOriginals()->get();

// the package will apply the lang scope by default, so only  
// the current locale records are returned (it can be disable in the config file)
$currentLocalePages = Page::get();

// if apply lang global scope is disabled you can use the lang scope as follow:
$enPagesWithTranslations = Page::lang('en')->withTranslations()->get();
// NOTE: always use withTranslations() rather than with('translations) as it is more efficient
// using withTranslations() will exlude the current locale from the translations relationship

// if you would like to remove a global scope for a given query,
// you may use the  withoutGlobalScope method as follow:
use BrunoFernandes\LaravelMultiLanguage\Scopes\LangScope;
$allPagesOfAllLocales = Page::withoutGlobalScope(LangScope::class)->get();

// TODO: add usage samples to be added
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

If you discover any security related issues, please email security@brunofernandes.com instead of using the issue tracker.

## Credits

- [Bruno Fernandes](https://github.com/bruno-fernandes)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
