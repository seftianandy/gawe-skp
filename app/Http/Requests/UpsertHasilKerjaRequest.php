<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Schema;

class UpsertHasilKerjaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $rencanaAksi = collect($this->input('rencana_aksi', []))
            ->map(function (mixed $item): string {
                if (is_string($item)) {
                    return $item;
                }

                if (is_array($item)) {
                    return (string) ($item['deskripsi'] ?? '');
                }

                return (string) $item;
            })
            ->values()
            ->all();

        $this->merge([
            'rencana_aksi' => $rencanaAksi,
        ]);
    }

    /**
     * @return array<string, array<mixed>|string>
     */
    public function rules(): array
    {
        $masterExistsRule = Schema::hasTable('indikator_kinerja_masters')
            ? [Rule::exists('indikator_kinerja_masters', 'id')]
            : ['integer'];

        return [
            'indikator_kinerja_id' => [
                'required',
                'integer',
                ...$masterExistsRule,
            ],
            'rencana_aksi' => ['required', 'array', 'min:1'],
            'rencana_aksi.*' => ['required', 'string'],
            'bukti_foto_baru' => ['nullable', 'array', 'max:5'],
            'bukti_foto_baru.*' => ['nullable', File::image()->max(5 * 1024)],
        ];
    }
}
