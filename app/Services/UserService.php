<?php

namespace App\Services;

use App\DTO\User\RegisterUserDTO;
use App\Models\Dsr;
use App\Models\User;
use App\Services\Interfaces\UserServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;

class UserService implements UserServiceInterface
{
    public function register(array $data): User
    {
        $dto = RegisterUserDTO::fromArray($data);
        return User::create([
            'name' => $dto->name,
            'email' => $dto->email,
            'password' => Hash::make($dto->password),
            'department_id' => 1,
            'role_id' => 2,
        ]);
    }

    public function login(array $data): ?array
    {
        $user = User::where('email', $data['email'])->first();

        if (!$user) {
            return null;
        }

        if (!Hash::check($data['password'], $user->password)) {
            return null;
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token
        ];
    }


    public function logout(User $user): bool
    {
        $token = $user->currentAccessToken();
        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }
        return true;
    }

    public function changePassword(array $data, $user)
    {
        // Check current password
        if (!Hash::check(
            $data['current_password'],
            $user->password
        )) {

            return response()->json([
                'message' => 'Current password is incorrect'
            ], 400);
        }

        // Update password
        $user->password = Hash::make(
            $data['new_password']
        );

        $user->save();

        Log::info('Password changed successfully', [
            'user_id' => $user->id,
            'email' => $user->email
        ]);

        return response()->json([
            'message' => 'Password changed successfully'
        ], 200);
    }


     public function saveAppPassword(int $userId, string $appPassword): array
{
    Log::info("ENTERED FUNCTION");

    try {

        Log::info("TRY START");

        $user = User::find($userId);

        Log::info("USER FETCHED", ['user' => $user]);

        if (!$user) {
            Log::info("USER NOT FOUND");
            return [
                'status' => false,
                'message' => 'User not found.',
                'status_code' => 404,
            ];
        }

        $encrypted = Crypt::encryptString($appPassword);

        Log::info("PASSWORD ENCRYPTED");

        $updated = $user->update([
            'app_password' => $encrypted,
        ]);

        Log::info("UPDATE RESULT", ['updated' => $updated]);

        return [
            'status' => true,
            'message' => 'App password saved successfully.',
            'user_id' => $user->id,
            'status_code' => 200,
        ];

    } catch (\Throwable $e) {

        Log::error('CATCH EXECUTED', [
            'user_id' => $userId,
            'error' => $e->getMessage(),
        ]);

        return [
            'status' => false,
            'message' => 'Failed to save app password.',
            'status_code' => 500,
        ];
    }
}
}
