<?php

namespace App\Http\Controllers;

use App\Http\Resources\PositionResource;
use App\Models\Company;
use App\Models\Position;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

// There's probably a better implementation for this
const currencyCodes = [
    "AED", "AFN", "ALL", "AMD", "ANG", "AOA", "ARS", "AUD", "AWG", "AZN", "BAM",
    "BBD", "BDT", "BGN", "BHD", "BIF", "BMD", "BND", "BOB", "BOV", "BRL", "BSD",
    "BTN", "BWP", "BYR", "BZD", "CAD", "CDF", "CHE", "CHF", "CHW", "CLF", "CLP",
    "CNY", "COP", "COU", "CRC", "CUC", "CUP", "CVE", "CZK", "DJF", "DKK", "DOP",
    "DZD", "EGP", "ERN", "ETB", "EUR", "FJD", "FKP", "GBP", "GEL", "GHS", "GIP",
    "GMD", "GNF", "GTQ", "GYD", "HKD", "HNL", "HRK", "HTG", "HUF", "IDR", "ILS",
    "INR", "IQD", "IRR", "ISK", "JMD", "JOD", "JPY", "KES", "KGS", "KHR", "KMF",
    "KPW", "KRW", "KWD", "KYD", "KZT", "LAK", "LBP", "LKR", "LRD", "LSL", "LTL",
    "LVL", "LYD", "MAD", "MDL", "MGA", "MKD", "MMK", "MNT", "MOP", "MRO", "MUR",
    "MVR", "MWK", "MXN", "MXV", "MYR", "MZN", "NAD", "NGN", "NIO", "NOK", "NPR",
    "NZD", "OMR", "PAB", "PEN", "PGK", "PHP", "PKR", "PLN", "PYG", "QAR", "RON",
    "RSD", "RUB", "RWF", "SAR", "SBD", "SCR", "SDG", "SEK", "SGD", "SHP", "SLL",
    "SOS", "SRD", "SSP", "STD", "SYP", "SZL", "THB", "TJS", "TMT", "TND", "TOP",
    "TRY", "TTD", "TWD", "TZS", "UAH", "UGX", "USD", "USN", "USS", "UYI", "UYU",
    "UZS", "VEF", "VND", "VUV", "WST", "XAF", "XAG", "XAU", "XBA", "XBB", "XBC",
    "XBD", "XCD", "XDR", "XFU", "XOF", "XPD", "XPF", "XPT", "XTS", "XXX", "YER",
    "ZAR", "ZMW"
];

/**
 * @group Position
 *
 * API endpoints for job positions
 */
class PositionController extends Controller
{

    /**
     * List all positions
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $positions = Position::with('company')->get();

        if ($positions->isEmpty()) {
            return self::sendFailure('Unable to retrieve positions at this time, please contact your system administrator',
                418);
        }

        return self::sendSuccess(PositionResource::collection($positions),
            'Retrieved positions', 200);
    }

    /**
     * Add a position for a company to the database
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|integer|exists:companies,id',
            'user_id' => 'required|integer|exists:users,id',
            'start' => 'required|date',
            'end' => 'required|date|after:start',
            'title' => 'required|string|between:2,255',
            'description' => 'required|string|min:2',
            'min_salary' => 'required|integer',
            'max_salary' => 'required|integer|gte:min_salary',
            'currency' => ['required', 'string', Rule::in(currencyCodes)],
            'benefits' => 'required|string|min:2',
            'requirements' => 'required|string|min:2',
            'type' => [
                'required', 'string', Rule::in([
                    'Casual', 'Part-Time', 'Full-Time', 'Contract'
                ])
            ],
        ]);

        if ($validator->fails()) {
            $messages = $validator->errors()->messages();
            return self::sendFailure($messages, 422);
        }

        $validated = $validator->safe()->all();
        $company = Company::with('positions')->find($validated['company_id']);
        $position = $company->positions()->create($validated);

        return self::sendSuccess(new PositionResource($position),
            "Position for Company: $company->name created", 201);
    }

    /**
     * Retrieve a single position and its related company.
     *
     * @param  string  $id
     * @return JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        $position = Position::with('company')->find($id);
        if ($position === null) {
            return self::sendFailure('Specified position not found', 404);
        }

        return self::sendSuccess(new PositionResource($position),
            "Retrieved position with id: $position->id");

    }

    /**
     * Update the specified position in the database
     *
     * @param  Request  $request
     * @param  string  $id
     * @return JsonResponse
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $position = Position::find($id);

        if ($position === null) {
            return self::sendFailure("Position with id: $id not found", 404);
        }

        $validator = Validator::make($request->all(), [
            'company_id' => 'prohibited',
            'user_id' => 'prohibited',
            'start' => 'prohibited',
            'end' => 'sometimes|date|after:now',
            'title' => 'sometimes|required|string|between:2,255',
            'description' => 'sometimes|string|min:2',
            'min_salary' => 'sometimes|integer',
            'max_salary' => 'sometimes|integer|gte:min_salary',
            'currency' => ['sometimes', 'string', Rule::in(currencyCodes)],
            'benefits' => 'sometimes|string|min:2',
            'requirements' => 'sometimes|string|min:2',
            'type' => [
                'sometimes', 'string', Rule::in([
                    'Casual', 'Part-Time', 'Full-Time', 'Contract'
                ])
            ],
        ]);

        if ($validator->fails()) {
            $messages = $validator->errors()->messages();
            return self::sendFailure($messages, 422);
        }

        $validated = $validator->safe()->all();

        $position->update($validated);

         return self::sendSuccess(new PositionResource($position),
            "Position with id: $position->id updated", 201);
    }

    /**
     * Soft delete the specified position from the database
     *
     * @urlParam id integer required The ID of the position.
     *
     * @param  string  $id
     * @return JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        $position = Position::find($id);
        if ($position === null) {
            return self::sendFailure("Specified position not found", 404);
        }
        $position->delete();

        return self::sendSuccess(new PositionResource($position), "Position with id: $position->id deleted", 200);
    }


    /**
     * Restore the specified soft deleted company from trash.
     *
     * @urlParam id integer required The ID of the position.
     *
     * @param  string  $id
     * @return JsonResponse
     */
    public function restore(string $id):JsonResponse
    {
        Position::withTrashed()
            ->where('id', $id)
            ->restore();

        return self::sendSuccess($id, "Position with id: $id restored", 200);
    }
}
