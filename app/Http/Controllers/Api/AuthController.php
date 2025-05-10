<?php

namespace App\Http\Controllers\Api;

use App\DTOs\UserDTO;
use App\Enums\ResponseMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\V1\UserResource;
use App\Models\User;
use App\Services\CustomerService;
use App\Traits\HandleExceptions;
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
    use HandleExceptions;

    public function __construct(
        private readonly CustomerService $customerService,
        private readonly string $entityName = 'user'
    ) {
    }

    /**
     * Register a new user
     *
     *
     * @return JsonResponse
     *
     * @group Auth
     *
     * @unauthenticated
     */
    public function register(RegisterRequest $request)
    {
        try {
            $validated = $request->validated();

            $userDTO = new UserDTO(
                first_name: $validated['first_name'],
                last_name: $validated['last_name'],
                email: $validated['email'],
                phone: $validated['phone'],
                password: $validated['password'],
                is_customer: $validated['is_customer'] ?? true,
            );

            $user = $this->customerService->createCustomer($userDTO);

            return ResponseUtils::created([
              'user' => new UserResource($user),
            ], ResponseMessage::REGISTER_SUCCESS->value);
        } catch (Exception $e) {
            return $this->handleException(
                $e,
                $this->entityName,
                [
                'request' => $request->all(),
        ]
            );
        }
    }

    /**
     * Login
     *
     *
     * @return JsonResponse
     *
     * @group Auth
     *
     * @unauthenticated
     */
    public function login(LoginRequest $request)
    {
        $validated = $request->validated();

        if (! Auth::attempt(['email' => $validated['email'], 'password' => $validated['password']])) {
            return ResponseUtils::unauthorized(ResponseMessage::LOGIN_FAILED->value);
        }

        $user = User::where('email', $validated['email'])->first();
        $user->update(['last_login' => now()]);

        $token = $user->createToken(
            'API token for ' . $validated['email'],
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
     *
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
     *
     * @return JsonResponse
     *
     * @group Auth
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        $user = AuthUtils::user();
        if (! $user) {
            return ResponseUtils::unauthorized();
        }

        $validated = $request->validated();

        // Check if the old password is correct
        if (! Hash::check($validated['old_password'], $user->password)) {
            return ResponseUtils::validationError(ResponseMessage::WRONG_OLD_PASSWORD->value);
        }

        try {
            $userDTO = new UserDTO(
                first_name: null,
                last_name: null,
                email: null,
                phone: null,
                password: $validated['new_password'],
                is_customer: null,
            );

            $this->customerService->updateCustomer($user->id, $userDTO);

            return ResponseUtils::noContent(ResponseMessage::CHANGE_PASSWORD_SUCCESS->value);
        } catch (Exception $e) {
            return $this->handleException(
                $e,
                $this->entityName,
                [
                'request' => $request->all(),
        ]
            );
        }
    }

    /**
     * Forgot password
     *
     *
     * @return JsonResponse
     *
     * @group Auth
     */
    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $validated = $request->validated();

        $status = Password::sendResetLink(['email' => $validated['email']]);

        return $status === Password::RESET_LINK_SENT
          ? ResponseUtils::success([], 'Email đặt lại mật khẩu đã được gửi.')
          : ResponseUtils::badRequest('Có lỗi xảy ra, vui lòng thử lại.');
    }

    /**
     * Reset password
     *
     *
     * @return JsonResponse
     *
     * @group Auth
     */
    public function resetPassword(Request $request)
    {
        $data = $request->all();

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
