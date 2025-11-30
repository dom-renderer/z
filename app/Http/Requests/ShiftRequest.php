<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\ShiftValidationService;
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
            'start' => ['required', 'date_format:H:i'],
            'end' => ['required', 'date_format:H:i'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            if ($validator->errors()->isEmpty()) {
                $start = $this->input('start');
                $end = $this->input('end');

                $shiftId = null;
                $routeParam = $this->route('shift');
                if ($routeParam) {
                    $shiftId = decrypt($routeParam);
                }

                $validationService = new ShiftValidationService();
                $result = $validationService->validateShiftTiming($start, $end, $shiftId);

                if (!$result['valid']) {
                    $validator->errors()->add('start', $result['message']);
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'start.required' => 'The start time is required.',
            'start.date_format' => 'The start time must be in HH:MM format (24-hour).',
            'end.required' => 'The end time is required.',
            'end.date_format' => 'The end time must be in HH:MM format (24-hour).',
        ];
    }
}