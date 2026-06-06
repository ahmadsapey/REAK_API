<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ProdukImage;

class Produk extends Model
{
    protected $fillable = [
        'kode_barang',
        'nama_barang',
        'harga',
        'stok',
        'deskripsi',
        'gambar',
        'kategori',
        'expiredDate',
        'rating',
    ];

    public function images()
    {
        return $this->hasMany(ProdukImage::class);
    }

}
