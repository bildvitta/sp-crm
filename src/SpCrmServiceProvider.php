<?php

namespace BildVitta\SpCrm;

use BildVitta\SpCrm\Console\Commands\DataImport\Crm\CustomerImportCommand;
use BildVitta\SpCrm\Console\Commands\InstallSp;
use BildVitta\SpCrm\Console\Commands\Messages\CustomersWorkerCommand;
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
     * @var string $seeder
     */
    protected string $seeder = 'SpCrmSeeder';
    
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
                'add_column_is_active_on_customers_table',
                'drop_column_kind_on_customers_table',
            ])
            ->runsMigrations();
    
        $package
            ->name('sp-crm')
            ->hasCommands([
                CustomerImportCommand::class,
                CustomersWorkerCommand::class,
                InstallSp::class,
            ]);

        $this->publishes([
            $package->basePath("/../database/seeders/{$this->seeder}.php.stub")
            => database_path("seeders/{$this->seeder}.php")
        ], 'seeders');
    }
}
