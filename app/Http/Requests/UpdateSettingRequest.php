<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSettingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'environment' => 'required|string',
            'app_name' => [
                'required', 'string', 'max:50',
                Rule::unique('settings', 'app_name')->ignore($this->route('setting')->id)
            ],
            'short_name' => 'required|string|max:20',
            'app_url' => 'nullable|url',
            'app_version' => 'nullable|string',
            'email' => 'nullable|email|max:100',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:100',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,svg,gif|max:2048',
            'favicon' => 'nullable|image|mimes:jpg,jpeg,png,svg,gif|max:1024',
            'description' => 'nullable|string',
            'copy_right_statement' => 'required|string',
            'status' => 'required|string',
            'user_id' => 'required|integer',
        ];
    }
}
