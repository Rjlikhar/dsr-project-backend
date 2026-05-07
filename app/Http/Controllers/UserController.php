<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\Interfaces\UserServiceInterface;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function __construct(
        protected UserServiceInterface $userService
    ) {}


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
            $request->app_password
        );

        return response()->json($result, $result['status_code']);
    }

    public function register(RegisterRequest $request)
    {

        Log::info("Hello");
        $user = $this->userService->register($request->validated());

        Log::info('User Registered Successfully', [
            'user_id' => $user->id,
            'email' => $user->email
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
}
