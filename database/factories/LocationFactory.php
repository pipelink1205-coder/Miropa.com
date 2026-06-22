<?php

namespace Database\Factories;

use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Location>
 */
class LocationFactory extends Factory
{
    public function definition(): array
    {
        $cities = [
            ['name' => 'Bogotá', 'city' => 'Bogotá', 'state' => 'Cundinamarca', 'lat' => 4.7110, 'lng' => -74.0721],
            ['name' => 'Medellín', 'city' => 'Medellín', 'state' => 'Antioquia', 'lat' => 6.2442, 'lng' => -75.5812],
            ['name' => 'Cali', 'city' => 'Cali', 'state' => 'Valle del Cauca', 'lat' => 3.4516, 'lng' => -76.5320],
            ['name' => 'Barranquilla', 'city' => 'Barranquilla', 'state' => 'Atlántico', 'lat' => 10.9685, 'lng' => -74.7813],
            ['name' => 'Cartagena', 'city' => 'Cartagena', 'state' => 'Bolívar', 'lat' => 10.3910, 'lng' => -75.4794],
            ['name' => 'Bucaramanga', 'city' => 'Bucaramanga', 'state' => 'Santander', 'lat' => 7.1193, 'lng' => -73.1227],
        ];

        $city = fake()->randomElement($cities);

        return [
            'name' => $city['name'],
            'city' => $city['city'],
            'state' => $city['state'],
            'country' => 'CO',
            'latitude' => $city['lat'] + fake()->randomFloat(4, -0.1, 0.1),
            'longitude' => $city['lng'] + fake()->randomFloat(4, -0.1, 0.1),
        ];
    }
}
