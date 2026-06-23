<?php

use App\Support\FashionCategoryTreeSync;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        FashionCategoryTreeSync::syncNinosBranch();
    }

    public function down(): void
    {
        // Reversible manualmente vía fashion:sync-categories si hace falta restaurar.
    }
};
