<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\UserCollection;
use App\Http\Resources\V1\UserResource;
use App\Services\EmployeeService;
use App\Traits\HandleExceptions;
use App\Traits\HandlePagination;
use App\Traits\HandleValidation;
use App\Utils\AuthUtils;
use App\Utils\ResponseUtils;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    use HandleExceptions;
    use HandlePagination;
    use HandleValidation;

    public function __construct(
        private readonly EmployeeService $employeeService,
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
        $users = $this->employeeService->getAllEmployees($request, $this->getPerPage($request));

        return new UserCollection($users);
    }

    /**
     * Get a employee
     *
     * @param int $employee_id
     * @return JsonResponse
     * @group Admin.Employees
     * @authenticated
     */
    public function show($employee_id)
    {
        try {
            $employee = $this->employeeService->getEmployeeById($employee_id);

            return ResponseUtils::success([
              'employee' => new UserResource($employee),
            ]);
        } catch (Exception $e) {
            return $this->handleException(
                $e,
                $this->entityName,
                [
                'employee_id' => $employee_id,
        ]
            );
        }
    }

    public function updatePermissions(Request $request, $employee_id)
    {
        if (! AuthUtils::userCan('update_user_permissions')) {
            return ResponseUtils::forbidden();
        }
    }
}
