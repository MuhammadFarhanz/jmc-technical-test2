<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Models\Item;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $items = Item::with(['dokumen', 'subKategori'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $items
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreItemRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Calculate total
        $validated['total'] = $validated['harga'] * $validated['kuantitas'];

        $item = Item::create($validated);

        // Load relationships for response
        $item->load(['dokumen', 'subKategori']);

        return response()->json([
            'success' => true,
            'data' => $item,
            'message' => 'Item berhasil ditambahkan'
        ], 201);
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $item = Item::with(['dokumen', 'subKategori'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $item
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateItemRequest $request, string $id): JsonResponse
    {
        $item = Item::findOrFail($id);
        $validated = $request->validated();

        // Recalculate total if price or quantity changed
        if (isset($validated['harga']) || isset($validated['kuantitas'])) {
            $harga = $validated['harga'] ?? $item->harga;
            $kuantitas = $validated['kuantitas'] ?? $item->kuantitas;
            $validated['total'] = $harga * $kuantitas;
        }

        $item->update($validated);
        $item->refresh()->load(['dokumen', 'subKategori']);

        return response()->json([
            'success' => true,
            'data' => $item,
            'message' => 'Item berhasil diperbarui'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $item = Item::findOrFail($id);
        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item berhasil dihapus'
        ]);
    }
}
