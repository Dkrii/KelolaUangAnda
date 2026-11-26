<?php

namespace App\Http\Requests;

use App\Enums\WalletType;
use App\Models\Wallet;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWalletRequest extends FormRequest
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
        /** @var Wallet $wallet */
        $wallet = $this->route('wallet');

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('wallets', 'name')
                    ->where('user_id', $this->user()->id)
                    ->whereNull('deleted_at')
                    ->ignore($wallet->id),
            ],
            'type' => ['required', Rule::enum(WalletType::class)],
            'institution' => ['nullable', 'string', 'max:100'],
            'account_number' => ['nullable', 'string', 'max:50'],
            'initial_balance' => ['nullable', 'numeric', 'min:0', 'max:9999999999999.99'],
            'color' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'initial_balance' => $this->input('initial_balance', 0),
            'color' => $this->input('color', '#10b981'),
            'is_active' => $this->has('is_active') ? $this->boolean('is_active') : true,
        ]);
    }
}
