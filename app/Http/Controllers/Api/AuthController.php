<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\V1\UserResource;
use App\Models\User;
use App\Traits\ApiResponses;
use Auth;
use Illuminate\Http\Request;


class AuthController extends Controller
{
	use ApiResponses;


	public function register(RegisterRequest $request)
	{
		$data = $request->validated();

		$user = User::create(array_merge($data, [
			'password' => bcrypt($data['password']),
			'is_customer' => $data['is_customer'] ?? true,
		]));

		return $this->ok('Đăng ký thành công.', [
			'user' => new UserResource($user),
		]);
	}

	public function login(LoginRequest $request)
	{
		$request->validated($request->only(['email', 'password']));

		if (!Auth::attempt($request->only(['email', 'password']))) {
			return $this->error('Thông tin đăng nhập không đúng! Vui lòng kiểm tra lại', 401);
		}

		$user = User::where('email', $request->email)->first();
		$token = $user->createToken(
			'API token for ' . $request->email,
			['*'],
			now()->addHour()
		)->plainTextToken;

		return $this->ok('Đăng nhập thành công!', [
			'token' => $token,
			'user' => new UserResource($user),
		]);
	}

	public function logout(Request $request)
	{
		$request->user()->currentAccessToken()->delete();

		return $this->ok('Đăng xuất thành công.');
	}
}
