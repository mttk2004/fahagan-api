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
            $validated = $request->validated();

            // Get attributes from JSON:API format
            $attributes = $validated;
            if (isset($validated['data']) && isset($validated['data']['attributes'])) {
                $attributes = $validated['data']['attributes'];
            }

            $userDTO = new UserDTO(
                first_name: $attributes['first_name'],
                last_name: $attributes['last_name'],
                email: $attributes['email'],
                phone: $attributes['phone'],
                password: $attributes['password'],
                is_customer: $attributes['is_customer'] ?? true,
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
        $validated = $request->validated();

        // Lấy attributes từ định dạng JSON:API
        $attributes = $validated;
        if (isset($validated['data']) && isset($validated['data']['attributes'])) {
            $attributes = $validated['data']['attributes'];
        }

        if (! Auth::attempt(['email' => $attributes['email'], 'password' => $attributes['password']])) {
            return ResponseUtils::unauthorized(ResponseMessage::LOGIN_FAILED->value);
        }

        $user = User::where('email', $attributes['email'])->first();
        $user->update(['last_login' => now()]);

        $token = $user->createToken(
            'API token for ' . $attributes['email'],
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
        $user = AuthUtils::user();
        if ($user && method_exists($user->currentAccessToken(), 'delete')) {
            $user->currentAccessToken()->delete();
        }

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

        // Lấy attributes từ định dạng JSON:API
        $attributes = $validatedData;
        if (isset($validatedData['data']) && isset($validatedData['data']['attributes'])) {
            $attributes = $validatedData['data']['attributes'];
        }

        // Check if the old password is correct
        if (! Hash::check($attributes['old_password'], $user->password)) {
            return ResponseUtils::validationError(ResponseMessage::WRONG_OLD_PASSWORD->value);
        }

        try {
            $userDTO = new UserDTO(
                first_name: null,
                last_name: null,
                email: null,
                phone: null,
                password: $attributes['new_password'],
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
        $validated = $request->validated();

        // Lấy attributes từ định dạng JSON:API
        $attributes = $validated;
        if (isset($validated['data']) && isset($validated['data']['attributes'])) {
            $attributes = $validated['data']['attributes'];
        }

        $status = Password::sendResetLink(['email' => $attributes['email']]);

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
        $data = $request->all();

        // Xử lý dữ liệu từ định dạng JSON:API nếu có
        if (isset($data['data']) && isset($data['data']['attributes'])) {
            $data = $data['data']['attributes'];
        }

        $validator = validator($data, [
            'token' => 'required',
            'email' => 'required|string|email',
            'password' => 'required|string|confirmed|min:8',
        ]);

        if ($validator->fails()) {
            return ResponseUtils::validationError($validator->errors()->first());
        }

        $status = Password::reset(
            $validator->validated(),
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
