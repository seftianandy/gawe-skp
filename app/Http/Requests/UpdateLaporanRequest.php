<?php

namespace App\Http\Requests;

use App\Models\Laporan;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLaporanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('periode')) {
            return;
        }

        $periode = CarbonImmutable::parse($this->input('periode'));

        $this->merge([
            'bulan' => $periode->month,
            'tahun' => $periode->year,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Laporan|null $laporan */
        $laporan = $this->route('laporan');

        return [
            'periode' => ['required', 'date'],
            'bulan' => [
                'required',
                'integer',
                'between:1,12',
                Rule::unique((new Laporan())->getTable())
                    ->ignore($laporan?->id)
                    ->where('user_id', $this->user()?->id)
                    ->where('tahun', $this->input('tahun')),
            ],
            'tahun' => ['required', 'integer', 'between:2000,2100'],
            'status' => ['required', Rule::in(['draft', 'submit', 'final'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'bulan.unique' => 'Anda sudah memiliki laporan untuk bulan dan tahun tersebut.',
        ];
    }
}
