<?php

namespace BildVitta\SpCrm;

use BildVitta\MessagesCrm\Console\Commands\DataImport\Crm\CustomerImportCommand;
use BildVitta\MessagesCrm\Console\Commands\Messages\CustomersWorkerCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

/**
 * Class SpCrmServiceProvider.
 *
 * @package BildVitta\SpCrm
 */
class SpCrmServiceProvider extends PackageServiceProvider
{
    /**
     * @param  Package  $package
     *
     * @return void
     */
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('sp-crm')
            ->hasConfigFile(['sp-crm'])
            ->hasMigrations([
                'create_sp_crm_customers_table',
                'create_sp_crm_bonds_table',
            ])
            ->runsMigrations();
    
        $package
            ->name('sp-crm')
            ->hasCommands([
                CustomerImportCommand::class,
                CustomersWorkerCommand::class,
            ]);
    }
}
