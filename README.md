# Laravel Multi-language

**IMPORTANT:** Under active development. Do not use in production, api might change.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/bruno-fernandes/laravel-multi-language.svg?style=flat-square)](https://packagist.org/packages/bruno-fernandes/laravel-multi-language)
[![CircleCI](https://circleci.com/gh/bruno-fernandes/laravel-multi-language.svg?style=svg&circle-token=204e4d6fde62b9ba380ef4d513a568e20ace4090)](https://circleci.com/gh/bruno-fernandes/laravel-multi-language)
[![CodeCoverage](https://codecov.io/gh/bruno-fernandes/laravel-multi-language/branch/master/graph/badge.svg)](https://codecov.io/github/bruno-fernandes/laravel-multi-language?branch=master)
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
            // Create columns
            $table->string(config('laravel-multi-language.lang_key'), 6)
                ->default('en')->index()->after('id');
            $table->integer(config('laravel-multi-language.foreign_key'))
                ->unsigned()->nullable()->index()->after('id');
            
            // Create composite unique index to prevent multiple
            // records using the same lang key
            $table->unique([
                config('laravel-multi-language.foreign_key'), 
                config('laravel-multi-language.lang_key')
            ]);
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

### Known issues

- When used with [Searchable package](https://github.com/nicolaslopezj/searchable) global scopes need to be removed and applied manually after the search method is used.

- when using hasOne relationships, if *foreign_key* and *local_key* are not set the LangScope (Global Scope) is applied to the relationship, if the relationship model is not translatable an error is thrown.

```sql
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'live_players.original_id' in 'where clause' (SQL: select * from `live_players` where `live_players`.`original_id` in (35) and `live_players`.`deleted_at` is null) (View: /home/vagrant/code/resources/frontend/views/index.blade.php)
```

``` php
// This does not work
class Content extends Model
{
    public function livePlayer()
    {
        return $this->hasOne(LivePlayer::class);
    }
}

// This works
class Content extends Model
{
    public function livePlayer()
    {
        return $this->hasOne(LivePlayer::class, 'id', 'live_player_id');
    }
}
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
