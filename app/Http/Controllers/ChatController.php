<?php

namespace App\Http\Controllers;

use App\Models\ManualMatch;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    /**
     * Get all conversations (matches with last message and unread count)
     */
    public function getConversations(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Get all matches for the user
            $matches = ManualMatch::where('user_id_1', $user->id)
                ->orWhere('user_id_2', $user->id)
                ->get();

            $conversations = [];
            foreach ($matches as $match) {
                $matchUserId = ($match->user_id_1 === $user->id) ? $match->user_id_2 : $match->user_id_1;
                $matchUser = User::find($matchUserId);

                if (!$matchUser) continue;

                // Last message
                $lastMessage = Message::whereIn('sender_id', [$user->id, $matchUserId])
                    ->whereIn('receiver_id', [$user->id, $matchUserId])
                    ->orderBy('created_at', 'desc')
                    ->first();

                // Unread count (messages sent to user, not read)
                $unreadCount = Message::where('sender_id', $matchUserId)
                    ->where('receiver_id', $user->id)
                    ->whereNull('read_at')
                    ->count();

                $conversations[] = [
                    'id' => $matchUser->id,
                    'name' => $matchUser->name,
                    'profile_picture' => $matchUser->profile_picture ?? '/placeholder.svg',
                    'is_online' => false, // Add logic if you have online status
                    'last_message' => $lastMessage ? [
                        'content' => $lastMessage->content,
                        'timestamp' => $lastMessage->created_at,
                    ] : null,
                    'unread_count' => $unreadCount,
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $conversations,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Get conversations error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch conversations.',
            ], 500);
        }
    }

    /**
     * Get messages for a specific match
     * @param int $matchId
     * @param Request $request (optional 'since' timestamp for polling)
     */
    public function getMessages(Request $request, int $matchId): JsonResponse
    {
        try {
            $user = $request->user();

            // Verify if valid match
            if (!$this->isValidMatch($user->id, $matchId)) {
                return response()->json(['success' => false, 'message' => 'Invalid match.'], 403);
            }

            $query = Message::whereIn('sender_id', [$user->id, $matchId])
                ->whereIn('receiver_id', [$user->id, $matchId])
                ->orderBy('created_at', 'asc');

            // For polling: only new messages since last timestamp
            if ($request->has('since')) {
                $query->where('created_at', '>', $request->input('since'));
            }

            $messages = $query->get()->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'sender_id' => $msg->sender_id,
                    'content' => $msg->content,
                    'attachment' => $msg->attachment_path ? Storage::url($msg->attachment_path) : null,
                    'timestamp' => $msg->created_at,
                    'read' => $msg->read_at !== null,
                ];
            });

            // Mark as read (for messages received by user)
            Message::where('receiver_id', $user->id)
                ->where('sender_id', $matchId)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            return response()->json([
                'success' => true,
                'data' => $messages,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Get messages error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch messages.',
            ], 500);
        }
    }

    /**
     * Send a message (text or file)
     * @param Request $request
     * @param int $matchId
     */
    public function sendMessage(Request $request, int $matchId): JsonResponse
    {
        try {
            $user = $request->user();

            // Verify valid match
            if (!$this->isValidMatch($user->id, $matchId)) {
                return response()->json(['success' => false, 'message' => 'Invalid match.'], 403);
            }

            // Validate input
            $validator = Validator::make($request->all(), [
                'content' => 'nullable|string|max:2000',
                'attachment' => 'nullable|file|mimes:jpg,png,gif,pdf,mp4|max:5120', // 5MB max
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
            }

            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $attachmentPath = $request->file('attachment')->store('chat_attachments', 'public');
            }

            $message = Message::create([
                'sender_id' => $user->id,
                'receiver_id' => $matchId,
                'content' => $request->input('content'),
                'attachment_path' => $attachmentPath,
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $message->id,
                    'sender_id' => $message->sender_id,
                    'content' => $message->content,
                    'attachment' => $attachmentPath ? Storage::url($attachmentPath) : null,
                    'timestamp' => $message->created_at,
                    'read' => true,
                ],
            ], 201);
        } catch (\Exception $e) {
            Log::error('Send message error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send message.',
            ], 500);
        }
    }

    /**
     * Helper: Check if valid match
     */
    private function isValidMatch(int $userId, int $matchId): bool
    {
        return ManualMatch::where(function ($query) use ($userId, $matchId) {
            $query->where('user_id_1', $userId)->where('user_id_2', $matchId);
        })->orWhere(function ($query) use ($userId, $matchId) {
            $query->where('user_id_1', $matchId)->where('user_id_2', $userId);
        })->exists();
    }
}