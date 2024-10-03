<?php

use App\Models\User;
use App\Models\Company;
use App\Models\Position;
use Illuminate\Database\Eloquent\Factories\Sequence;
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

test('users.store company_id state for client and applicant creation', function () {
    $password = [
     'password' => 'Password1',
     'password_confirmation' => 'Password1',
    ];
    $applicant = User::factory()->state([
        'company_id' => null,
        'type' => 'Applicant',
    ])->makeOne()->toArray();
    $applicant = array_merge($applicant, $password);

    $client = User::factory()->state([
        'type' => 'Client',
    ])->makeOne()->toArray();
    $client = array_merge($client, $password);

    $responseApplicant = $this->postJson('api/v1/users', $applicant);
    $responseClient = $this->postJson('api/v1/users', $client);

    $responseApplicant
        ->assertStatus(201)
        ->assertJson(fn(AssertableJson $json) => $json
            ->where('success', true)
            ->where('message', 'User with id: 1 created')
            ->has('data', fn(AssertableJson $json) => $json
                ->where('company_id', null)
                ->where('type', 'Applicant')
                ->etc()));

    $responseClient
        ->assertStatus(201)
        ->assertJson(fn(AssertableJson $json) => $json
            ->where('success', true)
            ->where('message', 'User with id: 2 created')
            ->has('data', fn(AssertableJson $json) => $json
                ->where('company_id', 1)
                ->where('type', 'Client')
                ->etc()));
});
