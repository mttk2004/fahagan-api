<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\StatsService;
use App\Traits\HandleExceptions;
use App\Utils\ResponseUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminStatsController extends Controller
{
  use HandleExceptions;

  public function __construct(
    private readonly StatsService $statsService
  ) {}

  /**
   * Get all stats
   *
   * @return JsonResponse
   * @group Admin.Stats
   * @authenticated
   */
  public function index(Request $request)
  {
    $stats = $this->statsService->recentlyStats($request);

    return ResponseUtils::success($stats);
  }
}
