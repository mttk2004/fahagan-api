<?php

namespace App\Services;

use App\Constants\ApplicationConstants;
use App\DTOs\UserDTO;
use App\Http\Filters\V1\UserFilter;
use App\Http\Sorts\V1\UserSort;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class EmployeeService extends BaseService
{
    /**
     * EmployeeService constructor.
     */
    public function __construct()
    {
        $this->model = new User;
        $this->filterClass = UserFilter::class;
        $this->sortClass = UserSort::class;
        $this->with = [];
    }

    /**
     * Lấy danh sách nhân viên với filter và sort
     */
    public function getAllEmployees(Request $request, int $perPage = ApplicationConstants::PER_PAGE): LengthAwarePaginator
    {
        $query = $this->model::where('is_customer', 0);

        if ($this->filterClass && class_exists($this->filterClass)) {
            $filter = new $this->filterClass($request);
            $query = $filter->apply($query);
        }

        if ($this->sortClass && class_exists($this->sortClass)) {
            $sort = new $this->sortClass($request);
            $query = $sort->apply($query);
        }

        // Eager load relations
        if (! empty($this->with)) {
            $query->with($this->with);
        }

        // Paginate results
        return $query->paginate($perPage);
    }

    /**
     * Tạo nhân viên mới
     *
     * @throws ValidationException
     * @throws Exception
     */
    public function createEmployee(UserDTO $userDTO): User
    {
        return $this->create($userDTO);
    }

    /**
     * Lấy thông tin chi tiết nhân viên
     *
     * @throws ModelNotFoundException
     */
    public function getEmployeeById(string|int $userId): User
    {
        $employee = $this->getById($userId);

        if ($employee->is_customer) {
            throw new ModelNotFoundException();
        }

        return $employee;
    }
}
