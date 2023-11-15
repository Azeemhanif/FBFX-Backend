<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AcademyResource extends JsonResource
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
            'title' => $this->title == null ? "" : $this->title,
            'description' => $this->description == null ? "" : $this->description,
            'url' => $this->url == null ? "" : $this->url,
            'youtube' => $this->youtube == null ? "" : $this->youtube,
            'image' => $this->image == null ? "" : $this->image,
        ];

        return $data;
    }
}
