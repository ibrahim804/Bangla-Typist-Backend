<?php

namespace App\Support;

class SentryLaravelServiceProvider extends \Sentry\Laravel\ServiceProvider
{
    public static $abstract = 'sentry-laravel';
}