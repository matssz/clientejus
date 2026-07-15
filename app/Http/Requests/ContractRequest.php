<?php

namespace App\Http\Requests;

use App\Models\Contract;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class ContractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'legal_case_id' => [
                'required',
                'integer',
                Rule::exists('legal_cases', 'id')->where(
                    fn ($query) => $query->where('user_id', $this->user()->id),
                ),
            ],
            'title' => ['required', 'string', 'max:255'],
            'signed_at' => ['required', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:signed_at'],
            'status' => ['required', Rule::in(array_keys(Contract::statuses()))],
            'original_document' => [
                'nullable',
                File::types(['pdf', 'doc', 'docx'])->max(10 * 1024),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'legal_case_id' => 'caso',
            'title' => 'título',
            'signed_at' => 'data de assinatura',
            'expires_at' => 'data de vencimento',
            'status' => 'status',
            'original_document' => 'documento original',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'title' => trim((string) $this->input('title')),
            'expires_at' => $this->input('expires_at') ?: null,
        ]);
    }
}
