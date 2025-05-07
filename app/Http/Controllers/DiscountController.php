<?php

namespace App\Http\Controllers;

use App\Models\Discount;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    public function index()
    {
        return Discount::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:discounts',
            'percentage' => 'nullable|numeric|min:0|max:100',
            'amount' => 'nullable|numeric|min:0',
            'expires_at' => 'nullable|date',
            'active' => 'boolean'
        ]);

        $discount = Discount::create($validated);
        return response()->json(['message' => 'Discount created.', 'discount' => $discount], 201);
    }

    public function show(Discount $discount)
    {
        return $discount;
    }

    public function update(Request $request, Discount $discount)
    {
        $discount->update($request->all());
        return response()->json(['message' => 'Discount updated.', 'discount' => $discount]);
    }

    public function destroy(Discount $discount)
    {
        $discount->delete();
        return response()->json(['message' => 'Discount deleted.']);
    }
}
