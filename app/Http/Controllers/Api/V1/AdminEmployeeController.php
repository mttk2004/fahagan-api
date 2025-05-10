<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\EmployeeStoreRequest;
use App\Http\Requests\V1\PermissionAdjustRequest;
use App\Http\Requests\V1\RoleAdjustRequest;
use App\Http\Resources\V1\UserCollection;
use App\Http\Resources\V1\UserResource;
use App\Models\User;
use App\Services\EmployeeService;
use App\Services\UserService;
use App\Traits\HandleExceptions;
use App\Traits\HandlePagination;
use App\Traits\HandleValidation;
use App\Utils\AuthUtils;
use App\Utils\ResponseUtils;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminEmployeeController extends Controller
{
    use HandleExceptions;
    use HandlePagination;
    use HandleValidation;

    public function __construct(
        private readonly EmployeeService $employeeService,
        private readonly UserService $userService,
        private readonly string $entityName = 'user'
    ) {
    }

    /**
     * Get all employees
     *
     * @return UserCollection|JsonResponse
     * @group Admin.Employees
     * @authenticated
     */
    public function index(Request $request)
    {
        if (! AuthUtils::userCan('view_users')) {
            return ResponseUtils::forbidden();
        }

        $users = $this->userService->getAllUsers($request, $this->getPerPage($request), false);

        return new UserCollection($users);
    }

    /**
     * Get all trashed employees
     *
     * @return UserCollection|JsonResponse
     * @group Admin.Employees
     * @authenticated
     */
    public function trashed(Request $request)
    {
        if (! AuthUtils::userCan('view_users')) {
            return ResponseUtils::forbidden();
        }

        $users = $this->userService->getAllUsers($request, $this->getPerPage($request), true);

        return new UserCollection($users);
    }

    /**
     * Get a employee
     *
     * @return JsonResponse
     * @group Admin.Employees
     * @authenticated
     */
    public function show(int $user_id)
    {
        if (
            ! AuthUtils::userCan('view_users') &&
            AuthUtils::user()->id != $user_id
        ) {
            return ResponseUtils::forbidden();
        }

        try {
            $user = $this->userService->getUserById($user_id);

            return ResponseUtils::success([
              'user' => new UserResource($user),
            ]);
        } catch (Exception $e) {
            return $this->handleException(
                $e,
                $this->entityName,
                [
                'user_id' => $user_id,
        ]
            );
        }
    }

    /**
     * Create a new employee
     *
     * @param EmployeeStoreRequest $request
     * @return JsonResponse
     * @group Admin.Employees
     * @authenticated
     */
    public function store(EmployeeStoreRequest $request)
    {
        if (! AuthUtils::userCan('create_users')) {
            return ResponseUtils::forbidden();
        }

        try {
            $employee = $this->employeeService->createEmployee($request);

            return ResponseUtils::success([
              'employee' => new UserResource($employee),
            ]);
        } catch (Exception $e) {
            return $this->handleException(
                $e,
                $this->entityName,
                [
                "data" => $request->all(),
        ]
            );
        }
    }

    /**
     * Handle employee resource operations (permissions or roles)
     *
     * @param mixed $request
     * @param int $employee_id
     * @param string $operation
     * @param string $resourceType 'permission' or 'role'
     * @return JsonResponse
     */
    private function handleEmployeeResourceOperation(
        Request $request,
        int $employee_id,
        string $operation,
        string $resourceType
    ) {
        $resourceKey = $resourceType === 'permission' ? 'permissions' : 'roles';
        $resources = $request->validated()[$resourceKey];

        try {
            $employee = User::find($employee_id);

            if (! $employee) {
                throw new Exception("Employee not found");
            }

            if ($resourceType === 'permission') {
                switch ($operation) {
                    case 'add':
                        $employee->givePermissionTo($resources);

                        break;
                    case 'remove':
                        $employee->revokePermissionTo($resources);

                        break;
                    case 'sync':
                        $employee->syncPermissions($resources);

                        break;
                    default:
                        throw new Exception("Invalid $resourceType operation");
                }
            } else {
                switch ($operation) {
                    case 'add':
                        $employee->assignRole($resources);

                        break;
                    case 'remove':
                        // removeRole không hỗ trợ mảng, phải xử lý từng role một
                        if (is_array($resources)) {
                            foreach ($resources as $role) {
                                $employee->removeRole($role);
                            }
                        } else {
                            $employee->removeRole($resources);
                        }

                        break;
                    case 'sync':
                        $employee->syncRoles($resources);

                        break;
                    default:
                        throw new Exception("Invalid $resourceType operation");
                }
            }

            return ResponseUtils::success([
              "employee" => new UserResource($employee),
            ]);
        } catch (Exception $e) {
            return $this->handleException(
                $e,
                $this->entityName,
                [
                "data" => $resources,
                "employee_id" => $employee_id,
                "operation" => $operation,
                "resource_type" => $resourceType,
        ]
            );
        }
    }

    /**
     * Handle permission operations for an employee
     *
     * @param PermissionAdjustRequest $request
     * @param int $employee_id
     * @param string $operation
     * @return JsonResponse
     */
    private function handlePermissionOperation(
        PermissionAdjustRequest $request,
        int $employee_id,
        string $operation
    ) {
        return $this->handleEmployeeResourceOperation($request, $employee_id, $operation, 'permission');
    }

    /**
     * Handle role operations for an employee
     *
     * @param RoleAdjustRequest $request
     * @param int $employee_id
     * @param string $operation
     * @return JsonResponse
     */
    private function handleRoleOperation(RoleAdjustRequest $request, int $employee_id, string
    $operation)
    {
        return $this->handleEmployeeResourceOperation($request, $employee_id, $operation, 'role');
    }

    /**
     * Add permissions to an employee
     *
     * @param PermissionAdjustRequest $request
     * @param int $employee_id
     * @return JsonResponse
     * @group Admin.Employees
     * @authenticated
     */
    public function addPermissions(PermissionAdjustRequest $request, int $employee_id)
    {
        return $this->handlePermissionOperation($request, $employee_id, 'add');
    }

    /**
     * Remove permissions from an employee
     *
     * @param PermissionAdjustRequest $request
     * @param int $employee_id
     * @return JsonResponse
     * @group Admin.Employees
     * @authenticated
     */
    public function removePermissions(PermissionAdjustRequest $request, int $employee_id)
    {
        return $this->handlePermissionOperation($request, $employee_id, 'remove');
    }

    /**
     * Sync permissions for an employee
     *
     * @param PermissionAdjustRequest $request
     * @param int $employee_id
     * @return JsonResponse
     * @group Admin.Employees
     * @authenticated
     */
    public function syncPermissions(PermissionAdjustRequest $request, int $employee_id)
    {
        return $this->handlePermissionOperation($request, $employee_id, 'sync');
    }

    /**
     * Add roles to an employee
     *
     * @param RoleAdjustRequest $request
     * @param int $employee_id
     * @return JsonResponse
     * @group Admin.Employees
     * @authenticated
     */
    public function addRole(RoleAdjustRequest $request, int $employee_id)
    {
        return $this->handleRoleOperation($request, $employee_id, 'add');
    }

    /**
     * Remove roles from an employee
     *
     * @param RoleAdjustRequest $request
     * @param int $employee_id
     * @return JsonResponse
     * @group Admin.Employees
     * @authenticated
     */
    public function removeRole(RoleAdjustRequest $request, int $employee_id)
    {
        return $this->handleRoleOperation($request, $employee_id, 'remove');
    }

    /**
     * Sync roles for an employee
     *
     * @param RoleAdjustRequest $request
     * @param int $employee_id
     * @return JsonResponse
     * @group Admin.Employees
     * @authenticated
     */
    public function syncRoles(RoleAdjustRequest $request, int $employee_id)
    {
        return $this->handleRoleOperation($request, $employee_id, 'sync');
    }
}
