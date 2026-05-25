<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthorResource extends JsonResource
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
             'id' => (int) $this->id,
            'name'=> strtoupper( $this->user->name),// use $this instead $request to ake data as collection to display and strtoupper for make name capital
            'bio'=>$this->bio,
            'nationality'=>strtolower($this->nationality),// make small letter
            'phone'=>$this->phone,
             'status' => $this->status,
            'books'=>$this->when($this->relationLoaded('books'),$this->books->count())

        ];

    }
}