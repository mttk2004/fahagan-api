<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Role;

class RoleService
{
    /**
     * Lấy tất cả các role
     *
     * @return Collection
     */
    public function getAllRoles(): Collection
    {
        return Role::with('permissions')->get();
    }

    /**
     * Thêm quyền cho role
     *
     * @param int|string|Role $role
     * @param array           $permissions
     *
     * @return Role
     */
    public function addPermissions(Role|int|string $role, array $permissions): Role
    {
        if (! $role instanceof Role) {
            $role = Role::findById($role);
        }

        $role->givePermissionTo($permissions);

        return $role->load('permissions');
    }

    /**
     * Xóa quyền khỏi role
     *
     * @param int|string|Role $role
     * @param array           $permissions
     *
     * @return Role
     */
    public function removePermissions(Role|int|string $role, array $permissions): Role
    {
        if (! $role instanceof Role) {
            $role = Role::findById($role);
        }

        $role->revokePermissionTo($permissions);

        return $role->load('permissions');
    }

    /**
     * Đồng bộ quyền cho role
     *
     * @param int|string|Role $role
     * @param array           $permissions
     *
     * @return Role
     */
    public function syncPermissions(
        Role|int|string $role,
        array $permissions
    ): Role {
        if (! $role instanceof Role) {
            $role = Role::findById($role);
        }

        $role->syncPermissions($permissions);

        return $role->load('permissions');
    }
}
