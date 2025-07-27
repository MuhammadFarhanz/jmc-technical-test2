<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Item extends Model
{
    protected $fillable = [
        'dokumen_id',
        'subkategori_id',
        'nama_item',
        'harga',
        'kuantitas',
        'satuan',
        'total',
        'asal_barang',
        'tanggal_kadaluarsa'
    ];

    protected $casts = [
        'harga' => 'decimal:2',
        'total' => 'decimal:2',
        'tanggal_kadaluarsa' => 'date:Y-m-d'
    ];

    public function dokumen(): BelongsTo
    {
        return $this->belongsTo(Dokumen::class);
    }

    public function subKategori(): BelongsTo
    {
        return $this->belongsTo(SubKategori::class, 'subkategori_id');
    }
}