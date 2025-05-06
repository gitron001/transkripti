<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ApiCredit;

class ApiCreditSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $ads = [
            ['id' => 1],

        ];

        foreach ($ads as $ad) {
            ApiCredit::updateOrCreate(['id' => $ad['id']], $ad);
        }
    }
}
