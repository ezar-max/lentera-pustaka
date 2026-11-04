<?php

namespace App\Policies;

use App\Models\Peminjaman;
use App\Models\User;

class PeminjamanPolicy
{
    public function view(User $user, Peminjaman $peminjaman): bool
    {
        if ($user->isStaf()) {
            return true;
        }

        return $peminjaman->user_id === $user->id;
    }

    public function update(User $user, Peminjaman $peminjaman): bool
    {
        if ($user->isStaf()) {
            return true;
        }

        return $peminjaman->user_id === $user->id;
    }
}
