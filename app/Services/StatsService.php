<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StatsService
{
    public function recentlyStats(Request $request)
    {
        // Lấy tham số day từ request và đảm bảo nó là số nguyên
        $day = $request->has('day') ? (int)$request->input('day') : 7;

        Log::info('Stats requested with day parameter: ' . $day);

        $date_from = now()->subDays($day);
        $date_to = now();

        Log::info('Date range: ' . $date_from->toDateTimeString() . ' to ' . $date_to->toDateTimeString());

        // Count Order
        $order_count = Order::whereBetween('created_at', [$date_from, $date_to])->count();

        // Total Revenue
        $orders = Order::whereBetween('created_at', [$date_from, $date_to])->get();
        $total_revenue = $orders->sum(function ($order) {
            return $order->getTotalAmount();
        });

        // Total Book Sold
        $total_book_sold = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
          ->whereBetween('orders.created_at', [$date_from, $date_to])
          ->sum('order_items.quantity');

        // Top 5 customers with highest revenue
        $all_orders = Order::whereBetween('created_at', [$date_from, $date_to])
          ->with('customer', 'items')
          ->get();

        $customer_revenues = $all_orders->groupBy('customer_id')
          ->map(function ($orders) {
              $total_revenue = $orders->sum(function ($order) {
                  return $order->getTotalAmount();
              });

              return [
                'customer_id' => $orders->first()->customer_id,
                'customer' => $orders->first()->customer,
                'orders' => $orders,
                'total_revenue' => $total_revenue,
              ];
          })
          ->sortByDesc('total_revenue')
          ->take(5)
          ->values();

        return [
          'order_count' => $order_count,
          'total_book_sold' => $total_book_sold,
          'total_revenue' => $total_revenue,
          'top_customers' => $customer_revenues,
        ];
    }
}
