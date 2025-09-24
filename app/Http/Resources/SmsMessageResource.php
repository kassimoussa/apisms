<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SmsMessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'direction' => $this->direction,
            'from' => $this->formatted_from,
            'to' => $this->formatted_to,
            'message' => $this->content,
            'status' => $this->status,
            'error_code' => $this->when($this->error_code, $this->error_code),
            'error_message' => $this->when($this->error_message, $this->error_message),
            'sent_at' => $this->sent_at?->toISOString(),
            'delivered_at' => $this->delivered_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            'metadata' => $this->when($this->metadata, $this->metadata),
        ];
    }
}
