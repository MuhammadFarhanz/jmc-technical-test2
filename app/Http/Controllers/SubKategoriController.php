<?php

namespace App\Http\Controllers;

use App\Models\SubKategori;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;

class SubKategoriController extends Controller
{

    public function index()
    {
        $subkategoris = SubKategori::with('kategori')
            ->get(); 
     


        return Inertia::render('SubKategori/Index', [
            'subKategoris' => $subkategoris
        ]);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'kategori_id' => 'required|exists:kategori,id',
            'nama_subkategori' => 'required|string|max:100',
            'batas_harga' => 'required|numeric|min:1',
        ]);

        $subkategori = SubKategori::create($validated);
        return back()->with('success', 'Sub kategori created successfully');
    }

    public function update(Request $request, SubKategori $subkategori)
    {
        $validated = $request->validate([
            'kategori_id' => 'required|exists:kategori,id',
            'nama_subkategori' => 'required|string|max:100',
            'batas_harga' => 'nullable|numeric|min:0',
        ]);

        $subkategori->update($validated);
        return back()->with('success', 'Sub kategori updated successfully');
    }

    // Delete a subkategori
    public function destroy($id)
    {
        $subkategori = SubKategori::findOrFail($id);
        $subkategori->delete();
        return response()->json(['message' => 'SubKategori deleted']);
    }
}
