<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\ManualMatch;
use App\Models\User; // Assuming you have a User model
use Illuminate\Http\Request;

class ManualMatchController extends Controller
{
    // Admin creates manual match
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id_1' => 'required|exists:users,id',
            'user_id_2' => 'required|exists:users,id|different:user_id_1',
            'compatibility' => 'nullable|integer|min:0|max:100',
            'note' => 'nullable|string',
        ]);

        $data['added_by'] = $request->user()->id; // admin id
        $data['compatibility'] = $data['compatibility'] ?? 100;

        $match = ManualMatch::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Manual match created successfully',
            'data' => $match,
        ], 201);
    }

    // Get matches for a specific user (updated to return full UserResource data)
    public function getMatches($id)
    {
        // Validate user exists
        $user = User::findOrFail($id);

        $matches = ManualMatch::where('user_id_1', $id)
            ->orWhere('user_id_2', $id)
            ->get();

        $matchData = $matches->map(function ($match) use ($id) {
            $otherId = ($match->user_id_1 == $id) ? $match->user_id_2 : $match->user_id_1;
            $otherUser = User::with(['images', 'answers'])->findOrFail($otherId);
            $resource = new UserResource($otherUser);
            return array_merge($resource->toArray(request()), [
                'compatibility' => $match->compatibility,
            ]);
        });

        return response()->json([
            'success' => true,
            'data' => $matchData,
        ]);
    }
}