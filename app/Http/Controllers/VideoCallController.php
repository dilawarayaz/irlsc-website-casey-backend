<?php

namespace App\Http\Controllers;

use App\Events\VideoCallAnswer;
use App\Events\VideoCallEnded;
use App\Events\VideoCallIceCandidate;
use App\Events\VideoCallIncoming;
use App\Events\VideoCallOffer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class VideoCallController extends Controller
{
    public function initiate(Request $request)
    {
        $request->validate([
            'to_user_id' => 'required|exists:users,id|integer|not_in:' . auth()->id(),
        ]);

        $from = auth()->id();
        $to   = $request->to_user_id;
        $callId = (string) Str::uuid();

        broadcast(new VideoCallIncoming($from, $to, $callId))->toOthers();

        return response()->json([
            'status'  => 'calling',
            'call_id' => $callId,
            'from'    => $from,
        ]);
    }

    public function sendOffer(Request $request)
    {
        $request->validate([
            'to_user_id' => 'required|exists:users,id',
            'call_id'    => 'required|string',
            'offer'      => 'required|array',
        ]);

        $from = auth()->id();
        $to   = $request->to_user_id;

        broadcast(new VideoCallOffer($from, $to, $request->call_id, $request->offer));

        return response()->json(['status' => 'offer_sent']);
    }

    public function sendAnswer(Request $request)
    {
        $request->validate([
            'to_user_id' => 'required|exists:users,id',
            'call_id'    => 'required|string',
            'answer'     => 'required|array',
        ]);

        $from = auth()->id();
        $to   = $request->to_user_id;

        broadcast(new VideoCallAnswer($from, $to, $request->call_id, $request->answer));

        return response()->json(['status' => 'answer_sent']);
    }

    public function sendCandidate(Request $request)
    {
        $request->validate([
            'to_user_id' => 'required|exists:users,id',
            'call_id'    => 'required|string',
            'candidate'  => 'required|array',
        ]);

        $from = auth()->id();
        $to   = $request->to_user_id;

        broadcast(new VideoCallIceCandidate($from, $to, $request->call_id, $request->candidate));

        return response()->json(['status' => 'candidate_sent']);
    }

    public function endCall(Request $request)
    {
        $request->validate([
            'call_id'    => 'required|string',
            'other_user_id' => 'required|exists:users,id',
        ]);

        $from = auth()->id();
        $to   = $request->other_user_id;

        broadcast(new VideoCallEnded($from, $to, $request->call_id));

        return response()->json(['status' => 'call_ended']);
    }
}