<?php

namespace App\Console\Commands;

use App\Support\FashionListingCategoryMigrator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MigrateFashionCategoriesCommand extends Command
{
    protected $signature = 'fashion:migrate-categories
                            {--dry-run : Simular sin escribir cambios}
                            {--rollback : Revertir migración de categorías}';

    protected $description = 'Migra anuncios de moda del árbol legacy (6 categorías) al árbol jerárquico';

    public function handle(FashionListingCategoryMigrator $migrator): int
    {
        if ($this->option('rollback')) {
            $result = $migrator->rollback();
            $this->info("Revertidos {$result['restored']} anuncios.");

            return self::SUCCESS;
        }

        $dryRun = (bool) $this->option('dry-run');
        if ($dryRun) {
            $this->warn('Modo simulación — no se guardarán cambios.');
        }

        $report = $migrator->migrate($dryRun);

        $path = storage_path('logs/fashion-category-migration-'.now()->format('Y-m-d-His').'.json');
        File::put($path, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->table(
            ['Métrica', 'Cantidad'],
            [
                ['Mapeados', $report['mapped']],
                ['Sin clasificar', $report['unclassified']],
                ['Omitidos', $report['skipped']],
            ],
        );

        $this->info("Reporte guardado en: {$path}");

        if ($report['unclassified'] > 0) {
            $this->warn("Hay {$report['unclassified']} anuncios sin clasificar — revisar en admin.");
        }

        return self::SUCCESS;
    }
}
