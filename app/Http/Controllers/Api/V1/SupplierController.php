<?php

namespace App\Http\Controllers\Api\V1;


use App\Http\Controllers\Controller;
use App\Http\Resources\V1\SupplierCollection;
use App\Http\Resources\V1\SupplierResource;
use App\Http\Sorts\V1\SupplierSort;
use App\Models\Supplier;
use App\Utils\ResponseUtils;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;


class SupplierController extends Controller
{
	public function index(Request $request)
	{
		$supplierSort = new SupplierSort($request);
		$suppliers = $supplierSort->apply(Supplier::query())->paginate();

		return ResponseUtils::success([
			'suppliers' => new SupplierCollection($suppliers),
		]);
	}

	public function store(Request $request) {}

	public function show($supplier_id)
	{
		try {
			$supplier = Supplier::findOrFail($supplier_id);

			return ResponseUtils::success([
				'supplier' => new SupplierResource($supplier),
			]);
		} catch (ModelNotFoundException) {
			return ResponseUtils::notFound();
		}
	}

	public function update(Request $request, $id) {}

	public function destroy($id) {}
}
