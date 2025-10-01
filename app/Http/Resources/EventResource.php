<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'startDate' => $this->start_date,
            'endDate' => $this->end_date,
            'startTime' => $this->start_time,
            'endTime' => $this->end_time,
            'location' => $this->location,
            'address' => $this->address,
            'coverImage' => $this->cover_image,
            'images' => $this->images ?? [],
            'category' => $this->category,
            'price' => $this->price,
            'maxAttendees' => $this->max_attendees,
            'currentAttendees' => $this->attendees->count(),
            'organizer' => [
                'id' => $this->organizer->id,
                'name' => $this->organizer->name,
                'avatar' => $this->organizer->avatar ?? '/placeholder.svg',
                'bio' => $this->organizer->bio,
                'eventsHosted' => $this->organizer->events->count(),
                'rating' => $this->organizer->rating ?? 4.5,
            ],
            'tags' => $this->tags ?? [],
            'isLiked' => $this->likes->contains(auth()->id()),
            'isAttending' => $this->attendees->contains(auth()->id()), // Added for detail page
            'attendees' => $this->attendees->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'avatar' => $user->avatar ?? '/placeholder.svg',
                ];
            }),
        ];
    }
}