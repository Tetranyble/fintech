<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;


/**
 * @OA\Schema(schema="RegistrationRequest")
 * {
 *
 *   @OA\Property(
 *     property="firstname",
 *     type="string",
 *     description="The user firstname"
 *   ),
 *   @OA\Property(
 *     property="lastname",
 *     type="string",
 *     description="The user lastname"
 *   ),
 *   @OA\Property(
 *     property="middlename",
 *     type="string",
 *     description="The user middlename"
 *   ),
 *   @OA\Property(
 *     property="phone",
 *     type="string",
 *     description="The user phone number"
 *   ),
 *   @OA\Property(
 *     property="email",
 *     type="string",
 *     description="The user email"
 *   ),
 *   @OA\Property(
 *     property="password",
 *     type="string",
 *     description="The user password. combination of letters, numbers, and spacial character."
 *   ),
 *   @OA\Property(
 *     property="password_confirmation",
 *     type="string",
 *     description="The user password confirmation"
 *   ),
 *   @OA\Property(
 *     property="referral",
 *     type="string",
 *     description="The user referral"
 *   )
 * }
 */
class RegistrationRequest extends FormRequest
{
    public function passedValidation()
    {
        if (isset($this->firstname) && isset($this->lastname)) {
            $this->merge(['username' => $this->firstname.'.'.$this->lastname]);
        }
        $this->merge(['username' => Str::uuid()->toString().'-'.time()]);
    }

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
            'firstname' => ['nullable', 'string', 'max:255'],
            'lastname' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            //'referrer' => ['nullable', 'string', 'exists:users,username'],
            'role' => 'nullable|string',
        ];
    }
}
