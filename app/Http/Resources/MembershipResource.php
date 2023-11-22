<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MembershipResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id == null ? "" : $this->id,
            'monthly_price' => $this->monthly_price == null ? "" : $this->monthly_price,
            'yearly_price' => $this->yearly_price == null ? "" : $this->yearly_price,
        ];

        return $data;
    }
}
