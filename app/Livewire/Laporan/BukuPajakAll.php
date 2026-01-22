<?php

namespace App\Livewire\Laporan;

use Livewire\Component;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

#[Title('Buku Pajak All')]
class BukuPajakAll extends Component
{
    public $tanggal_awal = '';
    public $tanggal_akhir = '';
    public $tanggal_cetak = '';
    public $jenis = 'ALL';
    public $laporan = [];
    public $ppnTotal = 0;
    public $pph21Total = 0;
    public $pph22Total = 0;
    public $pph23Total = 0;
    public $pajakPeriodeIni = 0;
    public $saldoAwal = 0;
    public $saldoAwalPemotongan = 0;
    public $saldoAwalPenyetoran = 0;
    public $bendahara;
    public $penggunaAnggaran;

    public function updatedTanggalAwal()
    {
        if ($this->tanggal_awal && $this->tanggal_akhir) {
            $this->ambilLaporan();
        }
    }

    public function updatedTanggalAkhir()
    {
        if ($this->tanggal_awal && $this->tanggal_akhir) {
            $this->ambilLaporan();
        }
    }

    public function updatedJenis()
    {
        if ($this->tanggal_awal && $this->tanggal_akhir) {
            $this->ambilLaporan();
        }
    }

    public function ambilLaporan()
    {
        if (!$this->tanggal_awal || !$this->tanggal_akhir) {
            $this->resetData();
            return;
        }

        if ($this->tanggal_awal > $this->tanggal_akhir) {
            $this->resetData();
            return;
        }

        $params = [
            $this->tanggal_awal,
            $this->tanggal_akhir,
            $this->tanggal_awal,
            $this->tanggal_akhir,
            $this->tanggal_awal,
            $this->tanggal_akhir,
            $this->tanggal_awal,
            $this->tanggal_akhir,
            $this->tanggal_awal,
            $this->tanggal_akhir,
            $this->tanggal_awal,
            $this->tanggal_akhir
        ];

        $filterJenis = "";
        $paramsJenis = [];

        if ($this->jenis != 'ALL') {
            $filterJenis = "AND p.jenis_pajak = ?";
            // We need to inject the genre parameter for each select part of the union
            // There are 3 tables * 2 parts (Utama, Setor) = 6 queries
            // Each query takes dates, dates. If ALL, just dates.
            // If specific type, dates, type, dates, type.
        }

        // Simpler approach: Build the query string dynamically

        $queries = [];
        $bindings = [];

        // 1. Pajak Giro (pajaks join belanjas)
        $queries[] = $this->buildQuery('pajaks', 'belanjas', 'belanja_id', 'TBP - ');

        // 2. Pajak KKPD (pajak_kkpds join belanja_kkpds)
        $queries[] = $this->buildQuery('pajak_kkpds', 'belanja_kkpds', 'belanja_id', 'TBP - ');

        // 3. Pajak LS (pajak_ls join belanja_ls)
        $queries[] = $this->buildQuery('pajak_ls', 'belanja_ls', 'belanja_ls_id', 'TBP - ');

        $unionQuery = implode(" UNION ALL ", $queries);
        $finalQuery = "SELECT * FROM ({$unionQuery}) AS laporan ORDER BY tgl_bukti, no_bukti";

        $this->laporan = DB::select($finalQuery, $this->getBindingsForUnion());

        $this->hitungTotalPajak();
        $this->hitungPajakPeriodeIni();
        $this->hitungSaldoAwal();
    }

    private function buildQuery($tablePajak, $tableBelanja, $fk, $prefixNoBukti)
    {
        $jenisCondition = $this->jenis != 'ALL' ? "AND p.jenis_pajak = ?" : "";

        // Note: For ID Billing/NTPN, structure might vary slightly but based on previous files:
        // Giro: p.no_billing, p.ntpn, p.ntb
        // KKPD: p.no_billing (LEFT/RIGHT logic in previous file), p.ntpn? 
        // Let's standardize the formatting logic based on the specific previous files.
        // Actually, better to explicitly write them out to match exact previous logic.

        // Logic for Giro
        if ($tablePajak == 'pajaks') {
            return "
                SELECT
                    p.id AS pajak_id,
                    b.tanggal AS tgl_bukti,
                    CONCAT('{$prefixNoBukti}', b.no_bukti) AS no_bukti,
                    CONCAT('Penerimaan PFK (Giro) - ', p.jenis_pajak, ' - ID BILLING : ', ' (', IFNULL((p.no_billing), ''), ')') AS uraian,
                    p.nominal AS pemotongan,
                    NULL AS penyetoran
                FROM {$tablePajak} p
                JOIN {$tableBelanja} b ON p.{$fk} = b.id
                WHERE b.tanggal BETWEEN ? AND ? AND YEAR(b.tanggal) = ? {$jenisCondition}
                UNION ALL
                SELECT
                    p.id AS pajak_id,
                    b.tanggal AS tgl_bukti,
                    CONCAT('{$prefixNoBukti}', b.no_bukti) AS no_bukti,
                    CONCAT('Pengeluaran PFK (Giro) - ', p.jenis_pajak, ' - NTPN : ' ,' (' , IFNULL(p.ntpn, '-'), ')' ,' - NTB : ' ,' (' , IFNULL(p.ntb, '-'), ')' ) AS uraian,
                    NULL AS pemotongan,
                    p.nominal AS penyetoran
                FROM {$tablePajak} p
                JOIN {$tableBelanja} b ON p.{$fk} = b.id
                WHERE b.tanggal BETWEEN ? AND ? AND YEAR(b.tanggal) = ? {$jenisCondition}
            ";
        }

        if ($tablePajak == 'pajak_kkpds') {
            return "
                SELECT
                    p.id AS pajak_id,
                    b.tanggal AS tgl_bukti,
                    CONCAT('{$prefixNoBukti}', b.no_bukti) AS no_bukti,
                    CONCAT('Penerimaan PFK (KKPD) - ', p.jenis_pajak, ' - ID BILLING : ', ' (', IFNULL(LEFT(p.no_billing, 15), ''), ')') AS uraian,
                    p.nominal AS pemotongan,
                    NULL AS penyetoran
                FROM {$tablePajak} p
                JOIN {$tableBelanja} b ON p.{$fk} = b.id
                WHERE b.tanggal BETWEEN ? AND ? AND YEAR(b.tanggal) = ? {$jenisCondition}
                UNION ALL
                SELECT
                    p.id AS pajak_id,
                    b.tanggal AS tgl_bukti,
                    CONCAT('{$prefixNoBukti}', b.no_bukti) AS no_bukti,
                    CONCAT('Pengeluaran PFK (KKPD) - ', p.jenis_pajak, ' - NTPN : ' ,' (' , IFNULL(RIGHT(p.no_billing, 15), '-'), ')' ,' - NTB : (-)' ) AS uraian,
                    NULL AS pemotongan,
                    p.nominal AS penyetoran
                FROM {$tablePajak} p
                JOIN {$tableBelanja} b ON p.{$fk} = b.id
                WHERE b.tanggal BETWEEN ? AND ? AND YEAR(b.tanggal) = ? {$jenisCondition}
            ";
        } else {
            // Logic for LS (which now has ntpn and ntb columns)
            $sourceName = 'LS';
            return "
                SELECT
                    p.id AS pajak_id,
                    b.tanggal AS tgl_bukti,
                    CONCAT('{$prefixNoBukti}', b.no_bukti) AS no_bukti,
                    CONCAT('Penerimaan PFK ({$sourceName}) - ', p.jenis_pajak, ' - ID BILLING : ', ' (', IFNULL(LEFT(p.no_billing, 15), ''), ')') AS uraian,
                    p.nominal AS pemotongan,
                    NULL AS penyetoran
                FROM {$tablePajak} p
                JOIN {$tableBelanja} b ON p.{$fk} = b.id
                WHERE b.tanggal BETWEEN ? AND ? AND YEAR(b.tanggal) = ? {$jenisCondition}
                UNION ALL
                SELECT
                    p.id AS pajak_id,
                    b.tanggal AS tgl_bukti,
                    CONCAT('{$prefixNoBukti}', b.no_bukti) AS no_bukti,
                    CONCAT('Pengeluaran PFK ({$sourceName}) - ', p.jenis_pajak, ' - NTPN : ' ,' (' , IFNULL(p.ntpn, '-'), ')' ,' - NTB : ' ,' (' , IFNULL(p.ntb, '-'), ')' ) AS uraian,
                    NULL AS pemotongan,
                    p.nominal AS penyetoran
                FROM {$tablePajak} p
                JOIN {$tableBelanja} b ON p.{$fk} = b.id
                WHERE b.tanggal BETWEEN ? AND ? AND YEAR(b.tanggal) = ? {$jenisCondition}
            ";
        }
    }

    private function getBindingsForUnion()
    {
        $bindings = [];
        // 3 tables * 2 subqueries = 6 chunks
        for ($i = 0; $i < 6; $i++) {
            $bindings[] = $this->tanggal_awal;
            $bindings[] = $this->tanggal_akhir;
            $bindings[] = session('tahun_anggaran');
            if ($this->jenis != 'ALL') {
                $bindings[] = $this->jenis;
            }
        }
        return $bindings;
    }

    private function hitungTotalPajak()
    {
        $tables = [
            ['pajaks', 'belanjas', 'id'],
            ['pajak_kkpds', 'belanja_kkpds', 'id'],
            ['pajak_ls', 'belanja_ls', 'id']
        ];

        $this->ppnTotal = 0;
        $this->pph21Total = 0;
        $this->pph22Total = 0;
        $this->pph23Total = 0;

        foreach ($tables as $t) {
            $tablePajak = $t[0];
            $tableBelanja = $t[1];
            // Foreign key usually matches table name logic in join, but here let's rely on explicit defs or just manual code
            $fk = $tablePajak == 'pajaks' ? 'belanja_id' : ($tablePajak == 'pajak_ls' ? 'belanja_ls_id' : 'belanja_id');

            foreach (['PPN', 'PPh 21', 'PPh 22', 'PPh 23'] as $type) {
                $val = DB::table($tablePajak)
                    ->join($tableBelanja, "$tablePajak.$fk", '=', "$tableBelanja.id")
                    ->where("$tablePajak.jenis_pajak", $type)
                    ->whereBetween("$tableBelanja.tanggal", [$this->tanggal_awal, $this->tanggal_akhir])
                    ->whereYear("$tableBelanja.tanggal", session('tahun_anggaran'))
                    ->sum("$tablePajak.nominal");

                if ($type == 'PPN')
                    $this->ppnTotal += $val;
                elseif ($type == 'PPh 21')
                    $this->pph21Total += $val;
                elseif ($type == 'PPh 22')
                    $this->pph22Total += $val;
                elseif ($type == 'PPh 23')
                    $this->pph23Total += $val;
            }
        }
    }

    private function hitungPajakPeriodeIni()
    {
        $this->pajakPeriodeIni = 0;
        $tables = [
            ['pajaks', 'belanjas'],
            ['pajak_kkpds', 'belanja_kkpds'],
            ['pajak_ls', 'belanja_ls']
        ];

        foreach ($tables as $t) {
            $tablePajak = $t[0];
            $tableBelanja = $t[1];
            $fk = $tablePajak == 'pajaks' ? 'belanja_id' : ($tablePajak == 'pajak_ls' ? 'belanja_ls_id' : 'belanja_id');

            $query = DB::table($tablePajak)
                ->join($tableBelanja, "$tablePajak.$fk", '=', "$tableBelanja.id")
                ->whereBetween("$tableBelanja.tanggal", [$this->tanggal_awal, $this->tanggal_akhir])
                ->whereYear("$tableBelanja.tanggal", session('tahun_anggaran'));

            if ($this->jenis != 'ALL') {
                $query->where("$tablePajak.jenis_pajak", $this->jenis);
            }

            $this->pajakPeriodeIni += $query->sum("$tablePajak.nominal");
        }
    }

    private function hitungSaldoAwal()
    {
        $tahun = Carbon::parse($this->tanggal_awal)->year;
        $awalTahun = $tahun . '-01-01';
        $sebelumTanggalAwal = Carbon::parse($this->tanggal_awal)->subDay()->format('Y-m-d');

        $this->saldoAwalPemotongan = 0;
        $this->saldoAwalPenyetoran = 0;
        $this->saldoAwal = 0;

        if ($sebelumTanggalAwal < $awalTahun) {
            return;
        }

        // We can reuse buildQuery logic but substituting dates
        $queries = [];
        $bindings = [];
        // We need new bindings for the saldo awal check
        // 3 tables * 2 subqueries = 6 chunks
        for ($i = 0; $i < 6; $i++) {
            $bindings[] = $awalTahun;
            $bindings[] = $sebelumTanggalAwal;
            $bindings[] = session('tahun_anggaran');
            if ($this->jenis != 'ALL') {
                $bindings[] = $this->jenis;
            }
        }

        $queries[] = $this->buildQuery('pajaks', 'belanjas', 'belanja_id', 'TBP - ');
        $queries[] = $this->buildQuery('pajak_kkpds', 'belanja_kkpds', 'belanja_id', 'TBP - ');
        $queries[] = $this->buildQuery('pajak_ls', 'belanja_ls', 'belanja_ls_id', 'TBP - ');

        $unionQuery = implode(" UNION ALL ", $queries);
        $finalQuery = "SELECT * FROM ({$unionQuery}) AS saldo_awal";

        $dataSaldoAwal = DB::select($finalQuery, $bindings);

        $this->saldoAwalPemotongan = collect($dataSaldoAwal)->sum('pemotongan');
        $this->saldoAwalPenyetoran = collect($dataSaldoAwal)->sum('penyetoran');
        $this->saldoAwal = $this->saldoAwalPemotongan - $this->saldoAwalPenyetoran;
    }

    private function resetData()
    {
        $this->laporan = [];
        $this->ppnTotal = 0;
        $this->pph21Total = 0;
        $this->pph22Total = 0;
        $this->pph23Total = 0;
        $this->pajakPeriodeIni = 0;
        $this->saldoAwal = 0;
        $this->saldoAwalPemotongan = 0;
        $this->saldoAwalPenyetoran = 0;
    }

    public function exportPdf()
    {
        if (!$this->laporan || count($this->laporan) == 0) {
            return;
        }

        $totalPemotonganPeriode = collect($this->laporan)->sum('pemotongan');
        $totalPenyetoranPeriode = collect($this->laporan)->sum('penyetoran');
        $saldoAkhir = $this->saldoAwal + $totalPemotonganPeriode - $totalPenyetoranPeriode;

        $bendahara = \App\Models\PengelolaKeuangan::where('jabatan', 'BENDAHARA PENGELUARAN')->first();
        $penggunaAnggaran = \App\Models\PengelolaKeuangan::where('jabatan', 'PENGGUNA ANGGARAN')->first();

        $data = [
            'laporan' => $this->laporan,
            'tanggal_awal' => $this->tanggal_awal,
            'tanggal_akhir' => $this->tanggal_akhir,
            'tanggal_cetak' => $this->tanggal_cetak ?: now()->format('Y-m-d'),
            'jenis' => $this->jenis,
            'ppnTotal' => $this->ppnTotal,
            'pph21Total' => $this->pph21Total,
            'pph22Total' => $this->pph22Total,
            'pph23Total' => $this->pph23Total,
            'pajakPeriodeIni' => $this->pajakPeriodeIni,
            'saldoAwal' => $this->saldoAwal,
            'saldoAwalPemotongan' => $this->saldoAwalPemotongan,
            'saldoAwalPenyetoran' => $this->saldoAwalPenyetoran,
            'saldoAkhir' => $saldoAkhir,
            'bendahara' => $bendahara,
            'penggunaAnggaran' => $penggunaAnggaran,
        ];

        $pdf = Pdf::loadView('livewire.laporan.buku-pajak-all-pdf', $data);
        $pdf->setPaper('a4', 'portrait');

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, 'Laporan-Pajak-Gabungan-' . now()->format('YmdHis') . '.pdf');
    }

    public function render()
    {
        $totalPemotonganPeriode = collect($this->laporan)->sum('pemotongan');
        $totalPenyetoranPeriode = collect($this->laporan)->sum('penyetoran');
        $saldoAkhir = $this->saldoAwal + $totalPemotonganPeriode - $totalPenyetoranPeriode;

        $this->bendahara = \App\Models\PengelolaKeuangan::where('jabatan', 'BENDAHARA PENGELUARAN')->first();
        $this->penggunaAnggaran = \App\Models\PengelolaKeuangan::where('jabatan', 'PENGGUNA ANGGARAN')->first();

        return view('livewire.laporan.buku-pajak-all', [
            'laporan' => $this->laporan,
            'ppnTotal' => $this->ppnTotal,
            'pph21Total' => $this->pph21Total,
            'pph22Total' => $this->pph22Total,
            'pph23Total' => $this->pph23Total,
            'pajakPeriodeIni' => $this->pajakPeriodeIni,
            'saldoAwal' => $this->saldoAwal,
            'saldoAwalPemotongan' => $this->saldoAwalPemotongan,
            'saldoAwalPenyetoran' => $this->saldoAwalPenyetoran,
            'saldoAkhir' => $saldoAkhir
        ]);
    }
}
