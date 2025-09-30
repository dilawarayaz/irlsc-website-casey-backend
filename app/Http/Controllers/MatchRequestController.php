<?php

namespace App\Http\Controllers;

use App\Models\MatchRequest;
use App\Models\ManualMatch;
use Illuminate\Http\Request;

class MatchRequestController extends Controller
{
    // User (premium) sends request
    public function store(Request $request)
    {
        $user = $request->user();

        // Check if user is premium (assume 'is_premium' column in users table)
        if (!$user->is_premium) {
            return response()->json([
                'success' => false,
                'message' => 'Only premium users can send match requests.',
            ], 403);
        }

        $data = $request->validate([
            'relationship_type' => 'required|string',
            'min_age' => 'nullable|integer|min:18',
            'max_age' => 'nullable|integer|min:18',
        ]);

        $data['user_id'] = $user->id;

        $matchRequest = MatchRequest::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Match request submitted successfully.',
            'data' => $matchRequest,
        ], 201);
    }

    // Admin handles request (approve and create manual match)
    public function handle(Request $request, $id)
    {
        $matchRequest = MatchRequest::findOrFail($id);

        $data = $request->validate([
            'user_id_2' => 'required|exists:users,id',
            'compatibility' => 'nullable|integer|min:0|max:100',
            'status' => 'required|in:approved,rejected',
        ]);

        $matchRequest->status = $data['status'];
        $matchRequest->handled_by = $request->user()->id; // admin id
        $matchRequest->save();

        if ($data['status'] === 'approved') {
            // Create manual match record
            $match = ManualMatch::create([
                'user_id_1' => $matchRequest->user_id,
                'user_id_2' => $data['user_id_2'],
                'compatibility' => $data['compatibility'] ?? 100,
                'added_by' => $request->user()->id,
                'note' => "Created via match request #{$matchRequest->id}",
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Match request handled successfully.',
            'data' => $matchRequest,
        ]);
    }
}
