<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PositionResource extends JsonResource
{
    /**
     * Transform the position resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $company = CompanyResource::make($this->company);

        return [
            'company' => $company,
            'start' => $this->start,
            'end' => $this->end,
            'title' => $this->title,
            'description' => $this->description,
            'min_salary' => $this->min_salary,
            'max_salary' => $this->max_salary,
            'currency' => $this->currency,
            'benefits' => $this->benefits,
            'requirements' => $this->requirements,
            'type' => $this->type,
        ];
    }
}
