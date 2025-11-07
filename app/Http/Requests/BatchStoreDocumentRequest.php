<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BatchStoreDocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'documents' => 'required|array',
            'documents.*.title' => 'required|string|max:255',
            'documents.*.path' => 'nullable|string|max:1000',
            'documents.*.body' => 'required|string',
            'documents.*.tags' => 'array',
            'documents.*.metadata' => 'array'
        ];
    }
}
