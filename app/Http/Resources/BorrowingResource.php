<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BorrowingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        //return parent::toArray($request);
       return [
            'id'            => $this->id,
            'borrowed_date' => $this->borrowed_date,
            'due_date'      => $this->due_date,
            'returned_date' => $this->returned_date,
            'status'        => $this->status,
            'renewal_count' => $this->renewal_count,
            'is_overdue'    => $this->isOverdue(),


            'book' => $this->whenLoaded('book', fn() => [
                'id'    => $this->book->id,
                'title' => $this->book->title,
                'isbn'  => $this->book->isbn,
            ]),


            'member' => $this->whenLoaded('member', fn() => [
                'id'   => $this->member->id,
                'name' => $this->member->user?->name,
            ]),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
