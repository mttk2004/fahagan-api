<?php

namespace App\Http\Controllers\Api\V1;


use App\Enums\ResponseMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\AddressStoreRequest;
use App\Http\Resources\V1\AddressCollection;
use App\Models\Address;
use App\Utils\AuthUtils;
use App\Utils\ResponseUtils;
use Illuminate\Http\Request;


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
		$addressData = $request->validated();
		$address = AuthUtils::user()->addresses()->create($addressData);

		return ResponseUtils::created([
			'address' => $address,
		], ResponseMessage::CREATED_ADDRESS->value);
	}

	public function update(Request $request, Address $address) {}

	public function destroy(Address $address) {}
}
