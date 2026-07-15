<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class CaseDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->legalCases()->whereKey($this->route('caso'))->exists() ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'checklist_item_id' => [
                'nullable',
                'integer',
                Rule::exists('checklist_items', 'id')->where(
                    fn ($query) => $query->where('legal_case_id', $this->route('caso')),
                ),
            ],
            'document' => [
                'required',
                File::types(['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'])->max(10 * 1024),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'checklist_item_id' => 'item do checklist',
            'document' => 'documento',
        ];
    }
}
