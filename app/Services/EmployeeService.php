<?php

namespace App\Services;

use App\Http\Filters\V1\UserFilter;
use App\Http\Requests\V1\EmployeeStoreRequest;
use App\Http\Sorts\V1\UserSort;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Hash;
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
     * Tạo nhân viên mới
     *
     * @throws ValidationException
     * @throws Exception
     */
    public function createEmployee(EmployeeStoreRequest $request): User
    {
        $user = User::create([
          'first_name' => $request->first_name,
          'last_name' => $request->last_name,
          'phone' => $request->phone,
          'email' => $request->email,
          'password' => Hash::make($request->password),
          'is_customer' => false,
        ]);

        $user->assignRole($request->role);

        return $user;
    }
}
