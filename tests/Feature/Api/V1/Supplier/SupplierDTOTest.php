<?php

namespace Tests\Feature\Api\V1\Supplier;

use App\DTOs\SupplierDTO;
use PHPUnit\Framework\TestCase;

class SupplierDTOTest extends TestCase
{
    public function test_it_creates_supplier_dto_from_request()
    {
        $requestData = [
            'data' => [
                'attributes' => [
                    'name' => 'Nhà Sách Test',
                    'phone' => '0123456789',
                    'email' => 'test@example.com',
                    'city' => 'Hà Nội',
                    'district' => 'Cầu Giấy',
                    'ward' => 'Dịch Vọng',
                    'address_line' => 'Số 1 Đường ABC',
                ],
            ],
        ];

        $supplierDTO = SupplierDTO::fromRequest($requestData);

        $this->assertEquals($requestData['data']['attributes']['name'], $supplierDTO->name);
        $this->assertEquals($requestData['data']['attributes']['phone'], $supplierDTO->phone);
        $this->assertEquals($requestData['data']['attributes']['email'], $supplierDTO->email);
        $this->assertEquals($requestData['data']['attributes']['city'], $supplierDTO->city);
        $this->assertEquals($requestData['data']['attributes']['district'], $supplierDTO->district);
        $this->assertEquals($requestData['data']['attributes']['ward'], $supplierDTO->ward);
        $this->assertEquals($requestData['data']['attributes']['address_line'], $supplierDTO->address_line);
    }

    public function test_it_creates_supplier_dto_with_nullable_properties()
    {
        $requestData = [
            'data' => [
                'attributes' => [
                    'name' => 'Nhà Sách Test',
                ],
            ],
        ];

        $supplierDTO = SupplierDTO::fromRequest($requestData);

        $this->assertEquals($requestData['data']['attributes']['name'], $supplierDTO->name);
        $this->assertNull($supplierDTO->phone);
        $this->assertNull($supplierDTO->email);
        $this->assertNull($supplierDTO->city);
        $this->assertNull($supplierDTO->district);
        $this->assertNull($supplierDTO->ward);
        $this->assertNull($supplierDTO->address_line);
    }

    public function test_it_converts_to_array()
    {
        $supplierDTO = new SupplierDTO(
            name: 'Nhà Sách Test',
            phone: '0123456789',
            email: 'test@example.com',
            city: 'Hà Nội',
            district: 'Cầu Giấy',
            ward: 'Dịch Vọng',
            address_line: 'Số 1 Đường ABC'
        );

        $array = $supplierDTO->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('Nhà Sách Test', $array['name']);
        $this->assertEquals('0123456789', $array['phone']);
        $this->assertEquals('test@example.com', $array['email']);
        $this->assertEquals('Hà Nội', $array['city']);
        $this->assertEquals('Cầu Giấy', $array['district']);
        $this->assertEquals('Dịch Vọng', $array['ward']);
        $this->assertEquals('Số 1 Đường ABC', $array['address_line']);
    }

    public function test_it_omits_null_properties_in_array()
    {
        $supplierDTO = new SupplierDTO(
            name: 'Nhà Sách Test',
            phone: null,
            email: null,
            city: null,
            district: null,
            ward: null,
            address_line: null
        );

        $array = $supplierDTO->toArray();

        $this->assertIsArray($array);
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayNotHasKey('phone', $array);
        $this->assertArrayNotHasKey('email', $array);
        $this->assertArrayNotHasKey('city', $array);
        $this->assertArrayNotHasKey('district', $array);
        $this->assertArrayNotHasKey('ward', $array);
        $this->assertArrayNotHasKey('address_line', $array);
    }
}
