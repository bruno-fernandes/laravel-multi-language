<?php

namespace BrunoFernandes\LaravelMultiLanguage\Tests;

use Orchestra\Testbench\TestCase;
use BrunoFernandes\LaravelMultiLanguage\Tests\Models\Page;
use BrunoFernandes\LaravelMultiLanguage\LaravelMultiLanguageFacade;
use BrunoFernandes\LaravelMultiLanguage\LaravelMultiLanguageServiceProvider;
use BrunoFernandes\LaravelMultiLanguage\Exceptions\ModelTranslationAlreadyExistsException;

class TranslatableTest extends TestCase
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
    public function model_is_created_with_the_default_locale()
    {
        $title = 'English title';
        $page = Page::create(['title' => $title]);

        $this->assertEquals($page->id, $page->original_id);
        $this->assertEquals('en', $page->lang);
        $this->assertEquals($title, $page->title);

        $this->assertDatabaseHas($page->getTable(), [$page->getLangKey() => 'en', 'title' => $title]);
    }

    /** @test */
    public function model_is_translated_to_another_locale()
    {
        $original = Page::create(['title' => 'English title']);

        $title = 'Spanish title';
        $page = $original->translateTo($locale = 'es', $data = ['title' => $title]);

        $this->assertEquals($page->original_id, $original->id);
        $this->assertEquals('es', $page->lang);
        $this->assertEquals($title, $page->title);

        $this->assertDatabaseHas($page->getTable(), [$page->getLangKey() => 'es', 'title' => $title]);
    }

    /** @test */
    public function trows_exception_when_translation_to_the_same_locale_of_the_original()
    {
        $this->expectException(ModelTranslationAlreadyExistsException::class);

        $original = Page::create(['title' => 'English title']);
        $original->translateTo('en');
    }

    /** @test */
    public function trows_exception_if_translation_already_exists()
    {
        $this->expectException(ModelTranslationAlreadyExistsException::class);

        $original = Page::create(['title' => 'English title']);
        $title = 'Spanish title';

        $original->translateTo('es', ['title' => $title]);
        $original->translateTo('es', ['title' => $title]);
    }
}
