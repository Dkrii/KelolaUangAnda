<?php

namespace App\Http\Requests;

use App\Enums\TransactionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTransactionRequest extends FormRequest
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
        $type = $this->input('type');

        return [
            'type' => ['required', Rule::enum(TransactionType::class)],
            'wallet_id' => [
                'required',
                'integer',
                Rule::exists('wallets', 'id')->where('user_id', $userId)->whereNull('deleted_at'),
            ],
            'destination_wallet_id' => [
                'nullable',
                'required_if:type,transfer',
                'integer',
                'different:wallet_id',
                Rule::exists('wallets', 'id')->where('user_id', $userId)->whereNull('deleted_at'),
            ],
            'category_id' => [
                'nullable',
                'required_unless:type,transfer',
                'prohibited_if:type,transfer',
                'integer',
                Rule::exists('categories', 'id')
                    ->where('user_id', $userId)
                    ->where('type', $type)
                    ->whereNull('deleted_at'),
            ],
            'amount' => ['required', 'numeric', 'gt:0', 'max:9999999999999.99', 'decimal:0,2'],
            'transaction_date' => ['required', 'date', 'before_or_equal:today'],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }
}
