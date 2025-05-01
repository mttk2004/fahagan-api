<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\Address\AddressDTO;
use App\Enums\ResponseMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\AddressStoreRequest;
use App\Http\Requests\V1\AddressUpdateRequest;
use App\Http\Resources\V1\AddressCollection;
use App\Http\Resources\V1\AddressResource;
use App\Services\AddressService;
use App\Traits\HandleExceptions;
use App\Traits\HandlePagination;
use App\Traits\HandleValidation;
use App\Utils\AuthUtils;
use App\Utils\ResponseUtils;
use Exception;
use Illuminate\Http\JsonResponse;
use Throwable;

class CustomerAddressController extends Controller
{
  use HandlePagination;
  use HandleExceptions;
  use HandleValidation;

  public function __construct(
    private readonly AddressService $addressService,
    private readonly string $entityName = 'address'
  ) {}

  /**
   * Show all addresses of the authenticated user.
   *
   * @return AddressCollection|JsonResponse
   * @group Customer.Address
   * @authenticated
   */
  public function index()
  {
    if (! AuthUtils::check()) {
      return ResponseUtils::unauthorized();
    }

    $addresses = $this->addressService->getAllAddresses(
      AuthUtils::user(),
      request(),
      $this->getPerPage(request())
    );

    return new AddressCollection($addresses);
  }

  /**
   * Store a new address for the authenticated user.
   *
   * @param AddressStoreRequest $request
   *
   * @return JsonResponse
   * @group Customer.Address
   * @authenticated
   * @throws Throwable
   */
  public function store(AddressStoreRequest $request)
  {
    if (! AuthUtils::check()) {
      return ResponseUtils::unauthorized();
    }

    try {

      $addressDTO = AddressDTO::fromRequest($request->validated());
      $address = $this->addressService->createAddress(AuthUtils::user(), $addressDTO);

      return ResponseUtils::created([
        'address' => new AddressResource($address),
      ], ResponseMessage::CREATED_ADDRESS->value);
    } catch (Exception $e) {
      return $this->handleException($e, $this->entityName, [
        'request_data' => $request->validated(),
      ]);
    }
  }

  /**
   * Update an address of the authenticated user.
   *
   * @param AddressUpdateRequest $request
   * @param                      $address_id
   *
   * @return JsonResponse
   * @group Customer.Address
   * @authenticated
   * @throws Throwable
   */
  public function update(AddressUpdateRequest $request, $address_id)
  {
    if (! AuthUtils::check()) {
      return ResponseUtils::unauthorized();
    }

    try {
      $validatedData = $request->validated();

      $emptyCheckResponse = $this->validateUpdateData($validatedData);
      if ($emptyCheckResponse) {
        return $emptyCheckResponse;
      }

      $address = $this->addressService->updateAddress(
        AuthUtils::user(),
        $address_id,
        AddressDTO::fromRequest($validatedData)
      );

      return ResponseUtils::success([
        'address' => new AddressResource($address),
      ], ResponseMessage::UPDATED_ADDRESS->value);
    } catch (Exception $e) {
      return $this->handleException($e, $this->entityName, [
        'request_data' => $request->validated(),
      ]);
    }
  }

  /**
   * Delete an address of the authenticated user.
   *
   * @param $address_id
   *
   * @return JsonResponse
   * @group Customer.Address
   * @authenticated
   * @throws Throwable
   */
  public function destroy($address_id)
  {
    if (! AuthUtils::check()) {
      return ResponseUtils::unauthorized();
    }

    try {
      $this->addressService->deleteAddress(AuthUtils::user(), $address_id);

      return ResponseUtils::noContent(ResponseMessage::DELETED_ADDRESS->value);
    } catch (Exception $e) {
      return $this->handleException($e, $this->entityName, [
        'request_data' => [
          'address_id' => $address_id,
        ],
      ]);
    }
  }
}
