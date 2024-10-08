<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateGroupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' =>'required|string|max:255|min:5',
            'description' =>'nullable|string|max:255',
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'admin_id' => 'required'
        ];

    }

    public function messages(): array
    {
        return [
            'name.required' => 'le nom du groupe est requis',
            'name.string' => 'Name must be a string',
            'name.max' => 'Name must not exceed 255 characters',
            'name.min' => 'Name must be at least 5 characters',
            'description.string' => 'Description must be a string',
            'description.max' => 'Description must not exceed 255 characters',
            'avatar.required' => 'Avatar is required',
            'avatar.image' => 'La Photo de profile doit etre une profile',
            'avatar.mimes' => 'Avatar must be a file of type: jpeg, png, jpg, gif, svg',
            'avatar.max' => 'Avatar file size must not exceed 2MB',
        ];  
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success'   => false,
            'message'   => 'Erreurs de validation',
            'data'      => $validator->errors()
        ], 422));
    }

 
    
}
