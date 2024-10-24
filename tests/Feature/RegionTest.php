<?php

use App\Models\Region;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    Sanctum::actingAs(
        User::factory()->state([
            'id' => 0,
            'type' => 'Staff',
            'company_id' => null,
        ])->create(), ['*']
    );
});

test('v1/regions retrieves successfully', function () {
    Region::factory()->create();

    $response = $this->getJson('/api/v1/regions');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Regions retrieved successfully',
        ]);
});

test('v1/regions returns false when regions are empty', function () {
    $response = $this->getJson('/api/v1/regions');

    $response->assertStatus(404)
        ->assertJson([
            'success' => false,
            'message' => 'Regions not found',
        ]);
});

test('v1/regions/{id} retrieves successfully', function () {
    Region::factory()->create()->count(6);

    $response = $this->getJson('/api/v1/regions/1');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Region retrieved successfully.',
        ]);
});

test('v1/regions/{id} returns false when region outside of array', function () {
    Region::factory(6)->create();

    $response = $this->getJson('/api/v1/regions/7');

    $response->assertStatus(404)
        ->assertJson([
            'success' => false,
            'message' => 'Region with id: 7 not found',
        ]);
});
