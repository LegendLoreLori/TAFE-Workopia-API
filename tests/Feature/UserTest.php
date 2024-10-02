<?php

use App\Models\User;
use App\Models\Company;
use App\Models\Position;
use Illuminate\Testing\Fluent\AssertableJson;

test('users.index correct format on success response', function () {
    User::factory(3)->for(Company::factory())->create();

    $response = $this->get('api/v1/users');

    $response->assertStatus(200)
        ->assertJson(fn(AssertableJson $json) => $json
            ->where('success', true)
            ->where('message', 'Retrieved users')
            ->has('data', 3)
            ->has('data.0', fn(AssertableJson $json) => $json
                ->has('company_id')
                ->has('name')
                ->has('family_name')
                ->has('username')
                ->has('email')
                ->has('type')
                ->has('company_id')
                ->has('status'))
        );
});

test('users.index returns error response on failure',
    function () {
        $response = $this->getJson('api/v1/users');

        $response
            ->assertStatus(418)
            ->assertExactJson([
                'success' => false,
                'message' => 'Unable to retrieve users at this time, please contact your system administrator'
            ]);
});
