<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\PermissionAdjustRequest;
use App\Http\Resources\V1\RoleCollection;
use App\Http\Resources\V1\RoleResource;
use App\Services\RoleService;
use App\Traits\HandleExceptions;
use App\Utils\ResponseUtils;
use Spatie\Permission\Models\Role;

class AdminRoleController extends Controller
{
    use HandleExceptions;

    public function __construct(
        private readonly RoleService $roleService,
        private readonly string $entityName = 'role'
    ) {
    }

    /**
     * Get all roles
     *
     * @return RoleCollection
     * @group Admin.Roles
     * @authenticated
     */
    public function index()
    {
        $roles = $this->roleService->getAllRoles();

        return new RoleCollection($roles);
    }

    /**
     * Add permissions to role
     *
     * @param PermissionAdjustRequest $request
     * @param Role $role
     * @return RoleResource
     * @group Admin.Roles
     * @authenticated
     */
    public function addPermissions(PermissionAdjustRequest $request, Role $role)
    {
        try {
            $permissions = $request->validated('permissions');
            $updatedRole = $this->roleService->addPermissions($role, $permissions);

            return ResponseUtils::success([
              'role' => new RoleResource($updatedRole),
            ]);
        } catch (\Exception $e) {
            return $this->handleException($e, $this->entityName, [
              'role_id' => $role->id,
            ]);
        }
    }

    /**
     * Remove permissions from role
     *
     * @param PermissionAdjustRequest $request
     * @param Role $role
     * @return RoleResource
     * @group Admin.Roles
     * @authenticated
     */
    public function removePermissions(PermissionAdjustRequest $request, Role $role)
    {
        try {
            $permissions = $request->validated('permissions');
            $updatedRole = $this->roleService->removePermissions($role, $permissions);

            return ResponseUtils::success([
              'role' => new RoleResource($updatedRole),
            ]);
        } catch (\Exception $e) {
            return $this->handleException($e, $this->entityName, [
              'role_id' => $role->id,
            ]);
        }
    }

    /**
     * Sync permissions to role
     *
     * @param PermissionAdjustRequest $request
     * @param Role $role
     * @return RoleResource
     * @group Admin.Roles
     * @authenticated
     */
    public function syncPermissions(PermissionAdjustRequest $request, Role $role)
    {
        try {
            $permissions = $request->validated('permissions');
            $updatedRole = $this->roleService->syncPermissions($role, $permissions);

            return ResponseUtils::success([
              'role' => new RoleResource($updatedRole),
            ]);
        } catch (\Exception $e) {
            return $this->handleException($e, $this->entityName, [
              'role_id' => $role->id,
            ]);
        }
    }
}
