<?php

namespace App\Console\Commands;

use Database\Seeders\TradeTestUsersSeeder;
use Illuminate\Console\Command;

class SeedTradeDemoUsersCommand extends Command
{
    protected $signature = 'trade:demo-users';

    protected $description = 'Crea o restablece Ana y Bruno para probar trueque (contraseña: password)';

    public function handle(): int
    {
        $this->call('db:seed', [
            '--class' => TradeTestUsersSeeder::class,
            '--force' => true,
        ]);

        $this->newLine();
        $this->components->info('Inicia sesión con correo (no Google):');
        $this->line('  Ana:   ana.trueque@marketplace.test');
        $this->line('  Bruno: bruno.trueque@marketplace.test');
        $this->line('  Clave: password');
        $this->newLine();
        $this->line('En /login → "Usar correo en su lugar"');

        return self::SUCCESS;
    }
}
