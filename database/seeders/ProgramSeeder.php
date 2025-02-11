<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $programs = [
            ['kode' => 'PRG001', 'nama' => 'Program A'],
            ['kode' => 'PRG002', 'nama' => 'Program B'],
            ['kode' => 'PRG003', 'nama' => 'Program C'],
            ['kode' => 'PRG004', 'nama' => 'Program D'],
            ['kode' => 'PRG005', 'nama' => 'Program E'],
            ['kode' => 'PRG006', 'nama' => 'Program F'],
            ['kode' => 'PRG007', 'nama' => 'Program G'],
            ['kode' => 'PRG008', 'nama' => 'Program H'],
            ['kode' => 'PRG009', 'nama' => 'Program I'],
            ['kode' => 'PRG010', 'nama' => 'Program J'],
        ];

        foreach ($programs as $program) {
            DB::table('programs')->insert([
                'kode' => $program['kode'],
                'nama' => $program['nama'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
