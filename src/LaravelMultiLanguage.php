<?php

namespace BrunoFernandes\LaravelMultiLanguage;

use Illuminate\Support\Facades\App;

class LaravelMultiLanguage
{
    public function getLocale()
    {
        return App::getLocale();
    }
}
