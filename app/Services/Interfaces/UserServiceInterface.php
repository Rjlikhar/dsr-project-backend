<?php

namespace App\Services\Interfaces;

use App\Models\User;
use Illuminate\Http\Request;

interface UserServiceInterface
{
    public function register(array $data);
    public function login(array $data);
    public function logout(User $user);
    public function changePassword(array $data, $user);
    public function saveAppPassword(int $userId, string $appPassword): array;
    public function updateMentorReviewer($id, array $data);

}