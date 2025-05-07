<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\MenuItem;
use App\Models\Stock;
use App\Models\Discount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    // 🔍 Get all orders
    public function index()
    {
        return Order::with(['user', 'orderItems.menuItem'])->get();
    }

    // 🔍 Get a single order
    public function show(Order $order)
    {
        return $order->load(['user', 'orderItems.menuItem']);
    }

    // 🧾 Place a new order with optional discount
    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'code' => 'nullable|string'
        ]);

        DB::beginTransaction();

        try {
            $total = 0;
            $discount = null;
            $discountAmount = 0;

            // 🔍 Validate discount code
            if ($request->code) {
                $discount = Discount::where('code', strtoupper($request->code))
                    ->where('active', true)
                    ->where(function ($q) {
                        $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
                    })
                    ->first();

                if (!$discount) {
                    return response()->json(['message' => 'Invalid or expired discount code.'], 400);
                }
            }

            $order = Order::create([
                'user_id' => $request->user()->id,
                'order_code' => 'ORD-' . strtoupper(Str::random(8)),
                'total_amount' => 0,
                'discount_id' => null,
                'discount_amount' => 0,
                'status' => 'pending',
                'payment_method' => null,
            ]);

            foreach ($validated['items'] as $item) {
                $menuItem = MenuItem::with('recipes')->findOrFail($item['menu_item_id']);
                $quantity = $item['quantity'];
                $subtotal = $menuItem->price * $quantity;

                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $menuItem->id,
                    'quantity' => $quantity,
                    'price' => $menuItem->price,
                    'subtotal' => $subtotal,
                ]);

                $total += $subtotal;

                foreach ($menuItem->recipes as $recipe) {
                    $requiredQty = $recipe->quantity * $quantity;
                    $stock = Stock::where('ingredient_id', $recipe->ingredient_id)->first();

                    if (!$stock || $stock->quantity < $requiredQty) {
                        DB::rollBack();
                        return response()->json([
                            'message' => 'Insufficient stock for ingredient: ' . $recipe->ingredient->name
                        ], 400);
                    }

                    $stock->quantity -= $requiredQty;
                    $stock->save();
                }
            }

            // 💸 Apply discount
            if ($discount) {
                if ($discount->percentage) {
                    $discountAmount = $total * ($discount->percentage / 100);
                } elseif ($discount->amount) {
                    $discountAmount = min($discount->amount, $total);
                }
            }

            $order->update([
                'total_amount' => $total - $discountAmount,
                'discount_id' => $discount?->id,
                'discount_amount' => $discountAmount
            ]);

            DB::commit();
            return response()->json($order->load('orderItems.menuItem'), 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Order failed', 'error' => $e->getMessage()], 500);
        }
    }

    // 💳 Mark an order as paid
    public function pay(Request $request, Order $order)
    {
        $request->validate([
            'payment_method' => 'required|in:cash,aba,bakong'
        ]);

        if ($order->status !== 'pending') {
            return response()->json(['message' => 'Only pending orders can be paid.'], 400);
        }

        $order->update([
            'payment_method' => $request->payment_method,
            'status' => 'completed',
            'paid_at' => now(),
        ]);

        return response()->json(['message' => 'Order marked as paid.']);
    }

    // ❌ Cancel an order and restore stock
    public function cancel(Order $order)
    {
        if ($order->status !== 'pending') {
            return response()->json(['message' => 'Only pending orders can be cancelled.'], 400);
        }

        DB::beginTransaction();

        try {
            foreach ($order->orderItems as $item) {
                $recipes = $item->menuItem->recipes;

                foreach ($recipes as $recipe) {
                    $restoredQty = $recipe->quantity * $item->quantity;

                    $stock = Stock::firstOrCreate(['ingredient_id' => $recipe->ingredient_id]);
                    $stock->quantity += $restoredQty;
                    $stock->save();
                }
            }

            $order->update(['status' => 'cancelled']);

            DB::commit();
            return response()->json(['message' => 'Order cancelled and stock restored.']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to cancel order.', 'error' => $e->getMessage()], 500);
        }
    }
}
