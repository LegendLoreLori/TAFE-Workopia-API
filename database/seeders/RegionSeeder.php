<?php

namespace Database\Seeders;

use App\Models\Region;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class RegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Code inspired by:
     *   https://www.itsolutionstuff.com/post/how-to-create-seeder-with-json-data-in-laravelexample.html
     * Data from:
     *   https://github.com/dr5hn/countries-states-cities-database
     */
    public function run(): void
    {
        Region::query()->truncate();

        $json = File::get('database/data/regions.json');

        $countries = json_decode($json);
        foreach ($countries as $key => $value) {
            Region::query()->create([
                'name' => $value->name,
            ]);
        }
    }
}
