<?php

namespace App\Services;

use App\Constants\ApplicationConstants;
use App\DTOs\BaseDTO;
use App\DTOs\UserDTO;
use App\Filters\UserFilter;
use App\Http\Sorts\V1\UserSort;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;


class UserService extends BaseService
{
    /**
     * UserService constructor.
     */
    public function __construct()
    {
        $this->model = new User;
        $this->filterClass = UserFilter::class;
        $this->sortClass = UserSort::class;
        $this->with = [];
    }

    /**
     * Lấy danh sách người dùng với filter và sort
     */
    public function getAllUsers(Request $request, int $perPage = ApplicationConstants::PER_PAGE): LengthAwarePaginator
    {
        return $this->getAll($request, $perPage);
    }

    /**
     * Tạo người dùng mới
     *
     * @throws ValidationException
     * @throws Exception
     */
    public function createUser(UserDTO $userDTO): User
    {
        return $this->create($userDTO);
    }

    /**
     * Lấy thông tin chi tiết người dùng
     *
     * @throws ModelNotFoundException
     */
    public function getUserById(string|int $userId): User
    {
        return $this->getById($userId);
    }

    /**
     * Cập nhật người dùng
     *
     * @throws ModelNotFoundException
     * @throws ValidationException
     * @throws Exception
     */
    public function updateUser(string|int $userId, UserDTO $userDTO): User
    {
        return $this->update($userId, $userDTO);
    }

    /**
     * Xóa người dùng
     *
     * @throws ModelNotFoundException
     * @throws Exception
     */
    public function deleteUser(string|int $userId): void
    {
        $this->delete($userId);
    }

    /**
     * Find a trashed resource based on unique attributes
     */
    protected function findTrashed(BaseDTO $dto): ?\Illuminate\Database\Eloquent\Model
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
