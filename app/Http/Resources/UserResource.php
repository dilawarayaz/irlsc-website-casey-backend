<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\User;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            // Basic info
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
            'role' => $this->role,
            'profile_type' => $this->profile_type,
            // Images
            'images' => $this->images->map(function ($image) {
                return [
                    'id' => $image->id,
                    'url' => $this->makeImageUrl($image->image_path),
                ];
            }),

            // Videos
            'videos' => $this->videos->map(function ($video) {
                return [
                    'id' => $video->id,
                    'url' => $this->makeImageUrl($video->video_path), // Reusing makeImageUrl for videos
                    'original_name' => $video->original_name,
                    'mime_type' => $video->mime_type,
                    'size' => $video->size,
                ];
            }),

            // Question Answers
            'answers' => $this->answers->map(function ($answer) {
                return [
                    'category'      => $answer->question?->category,
                    'question_id'   => $answer->question_id,
                    'question_key'  => $answer->question?->key,
                    'question_text' => $answer->question?->text,
                    'answer'        => $this->decodeAnswer($answer->answer),
                ];
            }),

            // Matches count
            'matches_count' => $this->calculateMatchesCount(),

            // Meta
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    private function decodeAnswer($answer)
    {
        $decoded = json_decode($answer, true);
        return $decoded === null ? $answer : $decoded;
    }

    private function makeImageUrl($path)
    {
        if (preg_match('/^https?:\/\//', $path)) {
            return $path;
        }
        return asset('storage/' . $path);
    }

    private function calculateMatchesCount()
    {
        $userInterests = json_decode($this->interests ?? '[]', true);
        if (empty($userInterests)) {
            return 0;
        }

        return User::where('id', '!=', $this->id)
            ->get()
            ->filter(function ($other) use ($userInterests) {
                $otherInterests = json_decode($other->interests ?? '[]', true);
                return count(array_intersect($userInterests, $otherInterests)) > 0;
            })
            ->count();
    }
}