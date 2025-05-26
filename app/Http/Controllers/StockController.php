<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StockController extends Controller
{
    // 📦 Get all stock items with related ingredient info
    public function index()
    {
        return Stock::with('ingredient')->get();
    }

    // 📥 Create or update stock (single or batch)
    public function store(Request $request)
    {
        if ($request->has('items')) {
            $data = $request->validate([
                'items' => 'required|array|min:1',
                'items.*.ingredient_id' => 'required|exists:ingredients,id',
                'items.*.quantity' => 'required|numeric|min:0',
            ]);

            $result = [];

            foreach ($data['items'] as $item) {
                $stock = Stock::updateOrCreate(
                    ['ingredient_id' => $item['ingredient_id']],
                    ['quantity' => $item['quantity']]
                );

                $result[] = $stock->load('ingredient');
            }

            return response()->json([
                'message' => 'Stock batch updated successfully.',
                'stocks' => $result
            ]);
        } else {
            $data = $request->validate([
                'ingredient_id' => 'required|exists:ingredients,id',
                'quantity' => 'required|numeric|min:0',
            ]);

            $stock = Stock::updateOrCreate(
                ['ingredient_id' => $data['ingredient_id']],
                ['quantity' => $data['quantity']]
            );

            return response()->json([
                'message' => 'Stock updated.',
                'stock' => $stock->load('ingredient')
            ]);
        }
    }

    // 👁 View a specific stock entry
    public function show(Stock $stock)
    {
        return response()->json($stock->load('ingredient'));
    }

    // ✏️ Update a specific stock entry by ID
    public function update(Request $request, Stock $stock)
    {
        $data = $request->validate([
            'quantity' => 'required|numeric|min:0',
        ]);

        $stock->update($data);

        return response()->json([
            'message' => 'Stock updated successfully.',
            'stock' => $stock->load('ingredient')
        ]);
    }

    // ❌ Delete a specific stock entry by ID
    public function destroy(Stock $stock)
    {
        $stock->delete();

        return response()->json([
            'message' => 'Stock deleted successfully.'
        ]);
    }
}
