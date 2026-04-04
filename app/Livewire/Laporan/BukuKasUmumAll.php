<?php

namespace App\Livewire\Laporan;

use Livewire\Component;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;
use App\Models\PengelolaKeuangan;

#[Title('Buku Kas Umum')]
class BukuKasUmumAll extends Component
{
    public $mulai;
    public $end;
    public $tahun;
    public $filterJenis = 'semua'; // semua, gu, kkpd, tu, ls
    public $tanggalCetak;

    public function mount()
    {
        $this->tahun = session('tahun_anggaran', date('Y'));
        $this->mulai = $this->tahun . "-01-01";
        $this->end = $this->tahun . "-01-01";
        $this->tanggalCetak = date('Y-m-d');
    }

    public function render()
    {
        $startYear = $this->tahun . "-01-01";
        $filter = $this->filterJenis;

        // === SALDO AWAL ===
        $saldo = 0;

        if ($filter === 'semua' || $filter === 'gu') {
            $saldo += DB::table('uang_giros')->whereIn('tipe', ['UP', 'GU'])
                ->where('tanggal', '<', $this->mulai)->where('tanggal', '>=', $startYear)->sum('nominal');
            $saldo -= DB::table('belanjas')->where('is_transfer', 1)
                ->where('tanggal', '<', $this->mulai)->where('tanggal', '>=', $startYear)->sum('nilai');
        }

        if ($filter === 'semua' || $filter === 'kkpd') {
            $saldo += DB::table('uang_kkpds')
                ->where('tanggal', '<', $this->mulai)->where('tanggal', '>=', $startYear)->sum('nominal');
            $saldo -= DB::table('belanja_kkpds')->where('is_transfer', 1)
                ->where('tanggal', '<', $this->mulai)->where('tanggal', '>=', $startYear)->sum('nilai');
        }

        if ($filter === 'semua' || $filter === 'tu') {
            $saldo += DB::table('uang_giros')->where('tipe', 'TU')
                ->where('tanggal', '<', $this->mulai)->where('tanggal', '>=', $startYear)->sum('nominal');
            $saldo -= DB::table('belanja_tus')
                ->where('tanggal', '<', $this->mulai)->where('tanggal', '>=', $startYear)->sum('nilai');
            $saldo -= DB::table('spp_spm_tu_nihils')
                ->where('tanggal', '<', $this->mulai)->where('tanggal', '>=', $startYear)->sum('nilai_setor');
        }

        // === DATA TRANSAKSI ===
        $queries = [];
        $m = $this->mulai;
        $e = $this->end;

        if ($filter === 'semua' || $filter === 'gu') {
            $queries[] = "SELECT '' AS id, tanggal, no_bukti, '' AS rekening, uraian, nominal AS debet, 0 AS kredit, 'sp2d_gu' AS jenis, 'GU' AS kategori, CONCAT('0_', tanggal) AS grp, 0 AS sub_urut FROM uang_giros WHERE tipe IN ('UP','GU') AND tanggal >= '{$m}' AND tanggal <= '{$e}'";
            $queries[] = "SELECT belanjas.id, tanggal, no_bukti, kode_belanja AS rekening, belanjas.uraian, 0 AS debet, nilai AS kredit, 'belanja_gu' AS jenis, 'GU' AS kategori, CONCAT('1_', tanggal) AS grp, 0 AS sub_urut FROM belanjas JOIN rkas ON rkas.id = belanjas.rka_id WHERE belanjas.is_transfer = 1 AND tanggal >= '{$m}' AND tanggal <= '{$e}'";
        }

        if ($filter === 'semua' || $filter === 'kkpd') {
            $queries[] = "SELECT '' AS id, tanggal, no_bukti, '' AS rekening, uraian, nominal AS debet, 0 AS kredit, 'sp2d_kkpd' AS jenis, 'KKPD' AS kategori, CONCAT('0_', tanggal) AS grp, 0 AS sub_urut FROM uang_kkpds WHERE tanggal >= '{$m}' AND tanggal <= '{$e}'";
            $queries[] = "SELECT belanja_kkpds.id, tanggal, no_bukti, kode_belanja AS rekening, belanja_kkpds.uraian, 0 AS debet, nilai AS kredit, 'belanja_kkpd' AS jenis, 'KKPD' AS kategori, CONCAT('1_', tanggal) AS grp, 0 AS sub_urut FROM belanja_kkpds JOIN rkas ON rkas.id = belanja_kkpds.rka_id WHERE belanja_kkpds.is_transfer = 1 AND tanggal >= '{$m}' AND tanggal <= '{$e}'";
        }

        if ($filter === 'semua' || $filter === 'tu') {
            $queries[] = "SELECT '' AS id, tanggal, no_bukti, '' AS rekening, uraian, nominal AS debet, 0 AS kredit, 'sp2d_tu' AS jenis, 'TU' AS kategori, CONCAT('0_', tanggal) AS grp, 0 AS sub_urut FROM uang_giros WHERE tipe = 'TU' AND tanggal >= '{$m}' AND tanggal <= '{$e}'";
            $queries[] = "SELECT belanja_tus.id, tanggal, no_bukti, kode_belanja AS rekening, belanja_tus.uraian, 0 AS debet, nilai AS kredit, 'belanja_tu' AS jenis, 'TU' AS kategori, CONCAT('1_', tanggal) AS grp, 0 AS sub_urut FROM belanja_tus JOIN rkas ON rkas.id = belanja_tus.rka_id WHERE tanggal >= '{$m}' AND tanggal <= '{$e}'";
            // Kompatibel: cek apakah kolom no_spp ada (hasil rename), jika tidak pakai no_bukti
            $nihilCol = \Schema::hasColumn('spp_spm_tu_nihils', 'no_spp') ? 'no_spp' : 'no_bukti';
            $queries[] = "SELECT id, tanggal, {$nihilCol} AS no_bukti, '' AS rekening, uraian, 0 AS debet, nilai_setor AS kredit, 'nihil_tu' AS jenis, 'TU' AS kategori, CONCAT('2_', tanggal) AS grp, 0 AS sub_urut FROM spp_spm_tu_nihils WHERE tanggal >= '{$m}' AND tanggal <= '{$e}'";
        }

        if ($filter === 'semua' || $filter === 'ls') {
            $queries[] = "SELECT belanja_ls.id, tanggal, no_bukti, '' AS rekening, CONCAT('SP2D LS - ', uraian) AS uraian, total_nilai AS debet, 0 AS kredit, 'sp2d_ls' AS jenis, 'LS' AS kategori, CONCAT('LS_', belanja_ls.id) AS grp, 0 AS sub_urut FROM belanja_ls WHERE tanggal >= '{$m}' AND tanggal <= '{$e}'";
            $queries[] = "SELECT belanja_ls.id, tanggal, no_bukti, '' AS rekening, CONCAT('Bayar LS - ', uraian) AS uraian, 0 AS debet, total_nilai AS kredit, 'belanja_ls' AS jenis, 'LS' AS kategori, CONCAT('LS_', belanja_ls.id) AS grp, 1 AS sub_urut FROM belanja_ls WHERE tanggal >= '{$m}' AND tanggal <= '{$e}'";
        }

        $data = collect();
        if (!empty($queries)) {
            $unionSql = implode(' UNION ALL ', $queries);
            $data = collect(DB::select("SELECT * FROM ({$unionSql}) AS combined ORDER BY tanggal ASC, grp ASC, sub_urut ASC"));
        }

        // === PAJAK ===
        $pajakGu = collect();
        $pajakKkpd = collect();
        $pajakTu = collect();
        $pajakLs = collect();

        if ($filter === 'semua' || $filter === 'gu') {
            $pajakGu = DB::table('pajaks')->join('belanjas', 'belanjas.id', '=', 'pajaks.belanja_id')
                ->where('belanjas.is_transfer', 1)
                ->where('belanjas.tanggal', '>=', $m)->where('belanjas.tanggal', '<=', $e)
                ->select('pajaks.*', 'belanjas.id as belanja_id')->get()->groupBy('belanja_id');
        }

        if ($filter === 'semua' || $filter === 'kkpd') {
            $pajakKkpd = DB::table('pajak_kkpds')->join('belanja_kkpds', 'belanja_kkpds.id', '=', 'pajak_kkpds.belanja_id')
                ->where('belanja_kkpds.is_transfer', 1)
                ->where('belanja_kkpds.tanggal', '>=', $m)->where('belanja_kkpds.tanggal', '<=', $e)
                ->select('pajak_kkpds.*', 'belanja_kkpds.id as belanja_id')->get()->groupBy('belanja_id');
        }

        if ($filter === 'semua' || $filter === 'tu') {
            $pajakTu = DB::table('pajak_tus')->join('belanja_tus', 'belanja_tus.id', '=', 'pajak_tus.belanja_tu_id')
                ->where('belanja_tus.tanggal', '>=', $m)->where('belanja_tus.tanggal', '<=', $e)
                ->select('pajak_tus.*', 'belanja_tus.id as belanja_id')->get()->groupBy('belanja_id');
        }

        if ($filter === 'semua' || $filter === 'ls') {
            $pajakLs = DB::table('pajak_ls')->join('belanja_ls', 'belanja_ls.id', '=', 'pajak_ls.belanja_ls_id')
                ->where('belanja_ls.tanggal', '>=', $m)->where('belanja_ls.tanggal', '<=', $e)
                ->select('pajak_ls.*', 'belanja_ls.id as belanja_id')->get()->groupBy('belanja_id');
        }

        return view('livewire.laporan.buku-kas-umum-all', [
            'data' => $data,
            'saldo' => $saldo,
            'pajakGu' => $pajakGu,
            'pajakKkpd' => $pajakKkpd,
            'pajakTu' => $pajakTu,
            'pajakLs' => $pajakLs,
            'penggunaAnggaran' => PengelolaKeuangan::where('jabatan', 'PENGGUNA ANGGARAN')->first(),
            'bendaharaPengeluaran' => PengelolaKeuangan::where('jabatan', 'BENDAHARA PENGELUARAN')->first(),
        ]);
    }
}
