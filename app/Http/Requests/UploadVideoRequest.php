<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadVideoRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user() !== null;
    }

    public function rules()
    {
        return [
            'video' => 'required|file|mimetypes:video/mp4,video/quicktime,video/x-msvideo,video/x-matroska,video/webm|max:51200', // max 50MB
        ];
    }

    public function messages()
    {
        return [
            'video.max' => 'Video size must be less than 50MB.',
        ];
    }
}
