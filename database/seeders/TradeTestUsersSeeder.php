<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Condition;
use App\Models\Listing;
use App\Models\Location;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

/**
 * Dos usuarios fijos para probar trueque en local.
 * Ejecutar: php artisan trade:demo-users
 */
class TradeTestUsersSeeder extends Seeder
{
    private const PASSWORD = 'password';

    /** @var list<array<string, mixed>> */
    private const USERS = [
        [
            'name' => 'Ana Trueque',
            'username' => 'ana_trueque',
            'email' => 'ana.trueque@marketplace.test',
            'phone' => '3001110001',
            'listing' => [
                'slug' => 'demo-trueque-vestido-ana',
                'title' => 'Vestido floral demo trueque (Ana)',
                'description' => 'Vestido de prueba para trueque en local. Prenda en excelente estado, ideal para validar el flujo de intercambio entre usuarios verificados.',
                'price' => 180_000,
                'accepts_trade' => true,
            ],
        ],
        [
            'name' => 'Bruno Trueque',
            'username' => 'bruno_trueque',
            'email' => 'bruno.trueque@marketplace.test',
            'phone' => '3001110002',
            'listing' => [
                'slug' => 'demo-trueque-camisa-bruno',
                'title' => 'Camisa linen demo trueque (Bruno)',
                'description' => 'Camisa de prueba para ofrecer en trueque. Estado impecable, pensada para pruebas locales del intercambio presencial.',
                'price' => 95_000,
                'accepts_trade' => false,
            ],
        ],
    ];

    public function run(): void
    {
        $this->ensurePrerequisites();

        $locationId = Location::query()->value('id');
        $category = $this->resolveCategory();
        $condition = Condition::query()->where('slug', 'nuevo-con-etiqueta')->firstOrFail();

        foreach (self::USERS as $data) {
            $user = $this->upsertUser($data);

            Profile::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'location_id' => $locationId,
                    'rating_avg' => 4.80,
                    'ratings_count' => 8,
                    'sales_count' => 5,
                    'purchases_count' => 3,
                    'response_rate' => 95.00,
                    'member_since' => now()->subMonths(6),
                ],
            );

            $listingData = $data['listing'];

            Listing::query()->updateOrCreate(
                ['slug' => $listingData['slug']],
                [
                    'user_id' => $user->id,
                    'category_id' => $category->id,
                    'condition_id' => $condition->id,
                    'location_id' => $locationId,
                    'title' => $listingData['title'],
                    'description' => $listingData['description'],
                    'price' => $listingData['price'],
                    'currency' => 'COP',
                    'is_negotiable' => true,
                    'accepts_trade' => $listingData['accepts_trade'],
                    'status' => 'active',
                    'published_at' => now()->subDays(3),
                ],
            );
        }

        $this->command?->newLine();
        $this->command?->info('Usuarios demo trueque listos (contraseña: '.self::PASSWORD.')');
        $this->command?->table(
            ['Nombre', 'Correo para login', 'Anuncio'],
            [
                ['Ana Trueque', 'ana.trueque@marketplace.test', '/anuncios/demo-trueque-vestido-ana'],
                ['Bruno Trueque', 'bruno.trueque@marketplace.test', '/anuncios/demo-trueque-camisa-bruno'],
            ],
        );
        $this->command?->warn('En /login elige "Usar correo en su lugar" (no Google).');
    }

    private function ensurePrerequisites(): void
    {
        if (Location::query()->doesntExist()) {
            $this->command?->warn('Ejecutando LocationSeeder...');
            $this->call(LocationSeeder::class);
        }

        if (Condition::query()->where('slug', 'nuevo-con-etiqueta')->doesntExist()) {
            $this->command?->warn('Ejecutando ConditionSeeder...');
            $this->call(ConditionSeeder::class);
        }

        if (Category::query()->doesntExist()) {
            throw new RuntimeException(
                'No hay categorías. Ejecuta primero: php artisan migrate:fresh --seed'
            );
        }
    }

    private function resolveCategory(): Category
    {
        $category = Category::query()
            ->where('sale_mode', 'marketplace')
            ->where('slug', 'like', 'moda-%')
            ->orderBy('id')
            ->first();

        if ($category !== null) {
            return $category;
        }

        $fallback = Category::query()
            ->where('sale_mode', 'marketplace')
            ->orderBy('id')
            ->first();

        if ($fallback !== null) {
            $this->command?->warn('Usando categoría marketplace genérica: '.$fallback->slug);

            return $fallback;
        }

        throw new RuntimeException(
            'No hay categoría marketplace. Ejecuta: php artisan migrate:fresh --seed'
        );
    }

    /** @param array<string, mixed> $data */
    private function upsertUser(array $data): User
    {
        $user = User::query()->where('email', $data['email'])->first();
        $username = $this->resolveUsername($data['username'], $user?->id);

        $attributes = [
            'name' => $data['name'],
            'username' => $username,
            'phone' => $data['phone'],
            'password' => Hash::make(self::PASSWORD),
            'phone_verified_at' => now(),
            'email_verified_at' => now(),
            'is_verified' => true,
            'verification_level' => 'id_document',
            'status' => 'active',
            'is_admin' => false,
        ];

        if ($user === null) {
            return User::query()->create(array_merge(['email' => $data['email']], $attributes));
        }

        $user->update($attributes);

        return $user->fresh();
    }

    private function resolveUsername(string $desired, ?int $existingUserId): string
    {
        $taken = User::query()
            ->where('username', $desired)
            ->when($existingUserId, fn ($query) => $query->where('id', '!=', $existingUserId))
            ->exists();

        if (! $taken) {
            return $desired;
        }

        $alternative = $desired.'_demo';

        $this->command?->warn("Usuario '{$desired}' ocupado; usando '{$alternative}'.");

        return $alternative;
    }
}
