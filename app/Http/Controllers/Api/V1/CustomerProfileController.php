<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class CustomerProfileController extends Controller
{
  /**
   * Hiển thị thông tin profile của khách hàng đã đăng nhập
   *
   * @return \Illuminate\Http\Response
   */
  public function show()
  {
    $user = Auth::user();
    return response()->json([
      'data' => $user,
      'message' => 'Lấy thông tin profile thành công'
    ]);
  }

  /**
   * Cập nhật thông tin profile của khách hàng đã đăng nhập
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function update(Request $request)
  {
    $user = Auth::user();

    $validated = $request->validate([
      'name' => 'sometimes|string|max:255',
      'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
      'phone' => 'sometimes|string|max:20',
      // Thêm các trường khác nếu cần
    ]);

    $user->update($validated);

    return response()->json([
      'data' => $user,
      'message' => 'Cập nhật profile thành công'
    ]);
  }
}
