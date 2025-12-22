<?php

namespace App\Http\Requests;

use App\Services\LoggingService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreSaleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && (
            auth()->user()->isAdmin() ||
            auth()->user()->isPharmacist() ||
            auth()->user()->isSalesStaff()
        );
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'customer_id' => [
                'nullable',
                'integer',
                'exists:customers,id'
            ],
            'customer_name' => [
                'required_without:customer_id',
                'string',
                'max:255'
            ],
            'customer_phone' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[\+]?[0-9\-\(\)\s]+$/'
            ],
            'customer_email' => [
                'nullable',
                'email',
                'max:255'
            ],
            'payment_method' => [
                'required',
                'string',
                'in:cash,card,insurance,mixed'
            ],
            'items' => [
                'required',
                'array',
                'min:1'
            ],
            'items.*.product_id' => [
                'required',
                'integer',
                'exists:products,id'
            ],
            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
                'max:1000'
            ],
            'items.*.unit_price' => [
                'required',
                'numeric',
                'min:0.01',
                'max:9999.99',
                'regex:/^\d+(\.\d{1,2})?$/'
            ],
            'items.*.batch_id' => [
                'nullable',
                'integer',
                'exists:batches,id'
            ],
            'discount_amount' => [
                'nullable',
                'numeric',
                'min:0',
                'max:9999.99',
                'regex:/^\d+(\.\d{1,2})?$/'
            ],
            'tax_amount' => [
                'nullable',
                'numeric',
                'min:0',
                'max:9999.99',
                'regex:/^\d+(\.\d{1,2})?$/'
            ],
            'paid_amount' => [
                'required',
                'numeric',
                'min:0.01',
                'max:99999.99',
                'regex:/^\d+(\.\d{1,2})?$/'
            ],
            'notes' => [
                'nullable',
                'string',
                'max:500'
            ]
        ];
    }

    /**
     * Get custom validation messages
     */
    public function messages(): array
    {
        return [
            'customer_name.required_without' => 'Customer name is required when no existing customer is selected.',
            'customer_phone.regex' => 'Please enter a valid phone number.',
            'payment_method.required' => 'Payment method is required.',
            'payment_method.in' => 'Please select a valid payment method.',
            'items.required' => 'At least one item is required.',
            'items.min' => 'At least one item is required.',
            'items.*.product_id.required' => 'Product is required for each item.',
            'items.*.product_id.exists' => 'Selected product does not exist.',
            'items.*.quantity.required' => 'Quantity is required for each item.',
            'items.*.quantity.min' => 'Quantity must be at least 1.',
            'items.*.quantity.max' => 'Quantity cannot exceed 1000.',
            'items.*.unit_price.required' => 'Unit price is required for each item.',
            'items.*.unit_price.min' => 'Unit price must be at least $0.01.',
            'items.*.unit_price.regex' => 'Unit price must be a valid monetary amount.',
            'items.*.batch_id.exists' => 'Selected batch does not exist.',
            'discount_amount.regex' => 'Discount amount must be a valid monetary amount.',
            'tax_amount.regex' => 'Tax amount must be a valid monetary amount.',
            'paid_amount.required' => 'Paid amount is required.',
            'paid_amount.min' => 'Paid amount must be at least $0.01.',
            'paid_amount.regex' => 'Paid amount must be a valid monetary amount.',
        ];
    }

    /**
     * Prepare the data for validation
     */
    protected function prepareForValidation()
    {
        // Calculate total price from items
        $totalPrice = 0;
        if ($this->has('items') && is_array($this->items)) {
            foreach ($this->items as $item) {
                if (isset($item['quantity']) && isset($item['unit_price'])) {
                    $totalPrice += $item['quantity'] * $item['unit_price'];
                }
            }
        }

        $this->merge([
            'total_price' => $totalPrice,
            'sale_date' => now(),
            'status' => 'completed'
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
                    'message' => 'Sale validation failed',
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
        LoggingService::logSecurityEvent('unauthorized_sale_creation_attempt', [
            'user_id' => auth()->id(),
            'user_role' => auth()->user()?->role?->name,
            'request_data' => $this->except(['password', 'password_confirmation', '_token'])
        ]);

        parent::failedAuthorization();
    }
}
