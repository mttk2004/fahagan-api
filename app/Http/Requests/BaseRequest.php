<?php

namespace App\Http\Requests;


use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;


class BaseRequest extends FormRequest
{
	public function failedAuthorization()
	{
		throw new AuthorizationException('Bạn không có quyền thực hiện hành động này.');
	}
}
