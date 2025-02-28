<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecurringTransferResource extends JsonResource
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
            'user' => $this->whenLoaded('user'),
            'recipient_email' => $this->recipient_email,
            'amount' => $this->amount,
            'reason' => $this->reason,
            'start_date' => $this->start_date->format('Y-m-d'),
            'end_date' => $this->end_date->format('Y-m-d'),
            'frequency_days' => $this->frequency_days,
            'is_active' => $this->is_active,
            'next_execution_date' => $this->next_execution_date->format('Y-m-d'),
            'created_at' => $this->created_at->format('Y-m-d'),
        ];
    }
}
