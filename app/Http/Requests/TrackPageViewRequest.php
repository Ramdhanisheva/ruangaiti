<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TrackPageViewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'visitor_id'    => ['required', 'string', 'max:64'],
            'session_id'    => ['required', 'string', 'max:64'],
            'path'          => ['required', 'string', 'max:500'],
            'viewable_type' => ['nullable', 'string', 'max:100'],
            'viewable_id'   => ['nullable', 'integer'],
            'referrer'      => ['nullable', 'string', 'max:500'],
        ];
    }
}
