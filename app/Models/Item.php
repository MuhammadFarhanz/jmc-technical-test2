<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $fillable = [
        'user_id',
        'kategori_id',
        'sub_kategori_id',
        'batas_harga',
        'nomor_surat',
        'asal_barang',
        'lampiran_path',
        'daftar_barang',
        'total_item',
        'total_harga'
    ];

    protected $casts = [
        'daftar_barang' => 'array',
        'batas_harga' => 'decimal:2',
        'total_harga' => 'decimal:2'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }

    public function subKategori()
    {
        return $this->belongsTo(SubKategori::class);
    }
}