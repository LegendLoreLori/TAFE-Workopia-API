<?php

use App\Models\Company;
use Illuminate\Foundation\Testing\Concerns\InteractsWithDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\Fluent\AssertableJson;

uses(InteractsWithDatabase::class);

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

test('companies.index returns formatted response on failure', function () {
    $response = $this->getJson('api/v1/companies');

    $response->assertStatus(418)
        ->assertExactJson(
            [
                'success' => false,
                'message' => 'Unable to retrieve companies at this time, please contact your administrator',
            ]
        );
});

test('companies.show returns formatted response on failure', function () {
   $response = $this->getJson('api/v1/companies/1');

   $response->assertStatus(404)
       ->assertExactJson([
          'success' => false,
          'message' => 'Specified company not found'
       ]);
});

test('companies.store returns correct response on success', function () {
    Storage::fake('local');

    $file = UploadedFile::fake()->image('logo.jpeg');

    $response = $this->post('api/v1/companies', [
        "name" => "foo",
        "city" => "bar",
        "state" => "baz",
        "country" => "bux",
        "logo" => $file
    ]);

    Storage::disk('local')->assertExists("/public/{$file->hashName()}");

    $response
        ->assertStatus(201)
        ->assertJson(fn(AssertableJson $json) => $json
            ->where('success', true)
            ->where('message', 'Company created with id: 1')
            ->has('data', fn(AssertableJson $json) => $json
                ->where('name', "foo")
                ->where('city', "bar")
                ->where('state', "baz")
                ->where('country', "bux")
                ->where('logo_path', "public/{$file->hashName()}")
                ->where('extension', 'jpg')));
});

test('companies.store logo_path is null when no image uploaded', function () {
    $response = $this->postJson('api/v1/companies', [
        "name" => "foo",
        "city" => "bar",
        "state" => "baz",
        "country" => "bux"
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
        ->assertStatus(422)
        ->assertExactJson([
            'success' => false,
            'message' => [
                'name' => ['Company name, state, and country must be a unique combination']
            ]
        ]);
});

test('companies.update returns correct success response', function () {
    Company::factory(3)->create();

    $response = $this->putJson('api/v1/companies/2', [
        "name" => "foo",
        "city" => "bar",
        "state" => "baz",
        "country" => "qux"
    ]);

    $response
        ->assertStatus(201)
        ->assertJson(fn(AssertableJson $json) => $json
            ->has('success')
            ->where('message', 'Company with id: 2 updated')
            ->has('data', fn(AssertableJson $json) => $json
                ->where('name', 'foo')
                ->where('city', 'bar')
                ->where('state', 'baz')
                ->where('country', 'qux')
                ->has('logo_path')
                ->has('extension')
            ));
});

test('companies.update early terminates if id does not exist', function () {
    $response = $this->putJson('api/v1/companies/2', [
        "name" => "foo",
        "city" => "bar",
        "state" => "baz",
        "country" => "qux"
    ]);

    $response
        ->assertStatus(404)
        ->assertExactJson([
           'success' => false,
           'message' => 'Company with id: 2 not found'
        ]);
});

test('companies.destroy returns correct success response', function () {
    $company = Company::factory()->create();

    $response = $this->deleteJson("/api/v1/companies/$company->id");

    $this->assertSoftDeleted($company);
    $response
        ->assertStatus(200)
        ->assertJson(fn(AssertableJson $json) => $json
            ->where('success', true)
            ->where('message', "Company with id: $company->id deleted")
            ->has('data'));
});

test('companies.restore restores a deleted resource and returns correct message on success',
    function () {
        $company = Company::factory()->create();

        $this->deleteJson("/api/v1/companies/$company->id");
        $this->assertSoftDeleted($company);

        $response = $this->getJson("/api/v1/companies/trash/$company->id");
        $this->assertNotSoftDeleted($company);

        $response
            ->assertStatus(200)
            ->assertJson(fn(AssertableJson $json) => $json
                ->where('success', true)
                ->where('message', "Company with id: 1 restored")
                ->where('data', '1')
            );
    });
