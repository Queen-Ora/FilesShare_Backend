<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterRequest extends FormRequest
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
         
            'name' => 'required|string|max:255|min:5',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required|string|same:password',
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom est requis',
            'email.required' => 'le mail est requis',
            'password.required' => 'le mot de passe est requis',
            'password_confirmation.required' => 'la confirmation du mot de passe est requise',
            'avatar.required' => 'la photo de profil du membre est requise',
            'avatar.image' => 'la photo de profil du membre doit être une image',
            'avatar.mimes' => 'la photo de profil du membre doit être une image de type: jpeg, png, jpg, gif, svg',
            'avatar.max' => 'la photo de profil du membre ne doit pas dépasser 2Mo',
            'name.min' => 'le nom doit avoir au moins 5 caractères',
            'name.max' => 'Name must not exceed 255 characters',
            'email.email' => 'le mail doit être valide',
            'email.unique' => 'Cet email existe déja',
            'password.min' => 'le mot de passe doit avoir au moins 6 caractères',
            'password.confirmed' => 'le mot de passe et sa confirmation ne sont pas identiques', 
            'password_confirmation.same' => 'la confirmation du mot de passe et sa confirmation ne sont pas identiques',
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
