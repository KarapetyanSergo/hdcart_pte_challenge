<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function getLatestOrders(): JsonResponse
    {
        $latestOrders = DB::table('order_product_test')
        ->fromSub(function ($q) {
            $q->from('order_product_test')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                ->from('product_test')
                ->whereColumn('order_product_test.product_id', 'product_test.id')
                ->whereJsonContains('product_test.payload', ['status' => Product::PUBLISH_STATUS]);
            })
            ->where('order_product_test.price', '>', 0)
            ->where('order_product_test.cost', '>', 0)
            ->selectRaw("*, ROW_NUMBER() OVER (PARTITION BY product_id ORDER BY line_item_id DESC) AS rn");
        }, 'latest_orders')
        ->where('latest_orders.rn', 1)
        ->get();

        return response()->json([
            'latest_orders' => $latestOrders
        ]);
    }
}
