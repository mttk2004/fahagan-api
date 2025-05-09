<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\BookCollection;
use App\Services\SearchService;
use App\Traits\HandleExceptions;
use App\Traits\HandlePagination;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchBookController extends Controller
{
    use HandleExceptions;
    use HandlePagination;

    protected SearchService $searchService;

    public function __construct()
    {
        $this->searchService = new SearchService();
    }

    /**
     * Tìm kiếm sách với các tham số tùy chọn
     *
     * @param Request $request
     * @return BookCollection|JsonResponse
     *
     * @group Search
     *
     * @unauthenticated
     */
    public function search(Request $request)
    {
        try {
            $books = $this->searchService->searchBooks($request, $this->getPerPage($request));

            return new BookCollection($books);
        } catch (Exception $e) {
            return $this->handleException($e, 'search', [
              'query' => $request->all(),
            ]);
        }
    }
}
