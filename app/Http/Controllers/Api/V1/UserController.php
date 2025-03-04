<?php

namespace App\Http\Controllers\Api\V1;


use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;


class UserController extends Controller
{
	use ApiResponses;

	public function index()
	{
		return $this->ok('Success', [
			'data' => User::all()
		]);
	}

	public function store(Request $request) {}

	public function show(User $user) {}

	public function update(Request $request, User $user) {}

	public function destroy(User $user) {}
}
