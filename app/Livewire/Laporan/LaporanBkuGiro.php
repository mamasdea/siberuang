<?php

namespace App\Livewire\Laporan;

use Livewire\Component;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;

#[Title('Lapuran GU Giro')]
class LaporanBkuGiro extends Component
{
    public $bulan = '';
    public $laporan = [];

    public function updatedBulan()
    {
        if ($this->bulan) {
            $this->ambilLaporan(); // Perbarui data saat bulan berubah
        }
    }

    public function ambilLaporan()
    {
        if (!$this->bulan) {
            $this->laporan = [];
            return;
        }

        $year = date("Y"); // Tahun default (bisa diubah)
        $startDate = "{$year}-{$this->bulan}-01";
        $endDate = date("Y-m-t", strtotime($startDate));

        $this->laporan = DB::select("
        WITH BelanjaUtama AS (
            SELECT
                b.id AS belanja_id,
                b.no_bukti,
                b.tanggal,
                r.kode_belanja AS rekening,
                b.uraian,
                b.nilai AS nominal,
                NULL AS jenis_pajak,
                NULL AS no_billing,
                NULL AS pajak_nominal
            FROM belanjas b
            JOIN rkas r ON b.rka_id = r.id
            WHERE b.tanggal BETWEEN ? AND ?
        ),
        PajakBelanja AS (
            SELECT
                p.belanja_id,
                NULL AS no_bukti,
                b.tanggal,
                NULL AS rekening,
                CONCAT('   Pajak: ', p.jenis_pajak) AS uraian,
                NULL AS nominal,
                p.jenis_pajak,
                p.no_billing,
                p.nominal AS pajak_nominal
            FROM pajaks p
            JOIN belanjas b ON p.belanja_id = b.id
            WHERE b.tanggal BETWEEN ? AND ?
        )
        SELECT * FROM (
            SELECT * FROM BelanjaUtama
            UNION ALL
            SELECT * FROM PajakBelanja
        ) AS laporan
        ORDER BY tanggal, no_bukti IS NULL, belanja_id, jenis_pajak IS NULL DESC
    ", [$startDate, $endDate, $startDate, $endDate]);
        dd($this->laporan);
    }

    public function render()
    {
        return view('livewire.laporan.laporan-bku-giro', [
            'laporan' => $this->laporan,
        ]);
    }
}
