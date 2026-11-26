<?php

namespace App\Http\Requests;

use App\Enums\CategoryType;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBudgetRequest extends FormRequest
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
        return [
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where(function (Builder $query) {
                    $query->where('user_id', $this->user()->id)
                        ->where('type', CategoryType::Expense->value)
                        ->whereNull('deleted_at');
                }),
                Rule::unique('budgets', 'category_id')
                    ->where('user_id', $this->user()->id)
                    ->where('month', $this->input('month').'-01'),
            ],
            'amount' => ['required', 'numeric', 'gt:0', 'max:9999999999999.99', 'decimal:0,2'],
            'month' => ['required', 'date_format:Y-m'],
        ];
    }
}
