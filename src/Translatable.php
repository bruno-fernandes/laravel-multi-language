<?php

namespace BrunoFernandes\LaravelMultiLanguage;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Builder;
use BrunoFernandes\LaravelMultiLanguage\Scopes\LangScope;
use BrunoFernandes\LaravelMultiLanguage\Exceptions\ModelTranslationAlreadyExistsException;

trait Translatable
{
    /**
     * @return void
     */
    public static function bootTranslatable(): void
    {
        static::creating(function ($model) {
            // set default language if not set
            if (!$model->{$model->getLangKey()}) {
                $model->{$model->getLangKey()} = App::getLocale();
            }
        });

        static::created(function ($model) {
            // Set original field id when created
            if (!$model->{$model->getForeignKey()}) {
                $model->{$model->getForeignKey()} = $model->id;
                $model->save();
            }
        });

        if (config('laravel-multi-language.apply_lang_global_scope')) {
            static::addGlobalScope(new LangScope);
        }
    }

    /**
     * @return string
     */
    public function getLangKey(): String
    {
        return config('laravel-multi-language.lang_key', 'lang');
    }

    /**
     * @return string
     */
    public function getForeignKey(): String
    {
        return config('laravel-multi-language.foreign_key', 'original_id');
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
     * @param String $lang
     * @param array $data
     * @return Illuminate\Database\Eloquent\Model
     */
    public function translateTo($lang, $data = [])
    {
        $excludedFields = ['id', 'lang', 'original_id', 'created_at', 'updated_at'];
        $newLangData = ['lang' => $lang, 'original_id' => $this->original_id];
        $originalData = Arr::except($this->toArray(), $excludedFields);
        $data = Arr::except($data, $excludedFields); // clean up passed data
        $data = array_merge($originalData, $data, $newLangData);

        if ($this->hasTranslation($lang)) {
            throw new ModelTranslationAlreadyExistsException('Translation already exists.', 1);
        }

        // TODO: add event here: model.translating

        $translation =  self::create($data);

        // TODO: add event here: model.translated

        return $translation;
    }

    /**
     * Checks if record has a  translation
     *
     * @param String $lang
     * @return Model|null
     */
    public function hasTranslation($lang)
    {
        if ($this->lang == $lang) {
            return true;
        }

        return self::withoutGlobalScope(LangScope::class)
            ->where('lang', $lang)
            ->where('original_id', $this->original_id)
            ->exists();
    }

    /**
     * Get translation
     *
     * @param String $lang
     * @return Model|null
     */
    public function translation($lang)
    {
        return self::withoutGlobalScope(LangScope::class)
            ->where('lang', $lang)
            ->where('original_id', $this->original_id)
            ->first();
    }

    /*
     * Return translation excluding the current language
     *
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function scopeWithTranslations(Builder $query, $lang = null, $fields = [])
    {
        return $query->with(['translations' => function ($q) use ($lang, $fields) {
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
        return $query->where($this->getLangKey(), $lang ?: App::getLocale());
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string                                $lang
     *
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function scopeNotLang(Builder $query, $lang = null)
    {
        return $query->where($this->getLangKey(), '!=', $lang ?: App::getLocale())
            ->withoutGlobalScope(LangScope::class);
    }

    /**
     * Return only original rows
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function scopeOnlyOriginal(Builder $query)
    {
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
