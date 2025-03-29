<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ResponseMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\AddressStoreRequest;
use App\Http\Requests\V1\AddressUpdateRequest;
use App\Http\Resources\V1\AddressCollection;
use App\Traits\HandlePagination;
use App\Utils\AuthUtils;
use App\Utils\ResponseUtils;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AddressController extends Controller
{
    use HandlePagination;

    /*
     * Show all addresses of the authenticated user.
     *
     * @return AddressCollection
     * @group Address
     */
    public function index()
    {
        $addresses = AuthUtils::user()
                              ->addresses()
                              ->paginate($this->getPerPage(request()));

        return new AddressCollection($addresses);
    }

    /*
     * Store a new address for the authenticated user.
     *
     * @param AddressStoreRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @group Address
     */
    public function store(AddressStoreRequest $request)
    {
        $address = AuthUtils::user()->addresses()->create($request->validated());

        return ResponseUtils::created([
            'address' => $address,
        ], ResponseMessage::CREATED_ADDRESS->value);
    }

    /*
     * Update an address of the authenticated user.
     *
     * @param AddressUpdateRequest $request
     * @param $address_id
     * @return \Illuminate\Http\JsonResponse
     * @group Address
     */
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

    /*
     * Delete an address of the authenticated user.
     *
     * @param $address_id
     * @return \Illuminate\Http\JsonResponse
     * @group Address
     */
    public function destroy($address_id)
    {
        try {
            $address = AuthUtils::user()->addresses()->findOrFail($address_id);
            $address->delete();

            return ResponseUtils::noContent(ResponseMessage::DELETED_ADDRESS->value);
        } catch (ModelNotFoundException) {
            return ResponseUtils::notFound(ResponseMessage::NOT_FOUND_ADDRESS->value);
        }
    }
}
