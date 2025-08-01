<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Kategori;
use App\Models\SubKategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ItemController extends Controller
{

    /**
     * Display a listing of all items.
     */

    public function index()
    {

        $items = Item::with(['kategori', 'subKategori', 'user'])
            ->get(); // Remove pagination

        return Inertia::render('BarangMasuk/Index', [
            'items' => $items, // Full dataset
            'kategori' => Kategori::all(),
            'subkategoris' => SubKategori::all()
        ]);
    }

    public function store(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'kategori_id' => 'required|exists:kategori,id',
            'sub_kategori_id' => 'required|exists:sub_kategori,id',
            'nomor_surat' => 'nullable|string|max:200',
            'asal_barang' => 'required|string|max:200',
            'lampiran' => 'nullable|file|mimes:doc,docx,zip|max:2048',
            'barang' => 'required|array|min:1',
            'barang.*.nama' => 'required|string|max:200',
            'barang.*.harga' => 'required|numeric|min:0',
            'barang.*.jumlah' => 'required|integer|min:1',
            'barang.*.satuan' => 'required|string|max:50',
            'barang.*.tgl_expired' => 'nullable|date',
        ]);


        $totalHarga = collect($validated['barang'])->sum(fn($item) => $item['harga'] * $item['jumlah']);
        $totalItem = count($validated['barang']);


        $item = Item::create([
            'user_id' => $validated['user_id'],
            'kategori_id' => $validated['kategori_id'],
            'sub_kategori_id' => $validated['sub_kategori_id'],
            'batas_harga' => SubKategori::find($validated['sub_kategori_id'])->batas_harga,
            'nomor_surat' => $validated['nomor_surat'],
            'asal_barang' => $validated['asal_barang'],
            'lampiran_path' => $request->hasFile('lampiran')
                ? $request->file('lampiran')->store('lampiran')
                : null,
            'daftar_barang' => $validated['barang'],
            'total_item' => $totalItem,
            'total_harga' => $totalHarga
        ]);

        // Return Inertia response
        return redirect()->route('dashboard')->with([
            'message' => 'Data berhasil disimpan',
            'data' => $item,
            'type' => 'success'
        ]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Item $item)
    {
        // Validate the request
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'kategori_id' => 'required|exists:kategori,id',
            'sub_kategori_id' => 'required|exists:sub_kategori,id',
            'nomor_surat' => 'nullable|string|max:200',
            'asal_barang' => 'required|string|max:200',
            'lampiran' => 'nullable|file|mimes:doc,docx,zip|max:2048',
            'barang' => 'required|array|min:1',
            'barang.*.nama' => 'required|string|max:200',
            'barang.*.harga' => 'required|numeric|min:0',
            'barang.*.jumlah' => 'required|integer|min:1',
            'barang.*.satuan' => 'required|string|max:50',
            'barang.*.tgl_expired' => 'nullable|date',
        ]);

        $totalHarga = collect($validated['barang'])->sum(fn($item) => $item['harga'] * $item['jumlah']);
        $totalItem = count($validated['barang']);

        // Handle file upload
        $lampiranPath = $item->lampiran_path;
        if ($request->hasFile('lampiran')) {
            // Delete old file if exists
            if ($lampiranPath) {
                Storage::delete($lampiranPath);
            }
            $lampiranPath = $request->file('lampiran')->store('lampiran');
        }

        $item->update([
            'user_id' => $validated['user_id'],
            'kategori_id' => $validated['kategori_id'],
            'sub_kategori_id' => $validated['sub_kategori_id'],
            'batas_harga' => SubKategori::find($validated['sub_kategori_id'])->batas_harga,
            'nomor_surat' => $validated['nomor_surat'],
            'asal_barang' => $validated['asal_barang'],
            'lampiran_path' => $lampiranPath,
            'daftar_barang' => $validated['barang'],
            'total_item' => $totalItem,
            'total_harga' => $totalHarga
        ]);

        return redirect()->route('dashboard')->with([
            'message' => 'Data berhasil diperbarui',
            'type' => 'success'
        ]);
    }
}