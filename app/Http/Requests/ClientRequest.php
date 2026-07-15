<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class ClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'document' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nome',
            'email' => 'e-mail',
            'phone' => 'telefone',
            'document' => 'CPF/CNPJ',
            'notes' => 'observações',
        ];
    }

    protected function prepareForValidation(): void
    {
        $email = trim((string) $this->input('email'));

        $this->merge([
            'name' => trim((string) $this->input('name')),
            'email' => $email === '' ? null : Str::lower($email),
            'phone' => $this->nullableText('phone'),
            'document' => $this->nullableText('document'),
            'notes' => $this->nullableText('notes'),
        ]);
    }

    private function nullableText(string $field): ?string
    {
        $value = trim((string) $this->input($field));

        return $value === '' ? null : $value;
    }
}
