<?php

namespace App\Imports;

use App\Models\RekeningBelanja;
use Maatwebsite\Excel\Concerns\ToModel;

class RekeningBelanjaImport implements ToModel
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new RekeningBelanja([
            'kode' => $row[0],
            'uraian_belanja' => $row[1],
        ]);
    }
}
