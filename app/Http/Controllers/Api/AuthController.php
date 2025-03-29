<?php

namespace App\Http\Controllers\Api;

use App\Enums\ResponseMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\V1\UserResource;
use App\Models\User;
use App\Utils\AuthUtils;
use App\Utils\ResponseUtils;
use Auth;
use Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    /**
     * Register a new user
     *
     * @param RegisterRequest $request
     *
     * @return JsonResponse
     * @group Auth
     * @unauthenticated
     */
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'is_customer' => $data['is_customer'] ?? true,
        ]);

        return ResponseUtils::created([
            'user' => new UserResource($user),
        ], ResponseMessage::REGISTER_SUCCESS->value);
    }

    /**
     * Login
     *
     * @param LoginRequest $request
     *
     * @return JsonResponse
     * @group Auth
     * @unauthenticated
     */
    public function login(LoginRequest $request)
    {
        $request->validated();

        if (! Auth::attempt($request->only(['email', 'password']))) {
            return ResponseUtils::unauthorized(ResponseMessage::LOGIN_FAILED->value);
        }

        $user = User::where('email', $request->email)->first();
        $user->update(['last_login' => now()]);

        $token = $user->createToken(
            'API token for ' . $request->email,
            ['*'],
            now()->addWeek()
        )->plainTextToken;

        return ResponseUtils::success([
            'token' => $token,
            'user' => new UserResource($user),
        ], ResponseMessage::LOGIN_SUCCESS->value);
    }

    /**
     * Logout
     *
     * @return JsonResponse
     * @group Auth
     */
    public function logout()
    {
        AuthUtils::user()->currentAccessToken()->delete();

        return ResponseUtils::noContent(ResponseMessage::LOGOUT_SUCCESS->value);
    }

    /**
     * Change password
     *
     * @param ChangePasswordRequest $request
     *
     * @return JsonResponse
     * @group Auth
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        $user = AuthUtils::user();
        if (! $user) {
            return ResponseUtils::unauthorized();
        }

        $validatedData = $request->validated();

        // Check if the old password is correct
        if (! Hash::check($validatedData['old_password'], $user->password)) {
            return ResponseUtils::validationError(ResponseMessage::WRONG_OLD_PASSWORD->value);
        }

        $user->update([
            'password' => bcrypt($validatedData['new_password']),
        ]);

        return ResponseUtils::noContent(ResponseMessage::CHANGE_PASSWORD_SUCCESS->value);
    }

    /**
     * Forgot password
     *
     * @param ForgotPasswordRequest $request
     *
     * @return JsonResponse
     * @group Auth
     */
    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? ResponseUtils::success([], 'Email đặt lại mật khẩu đã được gửi.')
            : ResponseUtils::badRequest('Có lỗi xảy ra, vui lòng thử lại.');
    }

    /**
     * Reset password
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @group Auth
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|string|email',
            'password' => 'required|string|confirmed|min:8',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? ResponseUtils::success([], 'Mật khẩu đã được đặt lại thành công.')
            : ResponseUtils::badRequest('Có lỗi xảy ra, vui lòng thử lại.');
    }
}
