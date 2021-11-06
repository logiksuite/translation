<?php

namespace LogikSuite\Translation;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use LogikSuite\Build\Console\ServeBuildCommand;

/**
 * Translation module service provider
 *
 * @copyright 2021 LogikSuite
 * @license MIT
 */
class TranslationServiceProvider extends ServiceProvider
{

    public function boot()
    {

        $this->publishes([
            __DIR__ . '/../config/logiksuite/translation.php' => config_path('logiksuite/translation.php'),
        ]);
    }
}
