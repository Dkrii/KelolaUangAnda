<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'from_wallet_id' => [
                'required',
                'integer',
                Rule::exists('wallets', 'id')->where('user_id', $userId)->whereNull('deleted_at'),
            ],
            'to_wallet_id' => [
                'required',
                'integer',
                'different:from_wallet_id',
                Rule::exists('wallets', 'id')->where('user_id', $userId)->whereNull('deleted_at'),
            ],
            'amount' => ['required', 'numeric', 'gt:0', 'max:9999999999999.99', 'decimal:0,2'],
            'transaction_date' => ['required', 'date', 'before_or_equal:today'],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }
}
