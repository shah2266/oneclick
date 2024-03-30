<?php

namespace Database\Seeders;

use App\Models\IofCompany;
use App\Models\User;
use App\Models\UserTheme;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        User::factory()->create();

        $this->call([IofcompanySeeder::class]);
        $this->call([NoclickMailTemplateSeeder::class]);

        // Seed with default "Dark" theme
        UserTheme::factory()->create();
        // Seed with "Light" theme
        UserTheme::factory()->light()->create();
    }
}
