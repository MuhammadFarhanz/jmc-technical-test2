<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\SubKategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ItemController extends Controller
{

    /**
     * Display a listing of all items.
     */
    public function index()
    {
        // Get all items with their related kategori, subKategori, and user data
        $items = Item::with(['kategori', 'subKategori', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $items
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'kategori_id' => 'required|exists:kategori,id',
            'sub_kategori_id' => 'required|exists:sub_kategori,id',
            'nomor_surat' => 'required|string|max:200|unique:items,nomor_surat',
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

        return response()->json([
            'message' => 'Data berhasil disimpan',
            'data' => $item
        ], 201);
    }
}