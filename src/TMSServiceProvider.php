<?php

namespace Kksigma\TMS;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Kksigma\TMS\Commands\TMSCommand;

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
}
