<?php

namespace BrunoFernandes\LaravelMultiLanguage\Tests;

use Orchestra\Testbench\TestCase;
use BrunoFernandes\LaravelMultiLanguage\Tests\Models\Page;
use BrunoFernandes\LaravelMultiLanguage\LaravelMultiLanguageFacade;
use BrunoFernandes\LaravelMultiLanguage\LaravelMultiLanguageServiceProvider;
use BrunoFernandes\LaravelMultiLanguage\Exceptions\ModelTranslationAlreadyExistsException;
use Illuminate\Support\Facades\Config;
use BrunoFernandes\LaravelMultiLanguage\Scopes\LangScope;
use Carbon\Carbon;

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
    public function model_is_translated_from_original_to_another_locale()
    {
        $original = Page::create([
            'title' => 'English title',
            'starts_at' => "2019-12-03 13:55:07+00:00"
        ]);

        $title = 'Spanish title';
        $page = $original->translateTo($locale = 'es', $data = ['title' => $title]);

        $this->assertEquals($page->original_id, $original->id);
        $this->assertEquals('es', $page->lang);
        $this->assertEquals($title, $page->title);

        $this->assertDatabaseHas($page->getTable(), [$page->getLangKey() => 'es', 'title' => $title]);
    }

    /** @test */
    public function model_is_translated_from_translation_to_another_locale()
    {
        $original = Page::create(['title' => 'English title']);
        $translation = $original->translateTo('es', ['title' => 'Title in spanish']);
        $translation2 = $translation->translateTo('pt', ['title' => 'Title in portuguese']);

        $this->assertEquals($translation2->original_id, $original->id);
        $this->assertEquals('pt', $translation2->lang);
        $this->assertEquals('Title in portuguese', $translation2->title);

        $this->assertDatabaseHas($translation2->getTable(), [$translation2->getLangKey() => 'pt', 'title' => 'Title in portuguese']);
    }

    /** @test */
    public function model_is_translated_from_original_to_another_locale_and_excluded_fields_are_not_included()
    {
        $original = Page::create([
            'title' => 'English title',
            'published_at' => '2019-12-03T07:22:29+00:00'
        ]);

        $title = 'Spanish title';
        $page = $original->translateTo($locale = 'es', $data = ['title' => $title]);

        $this->assertEquals($page->original_id, $original->id);
        $this->assertEquals('es', $page->lang);
        $this->assertEquals($title, $page->title);
        $this->assertEquals(null, $page->published_at);

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

    /** @test */
    public function only_returns_current_locale_records_if_lang_global_scope_is_applied()
    {
        $original = Page::create(['title' => 'English title']);
        $original->translateTo('es', ['title' => 'Spanish title']);

        $result = Page::all();

        $this->assertCount(1, $result);
        $this->assertEquals('en', $result[0]->lang);
    }

    /** @test */
    public function lang_global_scope_is_not_applied_if_config_apply_lang_global_scope_is_set_to_false()
    {
        Config::set('laravel-multi-language.apply_lang_global_scope', false);

        $original = Page::create(['title' => 'English title']);
        $original->translateTo('es', ['title' => 'Spanish title']);

        $result = Page::all();

        $this->assertCount(2, $result);
    }

    /** @test */
    public function local_lang_scope_is_applied()
    {
        Config::set('laravel-multi-language.apply_lang_global_scope', false);

        $original = Page::create(['title' => 'English title']);
        $original->translateTo('es', ['title' => 'Spanish title']);

        $result = Page::lang('es')->get();

        $this->assertCount(1, $result);
        $this->assertEquals('es', $result[0]->lang);
    }

    /** @test */
    public function translations_are_loaded_and_do_not_include_the_current_locale()
    {
        $original = Page::create(['title' => 'English title']);
        $translation = $original->translateTo('es', ['title' => 'Spanish title']);

        $result = Page::withTranslations()->get();

        $this->assertCount(1, $result);
        $this->assertEquals('en', $result[0]->lang);
        $this->assertCount(1, $result[0]->translations);
        $this->assertEquals('es', $result[0]->translations[0]->lang);
    }

    /** @test */
    public function it_returns_only_original_records()
    {
        // original records are records where the id == original_id
        $original = Page::create(['title' => 'English title']);
        $original->translateTo('es', ['title' => 'Spanish title']);

        $original = Page::create(['lang' => 'es', 'title' => 'Spanish title 2']);
        $original->translateTo('en', ['title' => 'English title 2']);

        $result = Page::withTranslations()->onlyOriginals()->withoutGlobalScope(LangScope::class)->get();

        $this->assertCount(2, $result);
        $this->assertEquals('en', $result[0]->lang);
        $this->assertEquals('es', $result[1]->lang);
    }

    /** @test */
    public function gets_translation_from_original()
    {
        $original = Page::create(['title' => 'English title']);
        $translation = $original->translateTo('es', ['title' => 'Spanish title']);

        $result = $original->translation('es');

        $this->assertEquals('es', $result->lang);
    }

    /** @test */
    public function gets_original_from_translation()
    {
        $original = Page::create(['title' => 'English title']);
        $translation = $original->translateTo('es', ['title' => 'Spanish title']);

        $result = $translation->translation('en');

        $this->assertEquals('en', $result->lang);
    }

    /** @test */
    public function if_has_translation_from_original()
    {
        $original = Page::create(['title' => 'English title']);
        $translation = $original->translateTo('es', ['title' => 'Spanish title']);

        $result = $original->hasTranslation('es');

        $this->assertTrue($result);
    }

    /** @test */
    public function if_has_translation_from_translation()
    {
        $original = Page::create(['title' => 'English title']);
        $translation = $original->translateTo('es', ['title' => 'Spanish title']);

        $result = $translation->hasTranslation('en');

        $this->assertTrue($result);
    }
}
