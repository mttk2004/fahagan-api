<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;


class RolesSeeder extends Seeder
{
	public function run(): void
	{
		// Tạo roles
		$admin = Role::create(['name' => 'admin']);
		$warehouseStaff = Role::create(['name' => 'warehouse_staff']);
		$orderApprover = Role::create(['name' => 'order_approver']);

		// Lấy danh sách quyền
		$permissions = Permission::all();

		// Gán tất cả quyền cho admin
		$admin->syncPermissions($permissions);

		// Nhân viên kho chỉ có quyền quản lý sản phẩm
		$warehouseStaff->syncPermissions(['view_orders']);

		// Nhân viên duyệt đơn có quyền duyệt đơn hàng
		$orderApprover->syncPermissions(['view_orders', 'approve_orders']);
	}
}
