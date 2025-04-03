<?php

namespace Database\Seeders;

use App\Models\Publisher;
use Illuminate\Database\Seeder;

class PublisherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo một vài nhà xuất bản cơ bản cho test
        Publisher::create([
            'name' => 'NXB Kim Đồng',
            'biography' => 'Nhà xuất bản sách thiếu nhi hàng đầu Việt Nam',
        ]);

        Publisher::create([
            'name' => 'NXB Trẻ',
            'biography' => 'Nhà xuất bản Trẻ là một trong những nhà xuất bản lớn tại Việt Nam',
        ]);

        Publisher::create([
            'name' => 'NXB Tổng hợp',
            'biography' => 'Nhà xuất bản Tổng hợp thành phố Hồ Chí Minh',
        ]);
    }
}
