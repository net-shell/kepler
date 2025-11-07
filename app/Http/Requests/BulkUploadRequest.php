<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkUploadRequest extends FormRequest
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
            'file' => 'required_without:files|file|mimes:csv,txt,xlsx,xls,pdf,json,tsv|max:10240',
            'files' => 'required_without:file|array',
            'files.*' => 'file|mimes:csv,txt,xlsx,xls,pdf,json,tsv|max:10240'
        ];
    }
}
