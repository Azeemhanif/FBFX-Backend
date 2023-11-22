<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
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
            'content' => $this->content == null ? "" : $this->content,
            'type' => $this->type == null ? "" : $this->type,
            'status' => $this->status == null ? "" : $this->status,
        ];

        return $data;
    }
}
