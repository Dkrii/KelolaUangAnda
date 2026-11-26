<?php

namespace App\Http\Requests;

use App\Enums\CategoryType;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConfirmReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'wallet_id' => [
                'required',
                Rule::exists('wallets', 'id')->where(function (Builder $query) {
                    $query->where('user_id', $this->user()->id)
                        ->where('is_active', true)
                        ->whereNull('deleted_at');
                }),
            ],
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where(function (Builder $query) {
                    $query->where('user_id', $this->user()->id)
                        ->where('type', CategoryType::Expense->value)
                        ->whereNull('deleted_at');
                }),
            ],
            'amount' => ['required', 'numeric', 'gt:0', 'max:9999999999999.99', 'decimal:0,2'],
            'transaction_date' => ['required', 'date', 'before_or_equal:today'],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }
}
