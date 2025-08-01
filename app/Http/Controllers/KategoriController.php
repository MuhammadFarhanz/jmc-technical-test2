<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kategori;
use Inertia\Inertia;

class KategoriController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $kategoris = Kategori::all();
        return Inertia::render('Kategori/Index', [
            'kategoris' => $kategoris
        ]);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Not used for API, handled by frontend
        return response()->json(['message' => 'Not implemented'], 501);
    }

    /**
     * Store a newly created resource in storage.
     */


    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_kategori' => 'required|string|unique:kategori,kode_kategori',
            'nama_kategori' => 'required|string|max:100',
        ]);

        Kategori::create($validated);

        return redirect()->route('master-data.kategori')->with('success', 'Kategori berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $kategori = Kategori::findOrFail($id);
        return response()->json($kategori);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // Not used for API, handled by frontend
        return response()->json(['message' => 'Not implemented'], 501);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $kategori = Kategori::findOrFail($id);

        $validated = $request->validate([
            'kode_kategori' => 'required|string|unique:kategori,kode_kategori,' . $kategori->id,
            'nama_kategori' => 'required|string|max:100',
        ]);

        $kategori->update($validated);

        return redirect()->route('master-data.kategori')->with('success', 'Kategori berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $kategori = Kategori::findOrFail($id);
        $kategori->delete();
        return response()->json(['message' => 'Kategori deleted']);
    }
}
