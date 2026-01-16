<?php

namespace Rooberthh\InsightApi\Database;

use Illuminate\Database\Eloquent\Factories\Factory;

class InsightApiRequestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'method' => $this->faker->randomElement(['GET', 'POST', 'PUT', 'DELETE']),
            'route_pattern' => '/api/users/{user}/posts/{post}/',
            'uri' => $this->faker->url(),
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'body' => [],
            'ip_address' => $this->faker->ipv4(),
            'status_code' => 200,
            'response_time_ms' => $this->faker->numberBetween(1000, 100000),
            'captured_at' => now(),
        ];
    }
}
