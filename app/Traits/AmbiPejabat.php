<?php

namespace App\Traits;

use App\Models\PengelolaKeuangan;

trait AmbiPejabat
{
    /**
     * Ambil data pengelola keuangan yang aktif pada tanggal tertentu.
     * Fallback ke data terbaru jika tidak ditemukan berdasarkan range.
     *
     * @return array{pa: mixed, bp: mixed, ppk: mixed, pb: mixed}
     */
    protected function ambilPejabat(string $tanggal): array
    {
        $jabatans = ['PENGGUNA ANGGARAN', 'BENDAHARA PENGELUARAN', 'PPK-SKPD', 'PENGURUS BARANG'];

        // Jika ada lebih dari 1 record aktif per jabatan, ambil yang tanggal_mulai terbaru
        $pejabat = PengelolaKeuangan::whereIn('jabatan', $jabatans)
            ->aktifPadaTanggal($tanggal)
            ->orderBy('tanggal_mulai', 'desc')
            ->get(['id', 'jabatan', 'nama', 'nip'])
            ->keyBy('jabatan');

        // Fallback per jabatan jika tidak ditemukan di range tanggal
        foreach ($jabatans as $j) {
            if (!$pejabat->has($j)) {
                $fallback = PengelolaKeuangan::where('jabatan', $j)
                    ->orderBy('id', 'desc')
                    ->first(['id', 'jabatan', 'nama', 'nip']);
                if ($fallback) {
                    $pejabat->put($j, $fallback);
                }
            }
        }

        return [
            'pa'  => $pejabat->get('PENGGUNA ANGGARAN'),
            'bp'  => $pejabat->get('BENDAHARA PENGELUARAN'),
            'ppk' => $pejabat->get('PPK-SKPD'),
            'pb'  => $pejabat->get('PENGURUS BARANG'),
        ];
    }
}
