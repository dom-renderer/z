<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductionCategoryRequest extends FormRequest
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
        if (request()->method() == 'PUT') {

            $id = decrypt($this->id);
            return ['name' => ['required', function ($name, $value, $fail) use ($id) {
                if (\App\Models\ProductionCategory::where('slug', \App\Helpers\Helper::slug($value))->where('id', '!=', $id)->withTrashed()->exists()) {
                    $fail("This category is already exists.");
                }
            }]];
        } else {
            return ['name' => ['required', function ($name, $value, $fail) {
                if (\App\Models\ProductionCategory::where('slug', \App\Helpers\Helper::slug($value))->withTrashed()->exists()) {
                    $fail("This category is already exists.");
                }
            }]];
        }
    }

    public function messages(): array
    {
        return ['name.required' => 'Name is required.'];
    }
}
