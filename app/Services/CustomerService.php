<?php

namespace App\Services;

use App\DTOs\UserDTO;
use App\Http\Filters\V1\UserFilter;
use App\Http\Sorts\V1\UserSort;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Throwable;

class CustomerService extends BaseService
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
     * Tạo khách hàng
     *
     * @param UserDTO $userDTO
     * @return User
     * @throws ValidationException
     * @throws Exception
     */
    public function createCustomer(UserDTO $userDTO): User
    {
        return $this->create($userDTO);
    }

    /**
     * Cập nhật khách hàng
     *
     * @throws ModelNotFoundException
     * @throws ValidationException
     * @throws Exception
     * @throws Throwable
     */
    public function updateCustomer(string|int $userId, UserDTO $userDTO): Model
    {
        return $this->update($userId, $userDTO);
    }
}
