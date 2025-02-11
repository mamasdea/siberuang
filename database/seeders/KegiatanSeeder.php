<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KegiatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Fetch all program IDs from the programs table
        $programIds = DB::table('programs')->pluck('id');

        foreach ($programIds as $programId) {
            for ($i = 1; $i <= 10; $i++) {
                DB::table('kegiatans')->insert([
                    'program_id' => $programId,
                    'kode' => 'KEG' . $programId . '0' . $i,
                    'nama' => 'Kegiatan ' . $i . ' for Program ID ' . $programId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
