<?php

namespace App\Services;

use App\DTOs\Discount\DiscountDTO;
use App\Models\Discount;
use App\Models\DiscountTarget;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DiscountService
{
    /**
     * Lấy danh sách mã giảm giá
     */
    public function getAllDiscounts(Request $request, int $perPage = 15): LengthAwarePaginator
    {
        $query = Discount::query();

        // Thêm logic filter và sorting ở đây nếu cần

        // Paginate
        return $query->paginate($perPage);
    }

    /**
     * Tạo mã giảm giá mới hoặc khôi phục mã giảm giá đã bị xóa mềm nếu đã tồn tại với cùng tên
     *
     * @throws ValidationException
     * @throws Exception
     */
    public function createDiscount(DiscountDTO $discountDTO): Discount
    {
        $data = $discountDTO->toArray();

        // Kiểm tra xem có đủ thông tin cần thiết hay không
        if (! isset($data['name'])) {
            throw ValidationException::withMessages([
                'data.attributes.name' => ['Tên mã giảm giá là bắt buộc.'],
            ]);
        }

        // Kiểm tra xem có mã giảm giá nào (chưa bị xóa) với cùng tên hay không
        $existingDiscount = Discount::where('name', $data['name'])->first();

        if ($existingDiscount) {
            throw ValidationException::withMessages([
                'data.attributes.name' => ['Đã tồn tại mã giảm giá với tên này. Vui lòng sử dụng tên khác.'],
            ]);
        }

        // Kiểm tra xem có mã giảm giá nào đã bị xóa mềm với cùng tên
        $deletedDiscount = Discount::withTrashed()
            ->where('name', $data['name'])
            ->onlyTrashed() // Chỉ lấy các mã giảm giá đã bị xóa
            ->first();

        // Nếu tồn tại, khôi phục và cập nhật
        if ($deletedDiscount) {
            try {
                DB::beginTransaction();

                // Khôi phục mã giảm giá
                $deletedDiscount->restore();

                // Cập nhật thông tin mới
                $deletedDiscount->update($data);

                // Cập nhật targets nếu có
                if (! empty($discountDTO->target_ids)) {
                    $this->syncTargets($deletedDiscount, $discountDTO->target_ids);
                }

                DB::commit();

                return $deletedDiscount->fresh(['targets']);
            } catch (Exception $e) {
                DB::rollBack();

                throw $e;
            }
        }

        // Tạo mã giảm giá mới nếu không tìm thấy mã giảm giá đã xóa với cùng tên
        try {
            DB::beginTransaction();

            // Tạo mã giảm giá
            $discount = Discount::create($data);

            // Gán targets
            if (! empty($discountDTO->target_ids)) {
                $this->syncTargets($discount, $discountDTO->target_ids);
            }

            DB::commit();

            return $discount->fresh(['targets']);
        } catch (QueryException $e) {
            DB::rollBack();

            // Nếu là lỗi ràng buộc duy nhất, chuyển nó thành ValidationException
            if ($e->getCode() == 23000 && strpos($e->getMessage(), 'discounts_name_unique') !== false) {
                throw ValidationException::withMessages([
                    'data.attributes.name' => ['Đã tồn tại mã giảm giá với tên này. Vui lòng sử dụng tên khác.'],
                ]);
            }

            throw $e;
        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }

    /**
     * Lấy thông tin chi tiết mã giảm giá
     *
     * @throws ModelNotFoundException
     */
    public function getDiscountById(string|int $discountId): Discount
    {
        return Discount::with(['targets'])->findOrFail($discountId);
    }

    /**
     * Cập nhật mã giảm giá
     *
     * @throws ModelNotFoundException
     * @throws ValidationException
     * @throws Exception
     */
    public function updateDiscount(string|int $discountId, DiscountDTO $discountDTO, array $originalRequest = []): Discount
    {
        try {
            // Tìm mã giảm giá hiện tại
            $discount = Discount::findOrFail($discountId);

            // Lấy dữ liệu cập nhật từ DTO
            $data = $discountDTO->toArray();

            // Kiểm tra xem có tên mã giảm giá mới, và nếu có, đảm bảo rằng nó là duy nhất
            if (isset($data['name']) && $data['name'] !== $discount->name) {
                $existingDiscount = Discount::where('name', $data['name'])
                    ->where('id', '!=', $discountId)
                    ->first();

                if ($existingDiscount) {
                    throw ValidationException::withMessages([
                        'data.attributes.name' => ['Đã tồn tại mã giảm giá với tên này. Vui lòng sử dụng tên khác.'],
                    ]);
                }
            }

            // Chỉ cập nhật khi có dữ liệu
            if (! empty($data)) {
                DB::beginTransaction();

                // Cập nhật mã giảm giá
                $discount->update($data);

                // Kiểm tra xem relationships targets có trong request gốc hay không
                $hasTargetsInRequest = isset($originalRequest['data']['relationships']['targets']);

                // Chỉ đồng bộ targets khi relationships targets có trong request
                if ($hasTargetsInRequest) {
                    $this->syncTargets($discount, $discountDTO->target_ids);
                }

                DB::commit();

                // Trả về mã giảm giá đã được cập nhật với targets
                return $discount->fresh(['targets']);
            }

            return $discount;
        } catch (ModelNotFoundException $e) {
            throw $e;
        } catch (ValidationException $e) {
            throw $e;
        } catch (Exception $e) {
            if (isset($discount) && DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            throw $e;
        }
    }

    /**
     * Xóa mã giảm giá
     *
     * @throws ModelNotFoundException
     */
    public function deleteDiscount(string|int $discountId): Discount
    {
        $discount = Discount::findOrFail($discountId);
        $discount->delete();

        return $discount;
    }

    /**
     * Đồng bộ targets với mã giảm giá
     */
    private function syncTargets(Discount $discount, array $targetIds): void
    {
        // Xóa targets hiện tại
        DiscountTarget::where('discount_id', $discount->id)->delete();

        // Thêm targets mới
        foreach ($targetIds as $targetId) {
            DiscountTarget::create([
                'discount_id' => $discount->id,
                'target_id' => $targetId,
                'target_type' => 'book', // Mặc định là book, cần cập nhật sau với giá trị thực tế
            ]);
        }
    }
}
