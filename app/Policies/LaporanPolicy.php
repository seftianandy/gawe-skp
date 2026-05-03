<?php

namespace App\Policies;

use App\Models\Laporan;
use App\Models\User;

class LaporanPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Laporan $laporan): bool
    {
        return $laporan->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Laporan $laporan): bool
    {
        return $laporan->user_id === $user->id;
    }

    public function delete(User $user, Laporan $laporan): bool
    {
        return $laporan->user_id === $user->id;
    }
}
