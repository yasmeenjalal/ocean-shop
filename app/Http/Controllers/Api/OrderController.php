<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $order = DB::transaction(function () use ($data, $request) {
            $order = Order::create([
                'user_id' => $request->user()->id,
                'total_price' => 0,
                'status' => 'pending',
            ]);

            $total = 0;

            foreach ($data['items'] as $item) {
                $product = Product::lockForUpdate()->find($item['product_id']);

                if ($product->stock < $item['quantity']) {
                    abort(response()->json([
                        'message' => "المطلوب من {$product->name} أكبر من المتوفر",
                    ], 422));
                }

                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                ]);

                $product->decrement('stock', $item['quantity']);

                $total += $product->price * $item['quantity'];
            }

            $order->update(['total_price' => $total]);

            return $order;
        });

        return response()->json($order->load('items.product'), 201);
    }

public function myOrders(Request $request): JsonResponse
{
    $orders = Order::with('items.product')
        ->where('user_id', $request->user()->id)
        ->latest()
        ->get();

    return response()->json($orders);
}

public function adminIndex(): JsonResponse
{
    $orders = Order::with(['user', 'items.product'])
        ->latest()
        ->get();

    return response()->json($orders);
}

public function updateStatus(Request $request, int $id): JsonResponse
{
    $order = Order::find($id);

    if (! $order) {
        return response()->json(['message' => 'الطلب غير موجود'], 404);
    }

    $data = $request->validate([
        'status' => ['required', 'in:pending,processing,shipped,delivered,cancelled'],
    ]);

    $order->update($data);

    return response()->json($order);
}


}