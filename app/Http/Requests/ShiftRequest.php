<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = null;
        $routeParam = $this->route('shift');
        if ($routeParam) {
            $id = decrypt($routeParam);
        }

        return [
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('shifts', 'title')->whereNull('deleted_at')->ignore($id),
            ],
            'start' => ['required', 'date_format:h:i'],
            'end' => ['required', 'date_format:h:i'],
        ];
    }
}
