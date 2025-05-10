<?php

namespace App\Services;

use App\Constants\ApplicationConstants;
use App\DTOs\BaseDTO;
use App\DTOs\UserDTO;
use App\Http\Filters\V1\UserFilter;
use App\Http\Sorts\V1\UserSort;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Throwable;

class UserService extends BaseService
{
    /**
     * CustomerService constructor.
     */
    public function __construct()
    {
        $this->model = new User;
        $this->filterClass = UserFilter::class;
        $this->sortClass = UserSort::class;
        $this->with = [];
    }

    /**
     * Lấy danh sách user với filter và sort
     *
     * @param Request $request
     * @param int     $perPage
     * @param bool $is_customer
     * @return LengthAwarePaginator
     */
    public function getAllUsers(Request $request, int $perPage =
    ApplicationConstants::PER_PAGE, bool $is_customer = true): LengthAwarePaginator
    {
        $query = $this->model::where('is_customer', $is_customer);

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
     * Lấy thông tin chi tiết user
     *
     * @throws ModelNotFoundException
     */
    public function getUserById(string|int $userId): Model
    {
        return $this->getById($userId);
    }

    /**
     * Xóa người dùng
     *
     * @throws ModelNotFoundException
     * @throws Exception
     * @throws Throwable
     */
    public function deleteUser(string|int $userId): void
    {
        $this->delete($userId);
    }

    /**
     * Find a trashed resource based on unique attributes
     */
    protected function findTrashed(BaseDTO $dto): ?Model
    {
        // Đảm bảo DTO là kiểu UserDTO trước khi tiếp tục
        if (! ($dto instanceof UserDTO) || ! isset($dto->email)) {
            return null;
        }

        return User::withTrashed()
                   ->where('email', $dto->email)
                   ->onlyTrashed()
                   ->first();
    }
}
