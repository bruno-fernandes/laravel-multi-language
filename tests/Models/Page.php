<?php

namespace BrunoFernandes\LaravelMultiLanguage\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use BrunoFernandes\LaravelMultiLanguage\Translatable;
use Carbon\Carbon;

/**
 * Test model
 */
class Page extends Model
{
    use Translatable;

    protected $guarded = [];

    protected $dates = ['published_at', 'starts_at'];

    protected $casts = [
        'published_at' => 'datetime:' . \DateTime::ISO8601,
        'starts_at' => 'datetime:' . \DateTime::ISO8601,
        'created_at' => 'datetime:' . \DateTime::ISO8601,
        'updated_at' => 'datetime:' . \DateTime::ISO8601
    ];

    protected $excludeFieldsFromTranslation = ['published_at'];

    public function setStartsAtAttribute($value)
    {
        $this->attributes['starts_at'] = Carbon::parse($value);
    }

    public function setPublishedAtAttribute($value)
    {
        $this->attributes['published_at'] = Carbon::parse($value);
    }

    // protected function serializeDate(\DateTimeInterface $date)
    // {
    //     return $date->toIso8601String();
    // }
}
