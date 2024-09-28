<?php

use App\Models\Company;
use App\Models\Position;
use Illuminate\Testing\Fluent\AssertableJson;

test('positions.index returns correct response on success', function () {
    Position::factory(3)->for(Company::factory())->create();

    $response = $this->getJson('api/v1/positions');

    $response
        ->assertStatus(200)
        ->assertJson(fn(AssertableJson $json) => $json
            ->where('success', true)
            ->where('message', 'Retrieved positions')
            ->has('data', 3)
            ->has('data.0', fn(AssertableJson $json) => $json
                ->has('company', fn(AssertableJson $json) => $json
                    ->has('city')
                    ->has('country')
                    ->has('extension')
                    ->has('logo_path')
                    ->has('name')
                    ->has('state'))
                ->has('start')
                ->has('end')
                ->has('title')
                ->has('description')
                ->has('min_salary')
                ->has('max_salary')
                ->has('currency')
                ->has('benefits')
                ->has('requirements')
                ->has('type')
            ));
});

test('position.index returns correctly formatted error response on failure',
    function () {
        $response = $this->getJson('api/v1/positions');

        $response
            ->assertStatus(418)
            ->assertExactJson([
                'success' => false,
                'message' => 'Unable to retrieve positions at this time, please contact your system administrator'
            ]);
    });
