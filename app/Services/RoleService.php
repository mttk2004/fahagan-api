<?php

namespace App\Services;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleService
{
  /**
   * Lấy tất cả các role
   *
   * @return \Illuminate\Database\Eloquent\Collection
   */
  public function getAllRoles()
  {
    return Role::with('permissions')->get();
  }

  /**
   * Thêm quyền cho role
   *
   * @param Role|int|string $role
   * @param array $permissions
   * @return Role
   */
  public function addPermissions($role, array $permissions)
  {
    if (!$role instanceof Role) {
      $role = Role::findById($role);
    }

    $role->givePermissionTo($permissions);

    return $role->load('permissions');
  }

  /**
   * Xóa quyền khỏi role
   *
   * @param Role|int|string $role
   * @param array $permissions
   * @return Role
   */
  public function removePermissions($role, array $permissions)
  {
    if (!$role instanceof Role) {
      $role = Role::findById($role);
    }

    $role->revokePermissionTo($permissions);

    return $role->load('permissions');
  }

  /**
   * Đồng bộ quyền cho role
   *
   * @param Role|int|string $role
   * @param array $permissions
   * @return Role
   */
  public function syncPermissions($role, array $permissions)
  {
    if (!$role instanceof Role) {
      $role = Role::findById($role);
    }

    $role->syncPermissions($permissions);

    return $role->load('permissions');
  }
}
