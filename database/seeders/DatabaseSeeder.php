<?php

namespace Database\Seeders;

use App\Models\Endpoint;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        try {
            $endpoint = new Endpoint([
                'slug' => 'demo',
                'title' => 'Demo webhook endpoint',
            ]);
            $endpoint->save();
            $endpoint->targets()->createMany([
                [
                    'title' => 'relay GET only',
                    'method' => 'GET',
                    'uri' => 'https://httpbin.org/get',
                    'rule' => "req.isMethod('get')",
                ],
                [
                    'title' => 'demo POST target',
                    'method' => 'POST',
                    'uri' => 'https://httpbin.org/post',
                    'rule' => "",
                    'headers' => json_encode(['Content-Type' => 'application/json'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
                    'body' => json_encode(['name' => "{{ req.input('name', 'not set') }}"], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
                ],
            ]);
        } catch (\Exception) {
            // skip
        }
    }
}
