<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubKegiatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Fetch all kegiatan IDs from the kegiatans table
        $kegiatanIds = DB::table('kegiatans')->pluck('id');

        foreach ($kegiatanIds as $kegiatanId) {
            for ($i = 1; $i <= 10; $i++) {
                DB::table('sub_kegiatans')->insert([
                    'kegiatan_id' => $kegiatanId,
                    'kode' => 'SUBKEG' . $kegiatanId . '0' . $i,
                    'nama' => 'Sub Kegiatan ' . $i . ' for Kegiatan ID ' . $kegiatanId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
