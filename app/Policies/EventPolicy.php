<?php

namespace App\Policies;

use App\Models\User;

class EventPolicy
{
    public function update(User $user, $event): bool
    {
        return $user->id === $event->meetupGroup->user_id;
    }
}
