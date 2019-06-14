<?php

namespace BrunoFernandes\LaravelMultiLanguage;

use Illuminate\Database\Eloquent\Builder;

trait Translatable
{
    /**
     * On model creation set original_id field
     *
     * @return void
     */
    public static function bootTranslatable()
    {
        static::created(function ($model) {
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
