<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Resources\CompanyResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CompanyController extends Controller
{
    /**
     * List all companies.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $companies = Company::all();
        if ($companies->isEmpty()) {
            return self::sendFailure('Unable to retrieve companies at this time, please contact your administrator',
                418);
        }

        return self::sendSuccess(CompanyResource::collection($companies),
            'Retrieved companies');
    }

    /**
     * Add a company to the database.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = validator::make($request->all(), [
            'city' => 'required|string|between:2,255',
            'state' => 'required|string|between:2,16',
            'country' => 'required|string|between:2,255',
            'name' => [
                'required', 'string', 'between:2,255',
                function (string $attribute, mixed $value, \Closure $fail) use (
                    $request
                ) {
                    $records = DB::table('companies')
                        ->select('*')
                        ->where('name', $request->input('name'))
                        ->where('city', $request->input('city'))
                        ->where('state', $request->input('state'))
                        ->where('country', $request->input('country'))
                        ->get();
                    if ($records->isNotEmpty()) {
                        $fail("Company name, state, and country must be a unique combination");
                    }
                }
            ],
            'logo' => 'sometimes|image|mimes:jpg,jpeg,png'
        ]);

        try {
            $validator->validate();
        } catch (ValidationException $e) {
            $messages = $e->errors();
            return self::sendFailure($messages, 422);
        }

        if ($request->hasFile('logo')) {
            $logo_path = $request->file('logo')->store('public');
            $request->merge(['logo_path' => $logo_path]);
        }

        $company = Company::create($request->all());
        $company = new CompanyResource($company);

        return self::sendSuccess($company,
            "Company created with id: $company->id", 201);
    }

    /**
     * Display a single company.
     *
     * @param  string  $id
     * @return JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        $company = new CompanyResource(Company::find($id));
        if ($company->isEmpty()) {
            return self::sendFailure('Specified company not found', 404);
        }

        return self::sendSuccess($company, "Retrieved company");
    }

    /**
     * Update the specified company in the database.
     *
     * @param  Request  $request
     * @param  string  $id
     * @return JsonResponse
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $company = Company::find($id);
        $validator = validator::make($request->all(), [
            'name' => [
                'required', 'string', 'between:2,255',
                function (
                    string $attribute,
                    mixed $value,
                    \Closure $fail
                ) use ($request) {
                    $records = DB::table('companies')
                        ->select('*')
                        ->where('name', $request->input('name'))
                        ->where('city', $request->input('city'))
                        ->where('state', $request->input('state'))
                        ->where('country', $request->input('country'))
                        ->get();
                    if ($records->isNotEmpty()) {
                        $fail("Company name, state, and country must be a unique combination");
                    }
                }
            ],
            'city' => 'sometimes|string|between:2,255',
            'state' => 'sometimes|string|between:2,16',
            'country' => 'sometimes|string|between:2,255',
            'logo' => 'sometimes|image|mimes:jpg,jpeg,png',
        ]);

        try {
            $validator->validate();
        } catch (ValidationException $e) {
            $message = $e->errors();
            return self::sendFailure($message, 422);
        }

        if ($request->hasFile('logo')) {
            $logo_path = $request->file('logo')->store('public');
            $request->merge(['logo_path' => $logo_path]);
        }

        $company->update($request->all());

        $company = new CompanyResource($company);
        return self::sendSuccess($company, "Company with id: $id updated", 201);
    }


    /**
     * Soft delete the specified company from the database
     *
     * @param  string  $id
     * @return JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        $company = Company::find($id);
        if ($company === null) {
            return self::sendFailure("Specified company not found", 404);
        }
        $company->delete();
        $company = new CompanyResource($company);

        return self::sendSuccess($company, "Company with id: $id deleted", 200);
    }

    /**
     * Restore the specified soft deleted company from trash.
     *
     * @param  string  $id
     * @return JsonResponse
     */
    public function restore(string $id): JsonResponse
    {
        Company::withTrashed()
            ->where('id', $id)
            ->restore();

        return self::sendSuccess($id, "Company with id: $id restored", 200);
    }
}
