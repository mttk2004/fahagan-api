<?php

namespace App\Utils;


use Auth;


class AuthUtils
{
	public static function user()
	{
		return Auth::guard('sanctum')->user();
	}

	public static function userCan($permission)
	{
		$user = self::user();

		return $user ? $user->hasPermissionTo($permission) : false;
	}
}
