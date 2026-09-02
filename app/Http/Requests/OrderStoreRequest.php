<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;

class OrderStoreRequest extends FormRequest
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

    public function rules(): array
    {
        return [
            'payment_type'                  => 'required|string|in:stripe,cash',
            'shipping_type'                 => 'required|string|in:home',
            'products'                      => 'required|array',
            'products.*.product_id'         => 'required|integer|exists:products,id',
            'products.*.quantity'           => 'required|integer|min:1',
            'customer_address'              => 'required|array',
            'customer_address.name'         => 'required|string|max:255',
            'customer_address.address'      => 'required|string|max:255',
            'customer_address.email'        => 'nullable|string|email|max:255',
            'customer_address.phone'        => 'required|string|max:255',
            'customer_address.comment'      => 'nullable|string|max:255',
            'shipping_address'              => 'required|array',
            'shipping_address.name'         => 'required|string|max:255',
            'shipping_address.address'      => 'required|string|max:255',
            'shipping_address.email'        => 'nullable|string|email|max:255',
            'shipping_address.phone'        => 'required|string|max:255',
            'shipping_address.comment'      => 'nullable|string|max:255',
            'shipping_charge_id'            => 'required|integer|exists:shipping_charges,id',
            'coupon_code'                   => 'nullable|string|exists:coupons,code',
        ];
    }


    /**
     * Get the error messages for the defined validation rules.*
     * @return array
     */
    public function failedValidation(Validator $validator)
    {
        if ($this->expectsJson()) {
            throw new HttpResponseException(response()->json([
                'message' => $validator->errors()->all(),
                'status' => false
            ], 422));
        } else {
            throw (new ValidationException($validator))
                ->errorBag($this->errorBag)
                ->redirectTo($this->getRedirectUrl());
        }
    }
     
    public function messages()
    {
        return [
            'shipping_address.first_name.required' => 'First name is required',
            'shipping_address.last_name.required' => 'Last name is required',
            'shipping_address.address.required' => 'Address is required',
            'shipping_address.email.required' => 'Email is required',
            'shipping_address.city.required' => 'City is required',
            'shipping_address.zone.required' => 'Zone is required',
        ];
    }
}

