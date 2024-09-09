<?php

use App\Models\Company;
use Illuminate\Testing\Fluent\AssertableJson;

test('companies.index returns all companies', function () {
    Company::factory(3)
        ->create();

    $response = $this->get('api/v1/companies');

    $response->assertStatus(200)
        ->assertJson(fn(AssertableJson $json) => $json
            ->where('status', 'ok')
            ->where('message', 'Retrieved companies successfully.')
            ->has('data', 3)
        );
});
