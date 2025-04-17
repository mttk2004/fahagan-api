<?php

namespace Tests\Feature\Api\V1\Address;

use App\DTOs\Address\AddressDTO;
use PHPUnit\Framework\TestCase;

class AddressDTOTest extends TestCase
{
  public function test_it_creates_address_dto_from_direct_format_request()
  {
    $requestData = [
      'name' => 'Nguyễn Văn A',
      'phone' => '0123456789',
      'city' => 'Hà Nội',
      'district' => 'Cầu Giấy',
      'ward' => 'Dịch Vọng',
      'address_line' => 'Số 1 Đường ABC',
    ];

    $addressDTO = AddressDTO::fromRequest($requestData);

    $this->assertEquals($requestData['name'], $addressDTO->name);
    $this->assertEquals($requestData['phone'], $addressDTO->phone);
    $this->assertEquals($requestData['city'], $addressDTO->city);
    $this->assertEquals($requestData['district'], $addressDTO->district);
    $this->assertEquals($requestData['ward'], $addressDTO->ward);
    $this->assertEquals($requestData['address_line'], $addressDTO->address_line);
  }

  public function test_it_creates_address_dto_with_nullable_properties()
  {
    $requestData = [
      'name' => 'Nguyễn Văn A',
    ];

    $addressDTO = AddressDTO::fromRequest($requestData);

    $this->assertEquals($requestData['name'], $addressDTO->name);
    $this->assertNull($addressDTO->phone);
    $this->assertNull($addressDTO->city);
    $this->assertNull($addressDTO->district);
    $this->assertNull($addressDTO->ward);
    $this->assertNull($addressDTO->address_line);
  }

  public function test_it_handles_empty_request_data()
  {
    $requestData = [];

    $addressDTO = AddressDTO::fromRequest($requestData);

    $this->assertNull($addressDTO->name);
    $this->assertNull($addressDTO->phone);
    $this->assertNull($addressDTO->city);
    $this->assertNull($addressDTO->district);
    $this->assertNull($addressDTO->ward);
    $this->assertNull($addressDTO->address_line);
  }

  public function test_it_converts_to_array()
  {
    $addressDTO = new AddressDTO(
      name: 'Nguyễn Văn A',
      phone: '0123456789',
      city: 'Hà Nội',
      district: 'Cầu Giấy',
      ward: 'Dịch Vọng',
      address_line: 'Số 1 Đường ABC'
    );

    $array = $addressDTO->toArray();

    $this->assertIsArray($array);
    $this->assertEquals('Nguyễn Văn A', $array['name']);
    $this->assertEquals('0123456789', $array['phone']);
    $this->assertEquals('Hà Nội', $array['city']);
    $this->assertEquals('Cầu Giấy', $array['district']);
    $this->assertEquals('Dịch Vọng', $array['ward']);
    $this->assertEquals('Số 1 Đường ABC', $array['address_line']);
  }

  public function test_it_omits_null_properties_in_array()
  {
    $addressDTO = new AddressDTO(
      name: 'Nguyễn Văn A',
      phone: null,
      city: null,
      district: null,
      ward: null,
      address_line: null
    );

    $array = $addressDTO->toArray();

    $this->assertIsArray($array);
    $this->assertArrayHasKey('name', $array);
    $this->assertArrayNotHasKey('phone', $array);
    $this->assertArrayNotHasKey('city', $array);
    $this->assertArrayNotHasKey('district', $array);
    $this->assertArrayNotHasKey('ward', $array);
    $this->assertArrayNotHasKey('address_line', $array);
  }

  public function test_it_can_create_dto_with_partial_data()
  {
    $addressDTO = new AddressDTO(
      name: 'Nguyễn Văn A',
      city: 'Hà Nội',
      ward: 'Dịch Vọng'
    );

    $this->assertEquals('Nguyễn Văn A', $addressDTO->name);
    $this->assertNull($addressDTO->phone);
    $this->assertEquals('Hà Nội', $addressDTO->city);
    $this->assertNull($addressDTO->district);
    $this->assertEquals('Dịch Vọng', $addressDTO->ward);
    $this->assertNull($addressDTO->address_line);

    $array = $addressDTO->toArray();
    $this->assertCount(3, $array);
  }
}
