<?php

namespace App\Services\Common;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;

class CommonService
{
    


    public static function setLocale($locale)
    {
        if (!in_array($locale, ['it', 'en']))
            abort(404);
            App::setLocale($locale);
    }

}