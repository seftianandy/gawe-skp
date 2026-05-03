<?php

namespace App\Concerns;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

trait ProfileValidationRules
{
    /**
     * Get the validation rules used to validate user profiles.
     *
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    protected function profileRules(?int $userId = null): array
    {
        return [
            'nama_instansi' => $this->namaInstansiRules(),
            'nama' => $this->namaRules(),
            'nip' => $this->nipRules($userId),
            'jabatan' => $this->jabatanRules(),
            'email' => $this->emailRules($userId),
        ];
    }

    /**
     * Get the validation rules used to validate institution names.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function namaInstansiRules(): array
    {
        return ['required', 'string', 'max:255'];
    }

    /**
     * Get the validation rules used to validate employee names.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function namaRules(): array
    {
        return ['required', 'string', 'max:255'];
    }

    /**
     * Get the validation rules used to validate employee IDs.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function nipRules(?int $userId = null): array
    {
        return [
            'required',
            'string',
            'max:50',
            $userId === null
                ? Rule::unique(User::class, 'nip')
                : Rule::unique(User::class, 'nip')->ignore($userId),
        ];
    }

    /**
     * Get the validation rules used to validate job titles.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function jabatanRules(): array
    {
        return ['required', 'string', 'max:255'];
    }

    /**
     * Get the validation rules used to validate user emails.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function emailRules(?int $userId = null): array
    {
        return [
            'nullable',
            'string',
            'email',
            'max:255',
            $userId === null
                ? Rule::unique(User::class)
                : Rule::unique(User::class)->ignore($userId),
        ];
    }
}
