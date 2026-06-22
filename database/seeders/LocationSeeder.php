<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $locations = [
            ['name' => 'Bogotá', 'city' => 'Bogotá', 'state' => 'Cundinamarca', 'country' => 'CO', 'latitude' => 4.7110, 'longitude' => -74.0721],
            ['name' => 'Medell��n', 'city' => 'Medellín', 'state' => 'Antioquia', 'country' => 'CO', 'latitude' => 6.2442, 'longitude' => -75.5812],
            ['name' => 'Cali', 'city' => 'Cali', 'state' => 'Valle del Cauca', 'country' => 'CO', 'latitude' => 3.4516, 'longitude' => -76.5320],
            ['name' => 'Barranquilla', 'city' => 'Barranquilla', 'state' => 'Atlántico', 'country' => 'CO', 'latitude' => 10.9685, 'longitude' => -74.7813],
            ['name' => 'Cartagena', 'city' => 'Cartagena', 'state' => 'Bolívar', 'country' => 'CO', 'latitude' => 10.3910, 'longitude' => -75.4794],
            ['name' => 'Bucaramanga', 'city' => 'Bucaramanga', 'state' => 'Santander', 'country' => 'CO', 'latitude' => 7.1193, 'longitude' => -73.1227],
            ['name' => 'Pereira', 'city' => 'Pereira', 'state' => 'Risaralda', 'country' => 'CO', 'latitude' => 4.8133, 'longitude' => -75.6961],
            ['name' => 'Manizales', 'city' => 'Manizales', 'state' => 'Caldas', 'country' => 'CO', 'latitude' => 5.0689, 'longitude' => -75.5174],
        ];

        foreach ($locations as $location) {
            Location::firstOrCreate(['city' => $location['city']], $location);
        }
    }
}
