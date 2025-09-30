<?php

namespace App\Http\Controllers;

use App\Http\Resources\EventResource;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $query = Event::query();

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%$search%")
                    ->orWhere('description', 'like', "%$search%")
                    ->orWhere('location', 'like', "%$search%")
                    ->orWhereJsonContains('tags', $search);
            });
        }

        if ($category = $request->query('category')) {
            if ($category !== 'all') {
                $query->where('category', $category);
            }
        }

        if ($priceFilter = $request->query('price_filter')) {
            if ($priceFilter === 'free') {
                $query->where('price', 0);
            } elseif ($priceFilter === 'under-30') {
                $query->where('price', '>', 0)->where('price', '<', 30);
            } elseif ($priceFilter === '30-60') {
                $query->where('price', '>=', 30)->where('price', '<=', 60);
            } elseif ($priceFilter === 'over-60') {
                $query->where('price', '>', 60);
            }
        }

        $events = $query->with(['organizer', 'attendees', 'likes'])->get();

        return EventResource::collection($events);
    }

    public function show(Event $event)
    {
        $event->load(['organizer', 'attendees', 'likes']);

        return new EventResource($event);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'start_time' => 'required',
            'end_time' => 'nullable',
            'location' => 'required|string',
            'address' => 'nullable|string',
            'category' => 'required|string',
            'price' => 'nullable|numeric|min:0',
            'max_attendees' => 'required|integer|min:1',
            'tags' => 'nullable|string',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $validated;
        $data['organizer_id'] = auth()->id();
        $data['price'] = $data['price'] ?? 0;

        if ($request->has('tags')) {
            $data['tags'] = array_map('trim', explode(',', $request->tags));
        }

        if ($request->hasFile('cover_image')) {
            $path = $request->file('cover_image')->store('images', 'public');
            $data['cover_image'] = Storage::url($path);
        }

        $images = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('images', 'public');
                $images[] = Storage::url($path);
            }
        }
        $data['images'] = $images;

        $event = Event::create($data);

        $event->load(['organizer', 'attendees', 'likes']);

        return new EventResource($event);
    }

    public function toggleLike(Event $event)
    {
        $user = auth()->user();

        if ($event->likes()->where('user_id', $user->id)->exists()) {
            $event->likes()->detach($user->id);
            $isLiked = false;
        } else {
            $event->likes()->attach($user->id);
            $isLiked = true;
        }

        return response()->json(['isLiked' => $isLiked]);
    }

    public function toggleAttend(Event $event)
    {
        $user = auth()->user();

        $isAttending = $event->attendees()->where('user_id', $user->id)->exists();

        if (!$isAttending && $event->attendees->count() >= $event->max_attendees) {
            return response()->json(['message' => 'Event is full'], 400);
        }

        if ($isAttending) {
            $event->attendees()->detach($user->id);
            $isAttending = false;
        } else {
            $event->attendees()->attach($user->id);
            $isAttending = true;
        }

        return response()->json([
            'isAttending' => $isAttending,
            'currentAttendees' => $event->attendees()->count(),
        ]);
    }
}