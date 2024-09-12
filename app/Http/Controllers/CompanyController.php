<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Resources\CompanyResource;

class CompanyController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $companies = CompanyResource::collection(Company::all());
        } catch (Exception $e) {
            return self::sendFailure($e);
        }
        return self::sendSuccess($companies, 'Retrieved companies successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $company = new CompanyResource(Company::query()->findOrFail($id));
        } catch(Exception $e) {
            return self::sendFailure($e, 404);
        }

        return self::sendSuccess($company, "Retrieved company successfully.");
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
