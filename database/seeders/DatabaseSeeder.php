<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Position;
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
        User::factory()->state([
            'type' => 'Staff',
            'username' => 'superadmin',
            'password' => 'password',
            'email' => 'test@email.com',
            'company_id' => null,
        ])->create();

        $this->call([
            RegionSeeder::class,
        ]);

        $companies = Company::factory(5)->create();
        $users = User::factory(10)
            ->recycle($companies)
            ->state(['type' => 'Client', 'status' => 'Active'])
            ->create();
        Position::factory(50)
            ->recycle($users)
            ->create();
        User::factory(200)
            ->state([
                'type' => 'Applicant',
                'company_id' => null
            ])
            ->create();
    }
}
