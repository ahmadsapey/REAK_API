<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Pembelian;

class PembelianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            'PT. Sentosa Teknologi',
            'CV. Mitra Elektronik',
            'UD. Sumber Jaya',
            'PT. Global Hardware',
            'PT. Solusi Perangkat'
        ];

        $data = [];

        foreach ($suppliers as $i => $s) {
            $data[] = [
                'supplier_name' => $s,
                'total_price' => rand(500000, 5000000),
                'status' => array_rand(array_flip(['pending','received','cancelled'])),
                'notes' => 'Contoh pembelian dari ' . $s,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        Pembelian::insert($data);
    }
}
