<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductionUomRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // decrypt ID if updating
        $id = !empty($this->id) ? decrypt($this->id) : '';

        return [
            'code' => 'required|string|max:191|unique:production_uoms,code,' . $id,
            'name' => 'required|string|max:191',
            'status' => 'required|in:active,inactive',
        ];
    }
}
