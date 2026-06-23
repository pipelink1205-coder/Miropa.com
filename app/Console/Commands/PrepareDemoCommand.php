<?php

namespace App\Console\Commands;

use Database\Seeders\BrandSeeder;
use Database\Seeders\ConditionSeeder;
use Database\Seeders\FashionCategorySeeder;
use Database\Seeders\FashionListingSeeder;
use Database\Seeders\ImpactFactorSeeder;
use Database\Seeders\LocationSeeder;
use Database\Seeders\UniverseSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class PrepareDemoCommand extends Command
{
    protected $signature = 'marketplace:prepare-demo
                            {--fresh : Ejecutar migrate:fresh antes de sembrar (¡borra datos!)}';

    protected $description = 'Prepara el entorno demo/staging: taxonomía Moda, universos y anuncios realistas';

    public function handle(): int
    {
        if ($this->option('fresh')) {
            if (! $this->confirm('migrate:fresh borrará toda la base de datos. ¿Continuar?')) {
                return self::FAILURE;
            }
            Artisan::call('migrate:fresh', [], $this->output);
        }

        $this->info('Sembrando datos base…');
        $this->callSilent('db:seed', ['--class' => LocationSeeder::class]);
        $this->callSilent('db:seed', ['--class' => ConditionSeeder::class]);
        $this->callSilent('db:seed', ['--class' => BrandSeeder::class]);
        $this->callSilent('db:seed', ['--class' => FashionCategorySeeder::class]);
        $this->callSilent('db:seed', ['--class' => UniverseSeeder::class]);
        $this->callSilent('db:seed', ['--class' => ImpactFactorSeeder::class]);
        $this->callSilent('db:seed', ['--class' => UserSeeder::class]);

        $this->info('Creando catálogo Moda demo…');
        $this->callSilent('db:seed', ['--class' => FashionListingSeeder::class]);

        if (! file_exists(public_path('storage'))) {
            Artisan::call('storage:link', [], $this->output);
        }

        $this->newLine();
        $this->components->info('Demo listo.');
        $this->line('  Usuario admin: demo@marketplace.test / password');
        $this->line('  Moda: /moda/mujer · /moda/hombre · /moda/ninos');
        $this->line('  Publicar: /listings/create');

        return self::SUCCESS;
    }
}
