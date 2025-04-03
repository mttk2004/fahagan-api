<?php

namespace App\Http\Controllers\Api;

use App\DTOs\User\UserDTO;
use App\Enums\ResponseMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\V1\UserResource;
use App\Models\User;
use App\Services\UserService;
use App\Traits\HandleUserExceptions;
use App\Utils\AuthUtils;
use App\Utils\ResponseUtils;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    use HandleUserExceptions;

    public function __construct(
        private readonly UserService $userService
    ) {
    }

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
        try {
            $userDTO = new UserDTO(
                first_name: $request->validated()['first_name'],
                last_name: $request->validated()['last_name'],
                email: $request->validated()['email'],
                phone: $request->validated()['phone'],
                password: $request->validated()['password'],
                is_customer: $request->validated()['is_customer'] ?? true,
            );

            $user = $this->userService->createUser($userDTO);

            return ResponseUtils::created([
                'user' => new UserResource($user),
            ], ResponseMessage::REGISTER_SUCCESS->value);
        } catch (Exception $e) {
            return $this->handleUserException($e, $request->validated(), null, 'đăng ký');
        }
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

        $user = User::where('email', $request->input('email'))->first();
        $user->update(['last_login' => now()]);

        $token = $user->createToken(
            'API token for ' . $request->input('email'),
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

        try {
            $userDTO = new UserDTO(
                first_name: null,
                last_name: null,
                email: null,
                phone: null,
                password: $validatedData['new_password'],
                is_customer: null,
            );

            $this->userService->updateUser($user->id, $userDTO);

            return ResponseUtils::noContent(ResponseMessage::CHANGE_PASSWORD_SUCCESS->value);
        } catch (Exception $e) {
            return $this->handleUserException($e, $request->validated(), $user->id, 'đổi mật khẩu');
        }
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
