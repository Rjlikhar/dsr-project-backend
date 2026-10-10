<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\Interfaces\UserServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;

class UserController extends Controller
{
    public function __construct(
        protected UserServiceInterface $userService
    ) {
    }


    public function saveAppPassword(Request $request)
    {
        $request->validate([
            'app_password' => 'required|string|min:8',
        ]);

        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $result = $this->userService->saveAppPassword(
            $user->id,
            str_replace(' ', '', $request->app_password)
        );

        return response()->json($result, $result['status_code']);
    }

    public function register(RegisterRequest $request)
    {

        if (Auth::user()->role_id != 1) {
            return response()->json([

                'status' => false,
                'message' => 'Unauthorized access',
            ], 403);
        }

        $user = $this->userService->register($request->validated());

        Log::info('User Registered Successfully', [
            'user_id' => $user
        ]);

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        Log::info("Login attempt");

        $result = $this->userService->login($request->validated());

        Log::info('login_result', [
            'result' => $result
        ]);
        if (!$result) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        return response()->json([
            'message' => 'Login successful',
            'user' => $result['user'],
            'token' => $result['token']
        ], 200);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user()
        ]);
    }

    public function logout(Request $request)
    {
        $this->userService->logout($request->user());
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6',
        ]);

        return $this->userService->changePassword(
            $request->all(),
            $request->user()
        );
    }

    public function forgotPassword(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
        ]);

        $status = $this->userService->sendPasswordResetLink($data);

        if ($status === Password::INVALID_USER) {
            return response()->json([
                'status' => false,
                'message' => 'This email address is not registered.',
            ], 404);
        }

        if ($status === Password::RESET_THROTTLED) {
            return response()->json([
                'status' => false,
                'message' => 'Please wait before requesting another reset email.',
            ], 429);
        }

        if ($status !== Password::RESET_LINK_SENT) {
            return response()->json([
                'status' => false,
                'message' => 'Could not send the password reset email.',
            ], 500);
        }

        return response()->json([
            'status' => true,
            'message' => 'Password reset token sent to your email.',
        ], 200);
    }

    public function resetPassword(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = $this->userService->resetPassword($data);

        if ($status !== Password::PASSWORD_RESET) {
            return response()->json([
                'message' => 'Invalid or expired reset token.',
            ], 422);
        }

        return response()->json([
            'message' => 'Password reset successfully. Please log in again.',
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'mentor' => 'nullable|string',
            'code_reviewer' => 'nullable|string',
        ]);

        $user = $this->userService->updateMentorReviewer($id, $validated);

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => $user,
        ], 200);
    }

    public function index()
    {
        return $this->userService->getAllUsers();
    }
}
