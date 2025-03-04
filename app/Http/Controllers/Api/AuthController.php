<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Traits\ApiResponses;
use Auth;
use Illuminate\Http\Request;


class AuthController extends Controller
{
	use ApiResponses;


	public function register(RegisterRequest $request)
	{
		$request->validated($request->only([
			'first_name',
			'last_name',
			'phone',
			'email',
			'password',
			'password_confirmation',
		]));

		$user = User::create([
			'first_name' => $request->first_name,
			'last_name' => $request->last_name,
			'phone' => $request->phone,
			'email' => $request->email,
			'password' => bcrypt($request->password),
			'is_customer' => isset($request->is_customer) ? $request->is_customer : true,
		]);

		return $this->ok(
			'Đăng ký thành công.',
			['user' => $user]
		);
	}

	public function login(LoginRequest $request)
	{
		$request->validated($request->only(['email', 'password']));

		if (!Auth::attempt($request->only('email', 'password'))) {
			return $this->error('Thông tin đăng nhập không đúng! Vui lòng kiểm tra lại', 401);
		}

		$user = User::firstWhere('email', $request->email);
		$token = $user->createToken(
			'API token for ' . $request->email,
			['*'],
			now()->addHour()
		)->plainTextToken;

		return $this->ok('Đăng nhập thành công!', [
			'token' => $token,
			'user' => $user
		]);
	}

	public function logout(Request $request)
	{
		$request->user()->currentAccessToken()->delete();

		return $this->ok('Đăng xuất thành công.');
	}
}
