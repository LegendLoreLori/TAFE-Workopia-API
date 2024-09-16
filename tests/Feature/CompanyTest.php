<?php

use App\Models\Company;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\Fluent\AssertableJson;

test('companies.index returns correct companies on success', function () {
    Company::factory(3)
        ->create();

    $response = $this->getJson('api/v1/companies');

    $response->assertStatus(200)
        ->assertJson(fn(AssertableJson $json) => $json
            ->where('success', true)
            ->where('message', 'Retrieved companies')
            ->has('data', 3)
        );
});

test('companies.store returns correct response on success', function () {
    Storage::fake('local');

    $file = UploadedFile::fake()->image('logo.jpeg');

    $response = $this->post('api/v1/companies', [
        "name" => "Big Oil",
        "city" => "Sydney",
        "state" => "New South Wales",
        "country" => "Australia",
        "logo" => $file
    ]);

    Storage::disk('local')->assertExists("/public/{$file->hashName()}");

    $response
        ->assertStatus(201)
        ->assertJson(fn(AssertableJson $json) => $json
            ->where('success', true)
            ->where('message', 'Company created with id: 1')
            ->has('data', fn(AssertableJson $json) => $json
                ->where('name', "Big Oil")
                ->where('city', "Sydney")
                ->where('state', "New South Wales")
                ->where('country', "Australia")
                ->where('logo_path', "public/{$file->hashName()}")
                ->where('extension', 'jpg')
                ->has('created_at')
                ->has('updated_at')));
});

test('companies.store logo_path is null when no image uploaded', function () {
    $response = $this->postJson('api/v1/companies', [
        "name" => "Big Oil",
        "city" => "Sydney",
        "state" => "New South Wales",
        "country" => "Australia"
    ]);

    $response
        ->assertStatus(201)
        ->assertJson([
            "data" => [
                "logo_path" => null,
                "extension" => null,
            ]
        ]);
});

test('companies.store enforces unique company combination', function () {
    $company = Company::factory()->create();

    $response = $this->postJson('api/v1/companies', [
        "name" => $company->name,
        "city" => $company->city,
        "state" => $company->state,
        "country" => $company->country,
    ]);

    $response
        ->assertStatus(400)
        ->assertExactJson([
            'success' => false,
            'message' => 'Company name, state, and country must be a unique combination'
        ]);
});
