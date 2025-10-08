<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    // Public: Get all questions (for frontend)
    public function index()
{
    $questions = Question::all()->map(function ($q) {
        return [
            'id' => $q->id,          // ✅ real numeric id
            'key' => $q->key,        // ✅ send key separately
            'category' => $q->category,
            'question' => $q->question,
            'type' => $q->type,
            'required' => $q->required,
            'options' => $q->options,
        ];
    });

    return response()->json([
        'success' => true,
        'data' => $questions,
    ]);
}


    // Admin: Create new question
    public function store(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string|unique:questions,key',
            'category' => 'required|string',
            'question' => 'required|string',
            'type' => 'required|in:text,select,multiselect,scale,boolean',
            'required' => 'boolean',
            'options' => 'nullable|array',
        ]);

        $question = Question::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Question created successfully.',
            'data' => $question,
        ], 201);
    }

    // Admin: Update question
    public function update(Request $request, Question $question)
    {
        $validated = $request->validate([
            'key' => 'string|unique:questions,key,' . $question->id,
            'category' => 'string',
            'question' => 'string',
            'type' => 'in:text,select,multiselect,scale,boolean',
            'required' => 'boolean',
            'options' => 'nullable|array',
        ]);

        $question->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Question updated successfully.',
            'data' => $question,
        ]);
    }

    // Admin: Delete question
    public function destroy(Question $question)
    {
        $question->delete();

        return response()->json([
            'success' => true,
            'message' => 'Question deleted successfully.',
        ]);
    }
}