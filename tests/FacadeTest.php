<?php

namespace BrunoFernandes\LaravelMultiLanguage\Tests;

use Orchestra\Testbench\TestCase;
use BrunoFernandes\LaravelMultiLanguage\LaravelMultiLanguageFacade;
use BrunoFernandes\LaravelMultiLanguage\LaravelMultiLanguageServiceProvider;

class FacadeTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            LaravelMultiLanguageServiceProvider::class
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'LaravelMultiLanguage' => LaravelMultiLanguageFacade::class
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        include_once __DIR__ . '/migrations/create_pages_table.php';
        (new \CreatePagesTable)->up();
    }

    /** @test */
    public function can_get_locale()
    {
        $locale = LaravelMultiLanguageFacade::getLocale();

        $this->assertEquals('en', $locale);
    }
}
