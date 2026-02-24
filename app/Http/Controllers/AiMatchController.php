<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AiMatchController extends Controller
{
    public function analyze(Request $request)
    {
        $request->validate([
            'user_id_1' => 'required|exists:users,id',
            'user_id_2' => 'required|exists:users,id|different:user_id_1',
        ]);

        $cacheKey = "ai_match_{$request->user_id_1}_{$request->user_id_2}";

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($request) {
            try {
                $user1 = User::with('answers')->findOrFail($request->user_id_1);
                $user2 = User::with('answers')->findOrFail($request->user_id_2);

                $prompt = $this->buildPrompt($user1, $user2);

                $response = OpenAI::chat()->create([
                    'model' => config('openai.model', 'gpt-4o-mini'),
                    'temperature' => 0.7,
                    'response_format' => [
                        'type' => 'json_schema',
                        'json_schema' => $this->getJsonSchema(),
                    ],
                    'messages' => [
                        ['role' => 'system', 'content' => $this->systemPrompt()],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                ]);

                $content = $response->choices[0]->message->content ?? '';

                // Debug logging
                Log::info('AI Raw Response', ['content' => $content]);

                $data = json_decode($content, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception('Invalid JSON from AI: ' . json_last_error_msg());
                }

                return $data;

            } catch (\Exception $e) {
                Log::error('AI Analysis Failed', [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'user1' => $request->user_id_1,
                    'user2' => $request->user_id_2,
                ]);

                return [
                    'error' => 'server_error',
                    'message' => 'Failed to analyze compatibility. Please try again later.'
                ];
            }
        });
    }

    private function systemPrompt(): string
    {
        
        return <<<'PROMPT'
You are an expert matchmaking psychologist with 20+ years of experience in long-term relationship compatibility.

Your ONLY job is to analyze two people's questionnaire answers and give a brutally honest, balanced professional opinion on their compatibility.

Rules you MUST strictly follow:
- Never hallucinate or make up information that is not in the provided answers
- If the answers are too short/empty/incomplete for a category, say so clearly
- Do NOT give fake high scores just to be positive – be realistic
- Output MUST be valid JSON only – no extra text before or after

Input format:
User A: [Name] (Age: [age])
Answers:
[question]: [answer]
[question]: [answer]
...

User B: [Name] (Age: [age])
Answers:
[question]: [answer]
...

Now analyze:

First, check if analysis is even possible:
- If either user has fewer than 8 meaningful answers (non-empty, non-"prefer not to say" type), output this JSON and STOP:
{
  "error": "insufficient_data",
  "message": "One or both profiles have too few answers to provide meaningful compatibility analysis. Need at least 8-10 substantive responses."
}

Only if both have enough data, then provide full analysis in this exact JSON structure:

{
  "overall_compatibility": integer 0-100,
  "personality_summary_a": "short 2-3 sentence summary of User A's core personality traits based ONLY on answers",
  "personality_summary_b": "short 2-3 sentence summary of User B's core personality traits based ONLY on answers",
  "key_strengths": array of 4-7 short bullet-point strings (what could work well between them),
  "potential_red_flags": array of 0-6 short bullet-point strings (realistic concerns / incompatibilities – can be empty),
  "long_term_compatibility": one paragraph (4-7 sentences) explaining long-term potential,
  "recommendation_for_admin": one paragraph (3-6 sentences) telling the admin whether to introduce them or not, and why,
  "suggested_icebreaker": one natural, friendly opening message suggestion that User A could send to User B
}
PROMPT;
    }

    private function buildPrompt($user1, $user2): string
    {
        $answers1 = $user1->answers->map(fn($a) => "{$a->question_text}: {$a->answer}")->join("\n");
        $answers2 = $user2->answers->map(fn($a) => "{$a->question_text}: {$a->answer}")->join("\n");

        return "Compare these two profiles for long-term relationship compatibility:\n\n" .
               "User A: {$user1->name} (Age: {$user1->age})\n" .
               "Answers:\n{$answers1}\n\n" .
               "User B: {$user2->name} (Age: {$user2->age})\n" .
               "Answers:\n{$answers2}\n\n" .
               "Give deep personality analysis.";
    }

    private function getJsonSchema(): array
    {
        return [
            'name' => 'match_analysis',
            'strict' => true,
            'schema' => [
                'type' => 'object',
                'properties' => [
                    'overall_compatibility' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 100],
                    'personality_summary_a' => ['type' => 'string'],
                    'personality_summary_b' => ['type' => 'string'],
                    'key_strengths' => ['type' => 'array', 'items' => ['type' => 'string']],
                    'potential_red_flags' => ['type' => 'array', 'items' => ['type' => 'string']],
                    'long_term_compatibility' => ['type' => 'string'],
                    'recommendation_for_admin' => ['type' => 'string'],
                    'suggested_icebreaker' => ['type' => 'string'],
                ],
                'required' => [
                    'overall_compatibility',
                    'personality_summary_a',
                    'personality_summary_b',
                    'key_strengths',
                    'potential_red_flags',
                    'long_term_compatibility',
                    'recommendation_for_admin',
                    'suggested_icebreaker'
                ],
                'additionalProperties' => false,
            ],
        ];
    }
}