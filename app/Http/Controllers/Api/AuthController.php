<?php

namespace App\Http\Controllers\Api;


use App\Enums\ResponseMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\V1\UserResource;
use App\Models\User;
use App\Utils\ResponseUtils;
use Auth;
use Illuminate\Http\JsonResponse;


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

		return ResponseUtils::created(ResponseMessage::REGISTER_SUCCESS->value, [
			'user' => new UserResource($user),
		]);
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

		if (!Auth::attempt($request->only(['email', 'password']))) {
			return ResponseUtils::unauthorized(ResponseMessage::LOGIN_FAILED->value);
		}

		$user = User::where('email', $request->email)->first();
		$user->update(['last_login' => now()]);

		$token = $user->createToken(
			'API token for ' . $request->email,
			['*'],
			now()->addDay()
		)->plainTextToken;

		return ResponseUtils::success(ResponseMessage::LOGIN_SUCCESS->value, [
			'token' => $token,
			'user' => new UserResource($user),
		]);
	}

	/**
	 * Logout
	 *
	 * @return JsonResponse
	 * @group Auth
	 */
	public function logout()
	{
		Auth::guard('sanctum')->user()->currentAccessToken()->delete();

		return ResponseUtils::success(ResponseMessage::LOGOUT_SUCCESS->value);
	}
}
