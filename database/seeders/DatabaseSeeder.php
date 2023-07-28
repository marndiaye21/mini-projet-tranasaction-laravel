<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Account;
use App\Models\Client;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $initial = ["78", "77", "70", "76", "75"];

        for ($i = 0; $i < 10; $i++) {
            Client::create([
                "fullname" => fake("fr_FR")->name(),
                "phone" => $initial[rand(0, count($initial))] . fake()->unique()->numerify("#######"),
                "created_at" => now(),
                "updated_at" => now()
            ]);
        }

        $accountTypes = ["Orange Money = OM", "Wave = WV", "Wari = WR", "Compte Bancaire = CB"];
        $clients = Client::all();

        for ($i=0; $i < 7; $i++) {
            $client = $clients[rand(0, count($clients) - 1)];
            $accountType = $accountTypes[rand(0, count($accountTypes) - 1)];
            Account::create(
                [
                    "account_number" => trim(explode("=", $accountType)[1], ' ') . "_" . $client->phone,
                    "balance" => rand(1000, 10000),
                    "account_type" => $accountType,
                    "client_id" => $client->id,
                ]
            );
        }
        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
