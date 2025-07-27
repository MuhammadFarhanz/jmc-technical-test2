<?php

namespace App\Http\Controllers;

use App\Models\SubKategori;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SubKategoriController extends Controller
{
    // List all subkategori
    public function index()
    {
        $subkategoris = SubKategori::with('kategori')->get();
        return response()->json($subkategoris);
    }

    // Store a new subkategori
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kategori_id' => 'required|exists:kategori,id',
            'nama_subkategori' => 'required|string|max:100',
            'batas_harga' => 'nullable|numeric|min:0',
        ]);
        $subkategori = SubKategori::create($validated);
        return response()->json($subkategori, 201);
    }

    // Show a single subkategori
    public function show($id)
    {
        $subkategori = SubKategori::with('kategori')->findOrFail($id);
        return response()->json($subkategori);
    }

    // Update a subkategori
    public function update(Request $request, $id)
    {
        $subkategori = SubKategori::findOrFail($id);
        $validated = $request->validate([
            'kategori_id' => 'required|exists:kategori,id',
            'nama_subkategori' => 'required|string|max:100',
            'batas_harga' => 'nullable|numeric|min:0',
        ]);
        $subkategori->update($validated);
        return response()->json($subkategori);
    }

    // Delete a subkategori
    public function destroy($id)
    {
        $subkategori = SubKategori::findOrFail($id);
        $subkategori->delete();
        return response()->json(['message' => 'SubKategori deleted']);
    }
}
