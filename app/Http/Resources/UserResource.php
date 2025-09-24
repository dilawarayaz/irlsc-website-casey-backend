<?php

namespace App\Http\Resources;

use App\Models\UserImage;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'bio' => $this->bio,
            'age' => $this->age,
            'location' => $this->location,
            'occupation' => $this->occupation,
            'education' => $this->education,
            'interests' => json_decode($this->interests ?? '[]', true),
            'looking_for' => $this->looking_for,
            'relationship_goals' => $this->relationship_goals,
            'images' => UserImage::where('user_id', $this->id)
                ->pluck('image_path')
                ->toArray(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}