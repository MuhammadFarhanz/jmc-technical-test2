<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubKategori extends Model
{
    protected $table = 'sub_kategori';
    protected $fillable = [
        'kategori_id',
        'nama_subkategori', 
        'batas_harga'
    ];
    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }

    public function items()
    {
        return $this->hasMany(Item::class);
    }
}
