<?php

namespace App\Http\Requests;

use App\Models\Question;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check(); // Only authenticated users
    }

    public function rules(): array
    {
        $questions = Question::all();
        $rules = [];

        foreach ($questions as $question) {
            $field = $question->key;
            $required_str = $question->required ? 'required' : 'nullable';

            switch ($question->type) {
                case 'text':
                    if ($field === 'dateOfBirth') {
                        $rules[$field] = $required_str . '|date|date_format:Y-m-d';
                    } else {
                        $rules[$field] = $required_str . '|string|max:255';
                    }
                    break;
                case 'select':
                    $rules[$field] = $required_str . '|string';
                    if ($question->options) {
                        $options = implode(',', array_map(function ($o) {
                            return '"' . addslashes($o) . '"';
                        }, $question->options));
                        $rules[$field] .= '|in:' . $options;
                    }
                    break;
                case 'multiselect':
                    $rules[$field] = $required_str . '|array';
                    if ($question->options) {
                        $options = implode(',', array_map(function ($o) {
                            return '"' . addslashes($o) . '"';
                        }, $question->options));
                        $rules[$field . '.*'] = 'in:' . $options;
                    }
                    break;
                case 'scale':
                    $rules[$field] = $required_str . '|integer|min:1|max:10';
                    break;
                case 'boolean':
                    $rules[$field] = $required_str . '|boolean';
                    break;
            }
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            '*.required' => 'This field is required.',
            '*.string' => 'This field must be a string.',
            '*.integer' => 'This field must be an integer.',
            '*.boolean' => 'This field must be true or false.',
            '*.max' => 'This field may not be greater than :max characters.',
            '*.min' => 'This field must be at least :min.',
            '*.in' => 'Invalid value selected.',
            '*.array' => 'This field must be an array.',
            'dateOfBirth.date' => 'Please enter a valid date (YYYY-MM-DD).',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation errors',
            'errors' => $validator->errors(),
        ], 422));
    }
}