<?php

namespace App\Http\Requests;

use App\Services\LoggingService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreSupplierRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:suppliers,name'
            ],
            'contact_person' => [
                'required',
                'string',
                'max:255'
            ],
            'phone' => [
                'required',
                'string',
                'max:20',
                'regex:/^[\+]?[0-9\-\(\)\s]+$/'
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:suppliers,email'
            ],
            'address' => [
                'required',
                'string',
                'max:500'
            ],
            'is_active' => [
                'boolean'
            ],
            'notes' => [
                'nullable',
                'string',
                'max:1000'
            ]
        ];
    }

    /**
     * Get custom validation messages
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Supplier name is required.',
            'name.unique' => 'A supplier with this name already exists.',
            'contact_person.required' => 'Contact person name is required.',
            'phone.required' => 'Phone number is required.',
            'phone.regex' => 'Please enter a valid phone number.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'A supplier with this email already exists.',
            'address.required' => 'Address is required.',
        ];
    }

    /**
     * Prepare the data for validation
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'is_active' => $this->boolean('is_active', true)
        ]);
    }

    /**
     * Handle a failed validation attempt
     */
    protected function failedValidation(Validator $validator)
    {
        LoggingService::logValidationError($validator->errors()->toArray(), $this);

        if ($this->expectsJson()) {
            throw new HttpResponseException(
                response()->json([
                    'success' => false,
                    'message' => 'Supplier validation failed',
                    'errors' => $validator->errors()
                ], 422)
            );
        }

        parent::failedValidation($validator);
    }

    /**
     * Handle a failed authorization attempt
     */
    protected function failedAuthorization()
    {
        LoggingService::logSecurityEvent('unauthorized_supplier_creation_attempt', [
            'user_id' => auth()->id(),
            'user_role' => auth()->user()?->role?->name,
            'request_data' => $this->except(['password', 'password_confirmation', '_token'])
        ]);

        parent::failedAuthorization();
    }
}
