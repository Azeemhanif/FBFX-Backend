<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PremiumMemberResource extends JsonResource
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
            'email' => $this->email == null ? "" : $this->email,
            'membership_type' => $this->membership_type == null ? "" : $this->membership_type,
            'type' => $this->type == null ? "" : $this->type,
            'status' => $this->status == null ? "" : $this->status,

        ];

        return $data;
    }
}
