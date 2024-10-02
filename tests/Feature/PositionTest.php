<?php

use App\Models\Company;
use App\Models\Position;
use Illuminate\Testing\Fluent\AssertableJson;

test('positions response format on success', function () {
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
                ->has('user', fn(AssertableJson $json) => $json
                    ->has('name')
                    ->has('family_name')
                    ->has('username')
                    ->has('email')
                    ->has('type')
                    ->has('company_id')
                    ->has('status'))
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

test('positions.store success response', function () {
    $company = Company::factory()->create();

    $position = Position::factory()->for($company)->makeOne()->toArray();

    $response = $this->postJson('api/v1/positions', $position);

    $response
        ->assertStatus(201)
        ->assertJson(fn(AssertableJson $json) => $json
            ->where('success', true)
            ->where('message', "Position for Company: $company->name created")
            ->has('data')
            ->etc());
});

test('positions.show returns with company on success', function () {
    Position::factory(3)->for(Company::factory())->create();

    $response = $this->getJson('api/v1/positions/2');

    $response
        ->assertStatus(200)
        ->assertJson(fn(AssertableJson $json) => $json
            ->where('success', true)
            ->where('message', "Retrieved position with id: 2")
            ->has('data', fn(AssertableJson $json) => $json
                ->has('company')
                ->has('title')
                ->etc()));
});


test('positions.update returns correct format on success', function () {
    $position = Position::factory()->for(Company::factory())->create();
    $now = now();

    $data = [
        'title' => 'Junior Software Developer',
        'end' => $now->addMonth()->format(DATE_ATOM),
        'type' => 'Part-Time'
    ];

    $response = $this->putJson('api/v1/positions/1', $data);

    $response
        ->assertStatus(201)
        ->assertJson(fn(AssertableJson $json) => $json
            ->where('success', true)
            ->where('message', "Position with id: $position->id updated")
            ->has('data', fn(AssertableJson $json) => $json
                ->has('company')
                ->where('title', $data['title'])
                ->where('end', $data['end'])
                ->where('type', $data['type'])
                ->etc()
            ));
});

test('positions.update validates start and end dates', function () {
    $now = now();
    Position::factory(2)->for(Company::factory())->create();

    $response1 = $this->putJson('api/v1/positions/1',
        ['start' => $now->addDay()]);
    $response2 = $this->putJson('api/v1/positions/2',
        ['end' => $now->subDay()]);

    $response1
        ->assertStatus(422)
        ->assertExactJson([
            'success' => false,
            'message' => ['start' => ['The start field is prohibited.']],
        ]);

    $response2
        ->assertStatus(422)
        ->assertExactJson([
            'success' => false,
            'message' => ['end' => ['The end field must be a date after now.']],
        ]);
});

test('positions.destroy returns correct success response', function () {
    $position = Position::factory()->create();

    $response = $this->deleteJson("/api/v1/positions/$position->id");

    $this->assertSoftDeleted($position);
    $response
        ->assertStatus(200)
        ->assertJson(fn(AssertableJson $json) => $json
            ->where('success', true)
            ->where('message', "Position with id: $position->id deleted")
            ->has('data'));
});

test('positions.restore restores a deleted resource and returns correct message on success',
    function () {
        $position = Position::factory()->create();

        $this->deleteJson("/api/v1/positions/$position->id");
        $this->assertSoftDeleted($position);

        $response = $this->getJson("/api/v1/positions/trash/$position->id");
        $this->assertNotSoftDeleted($position);

        $response
            ->assertStatus(200)
            ->assertJson(fn(AssertableJson $json) => $json
                ->where('success', true)
                ->where('message', "Position with id: 1 restored")
                ->where('data', '1')
            );
    });
