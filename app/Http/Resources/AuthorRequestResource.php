<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthorRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'reason'     => $this->reason,
            'status'     => $this->status,
            'created_at' => $this->created_at,

            // 👈 استخدم whenLoaded لمنع N+1
            'applicant' => $this->whenLoaded('user', fn() => [
                'id'    => $this->user->id,
                'name'  => $this->user->name,
                'email' => $this->user->email,
                'role'  => $this->user->role,
            ]),

            'author_profile' => $this->whenLoaded('author', fn() => $this->author ? [
                'bio'         => $this->author->bio,
                'phone'       => $this->author->phone,
                'nationality' => $this->author->nationality,
                'status'      => $this->author->status,
            ] : null),
        ];
    }
}
