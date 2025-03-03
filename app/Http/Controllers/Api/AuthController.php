<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Traits\ApiResponses;
use Auth;


class AuthController extends Controller
{
	use ApiResponses;


	public function register(RegisterRequest $request)
	{
		$request->validated();

		$user = User::create([
			'first_name' => $request->first_name,
			'last_name' => $request->last_name,
			'phone' => $request->phone,
			'email' => $request->email,
			'password' => bcrypt($request->password),
			'is_customer' => isset($request->is_customer) ? $request->is_customer : true,
		]);

		return $this->ok(
			'Register Successfully!',
			['user' => $user]
		);
	}

	public function login(LoginRequest $request)
	{
		$request->validated();

		if (!Auth::attempt($request->only('email', 'password'))) {
			return $this->error('Invalid Credentials!', 401);
		}

		$user = User::firstWhere('email', $request->email);
		$token = $user->createToken(
			'API token for ' . $request->email,
			['*'],
			now()->addHour()
		)->plainTextToken;

		return $this->ok('Authenticated!', ['token' => $token]);
	}
}
