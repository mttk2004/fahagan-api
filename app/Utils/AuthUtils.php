<?php

namespace App\Utils;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthUtils
{
    public static function user()
    {
        return Auth::guard('sanctum')->user();
    }

    /**
     * Kiểm tra xem người dùng đã đăng nhập hay chưa
     */
    public static function check()
    {
        return Auth::guard('sanctum')->check();
    }

    public static function userCan($permission)
    {
        $user = self::user();

        if (! $user) {
            return false;
        }

        $hasPermission = $user->hasPermissionTo($permission);

        // Debug information
        Log::debug("Permission check for [$permission]", [
            'user_id' => $user->id,
            'user_roles' => $user->getRoleNames(),
            'has_permission' => $hasPermission,
            'all_permissions' => $user->getAllPermissions()->pluck('name'),
        ]);

        return $hasPermission;
    }
}
