<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{
    /**
     * Transform the company resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'logo_path' => $this->logo_path,
            'extension' => pathinfo($this->logo_path, PATHINFO_EXTENSION),
            'created_at' => $this->updated_at,
            'updated_at' => $this->updated_at,
        ];
    }


}
