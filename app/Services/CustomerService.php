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
     * Cập nhật người dùng
     *
     * @throws ModelNotFoundException
     * @throws ValidationException
     * @throws Exception
     * @throws Throwable
     */
    public function updateUser(string|int $userId, UserDTO $userDTO): Model
    {
        return $this->update($userId, $userDTO);
    }
}
