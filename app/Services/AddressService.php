<?php

namespace App\Services;

use App\DTOs\Address\AddressDTO;
use App\Models\Address;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AddressService
{
    /**
     * Lấy danh sách địa chỉ của người dùng
     */
    public function getAllAddresses(User $user, Request $request, int $perPage = 15): LengthAwarePaginator
    {
        return $user->addresses()->paginate($perPage);
    }

    /**
     * Tạo địa chỉ mới cho người dùng
     *
     * @throws Exception
     */
    public function createAddress(User $user, AddressDTO $addressDTO): Address
    {
        $data = $addressDTO->toArray();

        try {
            DB::beginTransaction();

            // Tạo địa chỉ mới
            $address = $user->addresses()->create($data);

            DB::commit();

            return $address->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Lấy thông tin chi tiết của địa chỉ
     *
     * @throws ModelNotFoundException
     */
    public function getAddressById(User $user, string|int $addressId): Address
    {
        return $user->addresses()->findOrFail($addressId);
    }

    /**
     * Cập nhật địa chỉ
     *
     * @throws ModelNotFoundException
     * @throws Exception
     */
    public function updateAddress(User $user, string|int $addressId, AddressDTO $addressDTO): Address
    {
        $data = $addressDTO->toArray();

        try {
            DB::beginTransaction();

            // Tìm địa chỉ
            $address = $user->addresses()->findOrFail($addressId);

            // Cập nhật địa chỉ
            $address->update($data);

            DB::commit();

            return $address->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Xóa địa chỉ
     *
     * @throws ModelNotFoundException
     * @throws Exception
     */
    public function deleteAddress(User $user, string|int $addressId): Address
    {
        try {
            DB::beginTransaction();

            // Tìm địa chỉ
            $address = $user->addresses()->findOrFail($addressId);

            // Lưu thông tin địa chỉ trước khi xóa
            $deletedAddress = clone $address;

            // Xóa địa chỉ
            $address->delete();

            DB::commit();

            return $deletedAddress;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
