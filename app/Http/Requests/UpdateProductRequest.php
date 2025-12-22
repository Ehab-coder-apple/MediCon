<?php

namespace App\Http\Requests;

use App\Services\LoggingService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && (
            auth()->user()->isAdmin() ||
            auth()->user()->isPharmacist()
        );
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $productId = $this->route('product')?->id ?? $this->route('id');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'name')->ignore($productId)
            ],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('products', 'code')->ignore($productId),
                'regex:/^[A-Z0-9]+$/'
            ],
            'category' => [
                'required',
                'string',
                'max:100',
                'in:Pain Relief,Antibiotics,Vitamins,Cold & Flu,Digestive,Allergy,Topical,Prescription,OTC'
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000'
            ],
            'cost_price' => [
                'required',
                'numeric',
                'min:0.01',
                'max:9999.99',
                'regex:/^\d+(\.\d{1,2})?$/'
            ],
            'selling_price' => [
                'required',
                'numeric',
                'min:0.01',
                'max:9999.99',
                'regex:/^\d+(\.\d{1,2})?$/',
                'gte:cost_price'
            ],
            'alert_quantity' => [
                'required',
                'integer',
                'min:1',
                'max:10000'
            ],
        ];
    }

    /**
     * Get custom validation messages
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Product name is required.',
            'name.unique' => 'A product with this name already exists.',
            'code.required' => 'Product code is required.',
            'code.unique' => 'A product with this code already exists.',
            'code.regex' => 'Product code must contain only uppercase letters and numbers.',
            'category.required' => 'Product category is required.',
            'category.in' => 'Please select a valid product category.',
            'cost_price.required' => 'Cost price is required.',
            'cost_price.min' => 'Cost price must be at least $0.01.',
            'cost_price.regex' => 'Cost price must be a valid monetary amount.',
            'selling_price.required' => 'Selling price is required.',
            'selling_price.gte' => 'Selling price must be greater than or equal to cost price.',
            'selling_price.regex' => 'Selling price must be a valid monetary amount.',
            'alert_quantity.required' => 'Alert quantity is required.',
            'alert_quantity.min' => 'Alert quantity must be at least 1.',
        ];
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
                    'message' => 'Validation failed',
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
        LoggingService::logSecurityEvent('unauthorized_product_update_attempt', [
            'user_id' => auth()->id(),
            'user_role' => auth()->user()?->role?->name,
            'product_id' => $this->route('product')?->id,
            'request_data' => $this->except(['password', 'password_confirmation', '_token'])
        ]);

        parent::failedAuthorization();
    }
}
