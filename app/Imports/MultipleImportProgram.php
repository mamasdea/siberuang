<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MultipleImportProgram implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            0 => new ProgramImport(),     // Sheet 1: Program
            1 => new KegiatanImport(),    // Sheet 2: Kegiatan
            2 => new SubKegiatanImport(), // Sheet 3: SubKegiatan
            3 => new RkaImport(),         // Sheet 4: RKA
        ];
    }
}
