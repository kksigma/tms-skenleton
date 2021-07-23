<?php

namespace Kksigma\TMS;

use Kksigma\TMS\Commands\TMSCommand;
use Spatie\LaravelPackageTools\Package;
use Kksigma\TMS\Commands\PullTranslationsCommand;
use Kksigma\TMS\Commands\PushTranslationsCommand;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class TMSServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('tms')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_tms_table')
            ->hasCommand(TMSCommand::class);
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                TMSCommand::class,
                PushTranslationsCommand::class,
                PullTranslationsCommand::class,
            ]);
        }
    }
}
