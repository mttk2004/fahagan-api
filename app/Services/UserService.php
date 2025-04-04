<?php

namespace App\Services;

use App\DTOs\User\UserDTO;
use App\Filters\UserFilter;
use App\Http\Sorts\V1\UserSort;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UserService
{
    /**
     * Lấy danh sách người dùng với filter và sort
     */
    public function getAllUsers(Request $request, int $perPage = 15): LengthAwarePaginator
    {
        $query = User::query();

        // Apply filters
        $userFilter = new UserFilter($request);
        $query = $userFilter->apply($query);

        // Apply sorting
        $userSort = new UserSort($request);
        $query = $userSort->apply($query);

        // Paginate
        return $query->paginate($perPage);
    }

    /**
     * Tạo người dùng mới
     *
     * @throws ValidationException
     * @throws Exception
     */
    public function createUser(UserDTO $userDTO): User
    {
        try {
            DB::beginTransaction();

            // Tạo người dùng
            $user = User::create($userDTO->toArray());

            DB::commit();

            return $user;
        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }

    /**
     * Lấy thông tin chi tiết người dùng
     *
     * @throws ModelNotFoundException
     */
    public function getUserById(string|int $userId): User
    {
        return User::findOrFail($userId);
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
        try {
            // Tìm người dùng hiện tại
            $user = User::findOrFail($userId);

            DB::beginTransaction();

            // Cập nhật thông tin người dùng
            $user->update($userDTO->toArray());

            DB::commit();

            return $user->fresh();
        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }

    /**
     * Xóa người dùng
     *
     * @throws ModelNotFoundException
     * @throws Exception
     */
    public function deleteUser(string|int $userId): void
    {
        try {
            $user = User::findOrFail($userId);

            DB::beginTransaction();

            // Xóa người dùng
            $user->delete();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }
}
