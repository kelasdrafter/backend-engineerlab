<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
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
            'user' => $this->user,
            'course' => $this->course,
            'voucher_code' => $this->voucher_code,
            'status' => $this->status,
            'meta' => $this->meta,
            'amount' => $this->amount,
        ];
    }
}
