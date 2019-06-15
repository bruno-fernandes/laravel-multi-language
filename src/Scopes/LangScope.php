<?php

namespace BrunoFernandes\LaravelMultiLanguage\Scopes;

use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class LangScope implements Scope
{
    /**
     * Apply lang scope
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $builder->where($model->getLangKey(), App::getLocale());
    }
}
