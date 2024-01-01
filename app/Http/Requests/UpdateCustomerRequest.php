<?php

namespace App\Http\Requests;

use App\Models\Customer;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('customer_edit');
    }

    public function rules()
    {
        return [
            'fullname' => [
                'string',
                'required',
            ],
            'account_number' => [
                'string',
                'required',
                'unique:customers,account_number,' . request()->route('customer')->id,
            ],
            'telephone_number' => [
                'string',
                'nullable',
            ],
        ];
    }
}
