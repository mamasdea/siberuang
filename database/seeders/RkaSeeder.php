<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RkaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Fetch all sub_kegiatan IDs from the sub_kegiatans table
        $subKegiatanIds = DB::table('sub_kegiatans')->pluck('id');

        foreach ($subKegiatanIds as $subKegiatanId) {
            for ($i = 1; $i <= 10; $i++) {
                DB::table('rkas')->insert([
                    'sub_kegiatan_id' => $subKegiatanId,
                    'kode_belanja' => 'BEL' . $subKegiatanId . '0' . $i,
                    'nama_belanja' => 'Belanja ' . $i . ' for Sub Kegiatan ID ' . $subKegiatanId,
                    'anggaran' => rand(100000, 1000000), // Random anggaran for illustration
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
