<?php

namespace App\Services\Interfaces;


interface GroupInterface
{
    public function getAllGroups();
    public function updateUserGroup($userId, $groupId);
}