<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TrackFeedbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'visitor_id'    => ['required', 'string', 'max:64'],
            'likeable_type' => ['required', 'string', 'max:100'],
            'likeable_id'   => ['required', 'integer'],
            'type'          => ['required', 'in:like,helpful_yes,helpful_no'],
        ];
    }
}
