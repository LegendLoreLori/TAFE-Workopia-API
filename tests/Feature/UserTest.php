<?php

use App\Models\Company;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    Sanctum::actingAs(
        User::factory()->state([
            'id' => 0,
            'type' => 'Staff',
            'company_id' => null,
        ])->create(), ['users:administer']
    );
});


test('users.index correct format on success response', function () {
    User::factory(3)->for(Company::factory())->create();

    $response = $this->get('api/v1/users');

    $response->assertStatus(200)
        ->assertJson(fn(AssertableJson $json) => $json
            ->where('success', true)
            ->where('message', 'Retrieved users')
            ->has('data', 4)
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

test('users.store company_id state for client and applicant creation',
    function () {
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


test('user.show success format', function () {
    User::factory()->create();

    $response = $this->getJson('api/v1/users/1');

    $response
        ->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Retrieved user with id: 1',
            'data' => [],
        ]);
});

test('users.update handles email from input', function () {
    $users = User::factory(2)->create();

    $data = [
        'name' => 'Jane',
        'email' => $users[0]["email"],
    ];

    $response1 = $this->putJson("api/v1/users/1", $data);
    $response2 = $this->putJson("api/v1/users/2", $data);

    $response1
        ->assertStatus(201)
        ->assertJson(fn(AssertableJson $json) => $json
            ->where('success', true)
            ->where('message', 'User with id: 1 updated')
            ->has('data', fn(AssertableJson $json) => $json
                ->where('name', 'Jane')
                ->etc()));

    $response2
        ->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => ['email' => ['The email has already been taken.']]
        ]);

});

test('users.destroy returns correct success response', function () {
    $user = User::factory()->create();

    $response = $this->deleteJson("/api/v1/users/$user->id");

    $this->assertSoftDeleted($user);
    $response
        ->assertStatus(200)
        ->assertJson(fn(AssertableJson $json) => $json
            ->where('success', true)
            ->where('message', "User with id: $user->id deleted")
            ->has('data'));
});

test('users.restore restores a deleted resource and returns correct message on success',
    function () {
        $user = User::factory()->create();

        $this->deleteJson("/api/v1/users/$user->id");
        $this->assertSoftDeleted($user);

        $response = $this->getJson("/api/v1/users/trash/$user->id");
        $this->assertNotSoftDeleted($user);

        $response
            ->assertStatus(200)
            ->assertJson(fn(AssertableJson $json) => $json
                ->where('success', true)
                ->where('message', "User with id: 1 restored")
                ->where('data', '1')
            );
    });
