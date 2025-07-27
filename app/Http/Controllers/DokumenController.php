<?php

namespace App\Http\Controllers;

use App\Models\Dokumen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DokumenController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $dokumens = Dokumen::with('user')->get();
        return response()->json($dokumens);
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
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'nomor_dokumen' => 'required|string|max:255',
            'sumber' => 'required|string|max:255',
            'lampiran' => 'nullable|file|mimes:doc,docx,zip|max:5120',
        ]);

        $lampiranPath = null;
        if ($request->hasFile('lampiran')) {
            $lampiranPath = $request->file('lampiran')->store('lampiran_dokumen');
        }

        $dokumen = Dokumen::create([
            'user_id' => $validated['user_id'],
            'nomor_dokumen' => $validated['nomor_dokumen'],
            'sumber' => $validated['sumber'],
            'lampiran_path' => $lampiranPath,
        ]);

        return response()->json($dokumen, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $dokumen = Dokumen::with('user')->findOrFail($id);
        return response()->json($dokumen);
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
    public function update(Request $request, string $id)
    {
        $dokumen = Dokumen::findOrFail($id);
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'nomor_dokumen' => 'required|string|max:255',
            'sumber' => 'required|string|max:255',
            'lampiran' => 'nullable|file|mimes:doc,docx,zip|max:5120',
        ]);

        $lampiranPath = $dokumen->lampiran_path;
        if ($request->hasFile('lampiran')) {
            if ($lampiranPath) {
                Storage::delete($lampiranPath);
            }
            $lampiranPath = $request->file('lampiran')->store('lampiran_dokumen');
        }

        $dokumen->update([
            'user_id' => $validated['user_id'],
            'nomor_dokumen' => $validated['nomor_dokumen'],
            'sumber' => $validated['sumber'],
            'lampiran_path' => $lampiranPath,
        ]);

        return response()->json($dokumen);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $dokumen = Dokumen::findOrFail($id);
        if ($dokumen->lampiran_path) {
            Storage::delete($dokumen->lampiran_path);
        }
        $dokumen->delete();
        return response()->json(['message' => 'Dokumen deleted']);
    }
}
