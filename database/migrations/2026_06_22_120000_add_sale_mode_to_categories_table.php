<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->enum('sale_mode', ['marketplace', 'classified'])
                ->default('marketplace')
                ->after('is_active');
        });

        $classifiedParentSlugs = [
            'vehiculos',
            'hogar-y-jardin',
            'herramientas',
            'arte-y-antiguedades',
        ];

        $parentIds = DB::table('categories')
            ->whereIn('slug', $classifiedParentSlugs)
            ->pluck('id');

        if ($parentIds->isNotEmpty()) {
            DB::table('categories')
                ->whereIn('id', $parentIds)
                ->orWhereIn('parent_id', $parentIds)
                ->update(['sale_mode' => 'classified']);
        }
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('sale_mode');
        });
    }
};
