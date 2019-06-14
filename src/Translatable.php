<?php

namespace BrunoFernandes\LaravelMultiLanguage;

use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Builder;
use BrunoFernandes\LaravelMultiLanguage\Exceptions\ModelTranslationAlreadyExistsException;

trait Translatable
{
    /**
     *
     *
     * @return void
     */
    public static function bootTranslatable()
    {
        static::creating(function ($model) {
            // set default language if not set
            if (!$model->{$model->getLangKey()}) {
                $model->{$model->getLangKey()} = app()->getLocale();
            }
        });

        static::created(function ($model) {
            // On model creation set original_id field
            if (!$model->{$model->getForeignKey()}) {
                $model->{$model->getForeignKey()} = $model->id;
                $model->save();
            }
        });
    }

    /**
     * @return string
     */
    public function getLangKey()
    {
        // default: lang
        return config('laravel-multi-language.lang_key');
    }

    /**
     * @return string
     */
    public function getForeignKey()
    {
        // default: original_id
        return config('laravel-multi-language.foreign_key');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function translations()
    {
        return $this->hasMany(get_class($this), $this->getForeignKey(), $this->getForeignKey());
    }

    /**
     * Translate model to another language
     *
     * @param [type] $lang
     * @param array $data
     * @return Illuminate\Database\Eloquent\Model
     */
    public function translateTo($lang, $data = [])
    {
        $excludedFields = ['id', 'lang', 'original_id', 'created_at', 'updated_at'];
        $newLangData = ['lang' => $lang, 'original_id' => $this->id];

        $data = array_merge(
            Arr::except($this->toArray(), $excludedFields), // original data
            Arr::except($data, $excludedFields), // passed data that overides the original
            $newLangData
        );

        if ($this->lang == $lang || self::where($newLangData)->exists()) {
            throw new ModelTranslationAlreadyExistsException('Translation already exists.', 1);
        }

        return self::create($data);
    }

    /*
     * Return translation excluding the current language
     *
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function scopeWithTranslations(Builder $query, $lang = null, $fields = [])
    {
        return $query->with(['translations' => function ($q) use ($lang, $fields) {
            // $q->whereRaw('`'.$this->getTable().'`.`id` != `'.$this->getTable().'`.`id`');
            // $q->notLang($lang)->select(['id', 'lang', 'original_id', 'first_name']);
            $q->notLang($lang, $fields);
        }]);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string                                $lang
     *
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function scopeLang(Builder $query, $lang = null)
    {
        return $query->where($this->getLangKey(), $lang ?: app()->getLocale());
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string                                $lang
     *
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function scopeNotLang(Builder $query, $lang = null)
    {
        if (!$lang) $lang = app()->getLocale();
        return $query->where($this->getLangKey(), '!=', $lang);
    }

    /**
     * Return only original rows
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function scopeOnlyOriginal(Builder $query)
    {
        // TODO: create tests
        return $query->whereRaw($this->getTable() . '.id = ' . $this->getTable() . '.' . $this->getForeignKey());
    }

    /* @param \Illuminate \Database \Eloquent \Builder $query
    *
    * @return \Illuminate\Database\Eloquent\Builder | static
    */
    public function scopeOnlyOriginals(Builder $query)
    {
        return $this->scopeOnlyOriginal($query);
    }
}
