<?php

namespace App\Imports;

use App\Models\Program;
use App\Models\Kegiatan;
use App\Models\SubKegiatan;
use App\Models\Rka;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

// Import untuk Program
class ProgramImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return Program::updateOrCreate(
            ['id' => $row['id']],
            [
                'kode' => $row['kode'],
                'nama' => $row['nama'],
            ]
        );
    }
}

// Import untuk Kegiatan
class KegiatanImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new Kegiatan([
            'id' => $row['id'],
            'program_id' => $row['program_id'],
            'kode' => $row['kode'],
            'nama' => $row['nama'],
        ]);
    }
}

// Import untuk SubKegiatan
class SubKegiatanImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new SubKegiatan([
            'id' => $row['id'],
            'kegiatan_id' => $row['kegiatan_id'],
            'kode' => $row['kode'],
            'nama' => $row['nama'],
        ]);
    }
}

// Import untuk RKA
class RkaImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new Rka([
            'id' => $row['id'],
            'sub_kegiatan_id' => $row['sub_kegiatan_id'],
            'kode_belanja' => $row['kode_belanja'],
            'nama_belanja' => $row['nama_belanja'],
            'anggaran' => $row['anggaran'],
        ]);
    }
}
