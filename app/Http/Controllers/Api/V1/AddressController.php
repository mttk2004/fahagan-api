<?php

namespace App\Http\Controllers\Api\V1;


use App\Enums\ResponseMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\AddressStoreRequest;
use App\Http\Requests\V1\AddressUpdateRequest;
use App\Http\Resources\V1\AddressCollection;
use App\Models\Address;
use App\Utils\AuthUtils;
use App\Utils\ResponseUtils;
use Illuminate\Database\Eloquent\ModelNotFoundException;


class AddressController extends Controller
{
	public function index()
	{
		return ResponseUtils::success([
			'addresses' => new AddressCollection(AuthUtils::user()->addresses),
		]);
	}

	public function store(AddressStoreRequest $request)
	{
		$address = AuthUtils::user()->addresses()->create($request->validated());

		return ResponseUtils::created([
			'address' => $address,
		], ResponseMessage::CREATED_ADDRESS->value);
	}

	public function update(AddressUpdateRequest $request, $address_id)
	{
		try {
			$address = AuthUtils::user()->addresses()->findOrFail($address_id);
			$address->update($request->validated());

			return ResponseUtils::success([
				'address' => $address,
			], ResponseMessage::UPDATED_ADDRESS->value);
		} catch (ModelNotFoundException) {
			return ResponseUtils::notFound(ResponseMessage::NOT_FOUND_ADDRESS->value);
		}
	}

	public function destroy(Address $address) {}
}
