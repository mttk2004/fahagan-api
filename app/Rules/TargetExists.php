<?php

namespace App\Rules;

use App\Models\Author;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Publisher;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class TargetExists implements ValidationRule
{
    /**
     * Xác định xem giá trị đã cho có hợp lệ hay không.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure  $fail
     * @return void|bool
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Chỉ xử lý nếu đây là ID của target
        if (!preg_match('/^data\.relationships\.targets\.(\d+)\.id$/', $attribute, $matches)) {
            return;
        }

        $index = $matches[1];
        $typeAttribute = "data.relationships.targets.{$index}.type";
        $request = request();

        // Lấy kiểu target
        $type = $request->input($typeAttribute);
        if (!$type) {
            return;
        }

        $exists = false;

        // Kiểm tra tồn tại dựa trên loại target
        switch ($type) {
            case 'book':
                $exists = Book::where('id', $value)->exists();
                break;
            case 'genre':
                $exists = Genre::where('id', $value)->exists();
                break;
            case 'author':
                $exists = Author::where('id', $value)->exists();
                break;
            case 'publisher':
                $exists = Publisher::where('id', $value)->exists();
                break;
        }

        if (!$exists) {
            $fail("Không tìm thấy {$type} với ID là {$value}.");
        }
    }
}
