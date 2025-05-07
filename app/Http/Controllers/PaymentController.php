<?php
namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    // 🔍 List all payments (for admin)
    public function index()
    {
        return Payment::with('order.user')->latest()->get();
    }

    // 📦 View one payment
    public function show(Payment $payment)
    {
        return $payment->load(['order', 'logs']);
    }

    // 💰 Store a new payment (manual input or from frontend)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'method' => 'required|in:cash,static_qr,khqr,aba,card',
            'amount' => 'required|numeric|min:0.01',
            'transaction_id' => 'nullable|string',
            'note' => 'nullable|string'
        ]);

        $order = Order::findOrFail($validated['order_id']);

        if ($order->status === 'paid') {
            return response()->json(['message' => 'Order already paid'], 400);
        }

        $payment = Payment::create([
            'order_id' => $order->id,
            'method' => $validated['method'],
            'amount' => $validated['amount'],
            'transaction_id' => $validated['transaction_id'] ?? Str::uuid(),
            'status' => 'approved',
            'confirmed_at' => now()
        ]);

        // Optional: add log for traceability
        if ($validated['note']) {
            PaymentLog::create([
                'payment_id' => $payment->id,
                'raw_data' => $validated['note'],
                'source' => 'manual'
            ]);
        }

        // Update order status
        $order->update([
            'status' => 'paid',
            'payment_method' => $validated['method'],
            'paid_at' => now()
        ]);

        return response()->json(['message' => 'Payment recorded', 'payment' => $payment], 201);
    }

    // ✍️ Log extra info for payment
    public function log(Request $request, Payment $payment)
    {
        $request->validate([
            'raw_data' => 'required|string',
            'source' => 'required|string'
        ]);

        $log = PaymentLog::create([
            'payment_id' => $payment->id,
            'raw_data' => $request->raw_data,
            'source' => $request->source
        ]);

        return response()->json(['message' => 'Log saved', 'log' => $log]);
    }
}
