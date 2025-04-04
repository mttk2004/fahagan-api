<?php

namespace App\Http\Requests\V1;

use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Traits\HasRequestFormat;
use App\Utils\AuthUtils;
use Illuminate\Http\Request;

class UserUpdateRequest extends BaseRequest implements HasValidationMessages
{
  use HasRequestFormat;

  protected function prepareForValidation(): void
  {
    $this->convertToJsonApiFormat([
      'first_name',
      'last_name',
      'phone',
      'email',
    ]);
  }
  public function rules(): array
  {
      return [
          'data.attributes.first_name' => ['sometimes', 'string', 'max:30'],
          'data.attributes.last_name' => ['sometimes', 'string', 'max:30'],
          'data.attributes.phone' => [
              'sometimes',
              'string',
              'regex:/^0[35789][0-9]{8}$/',
              'unique:users,phone',
          ],
          'data.attributes.email' => [
              'sometimes',
              'string',
              'lowercase',
              'email',
              'max:50',
              'unique:users,email',
          ],
      ];
  }

  public function messages(): array
  {
      return [
          'data.attributes.first_name.string' => 'Tên nên là một chuỗi.',
          'data.attributes.first_name.max' => 'Tên nên có độ dài tối đa 30.',

          'data.attributes.last_name.string' => 'Họ nên là một chuỗi.',
          'data.attributes.last_name.max' => 'Họ nên có độ dài tối đa 30.',

          'data.attributes.phone.string' => 'Số điện thoại nên là một chuỗi.',
          'data.attributes.phone.regex' => 'Số điện thoại không hợp lệ.',
          'data.attributes.phone.unique' => 'Số điện thoại đã được sử dụng.',

          'data.attributes.email.string' => 'Email nên là một chuỗi.',
          'data.attributes.email.lowercase' => 'Email nên viết thường.',
          'data.attributes.email.email' => 'Email không hợp lệ.',
          'data.attributes.email.max' => 'Email nên có độ dài tối đa 50.',
          'data.attributes.email.unique' => 'Email đã được sử dụng.',
      ];
  }

  public function authorize(Request $request): bool
  {
      $user = AuthUtils::user();

      return AuthUtils::userCan('edit_users')
          || $user->id == $request->route('user');
  }
}
