<?php

namespace BrunoFernandes\LaravelMultiLanguage\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use BrunoFernandes\LaravelMultiLanguage\Translatable;

/**
 * Test model
 */
class Page extends Model
{
    use Translatable;

    protected $guarded = [];

    protected $excludeFieldsFromTranslation = ['published_at'];
}
