<?php

namespace App\Services;

use App\Models\Group;
use App\Models\User;
use App\Services\Interfaces\GroupInterface;
use Illuminate\Support\Facades\Log;

class GroupService implements GroupInterface
{

    public function getAllGroups()
    {
        return Group::all();
    }

    public function updateUserGroup($userId, $groupId)
    {
        Log::info('Updating user group', [
            'user_id' => $userId,
            'group_id' => $groupId
        ]);

        $user = User::findOrFail($userId);

        $user->group_id = $groupId;
        $user->save();

        return $user;
    }
}
