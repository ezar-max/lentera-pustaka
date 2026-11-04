<?php

namespace App\Policies;

use App\Models\Percakapan;
use App\Models\User;

class PercakapanPolicy
{
    public function view(User $user, Percakapan $percakapan): bool
    {
        if ($user->isStaf() && $percakapan->tipe === 'petugas') {
            return true;
        }

        return $percakapan->user_id === $user->id;
    }

    public function update(User $user, Percakapan $percakapan): bool
    {
        if ($user->isStaf() && $percakapan->tipe === 'petugas') {
            return true;
        }

        return $percakapan->user_id === $user->id;
    }
}
