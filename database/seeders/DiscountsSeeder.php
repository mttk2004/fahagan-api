<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Book;
use App\Models\Discount;
use Illuminate\Database\Seeder;

class DiscountsSeeder extends Seeder
{
  public function run(): void
  {
    $discounts = [
      [
        'name' => 'Giảm 10% cho sách của một tác giả ngẫu nhiên',
        'discount_type' => 'percentage',
        'discount_value' => 10,
        'target_type' => 'book',
        'start_date' => now(),
        'end_date' => now()->addDays(15),
        'description' => 'Giảm 10% cho sách của một tác giả ngẫu nhiên',
      ],
      [
        'name' => 'Giảm 20% cho tất cả các đơn hàng',
        'discount_type' => 'percentage',
        'discount_value' => 20,
        'target_type' => 'order',
        'start_date' => now(),
        'end_date' => now()->addDays(10),
        'description' => 'Giảm 20% cho tất cả các đơn hàng',
      ],
    ];

    foreach ($discounts as $discount) {
      $discount = Discount::create($discount);

      // Lấy tất cả sách của môt tác giả ngẫu nhiên
      if ($discount->target_type === 'book') {
        $author = Author::inRandomOrder()->first();

        $bookIds = Book::whereHas('authors', function ($query) use ($author) {
          $query->where('author_id', $author->id);
        })->pluck('id')->toArray();

        // Nếu không có sách nào của tác giả đó thì bỏ qua
        if (empty($bookIds)) {
          continue;
        }

        // Gán discount cho tất cả sách của tác giả đó
        $discount->books()->sync($bookIds);
      }
    }
  }
}
