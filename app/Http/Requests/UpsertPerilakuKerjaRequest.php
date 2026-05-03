<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class UpsertPerilakuKerjaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'deskripsi' => ['required', 'string'],
            'hapus_bukti_perilaku' => ['nullable', 'array'],
            'hapus_bukti_perilaku.*' => ['integer'],
            'bukti_perilaku_baru' => ['nullable', 'array'],
            'bukti_perilaku_baru.*' => [
                'nullable',
                File::image()->max(5 * 1024),
            ],
        ];
    }
}
