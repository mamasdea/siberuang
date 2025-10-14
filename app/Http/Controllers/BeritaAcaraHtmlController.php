<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Kontrak;
use Illuminate\Http\Request;
use App\Models\KontrakRealisasi;
use App\Models\PengelolaKeuangan;
use Illuminate\Support\Facades\View;

class BeritaAcaraHtmlController extends Controller
{
    /** Jenis BA yang diizinkan dan judulnya */
    private array $mapTitles = [
        'pemeriksaan'   => 'Berita Acara Pemeriksaan',
        'serah_terima'  => 'Berita Acara Serah Terima Barang/Jasa',
        'pekerjaan'     => 'Berita Acara Serah Terima Pekerjaan',
        'penerimaan'    => 'Berita Acara Penerimaan',
        'administratif' => 'Berita Acara Administratif',
        'pembayaran'    => 'Berita Acara Pembayaran',
    ];

    /**
     * URL: /kontrak/{kontrak}/realisasi/{realisasi}/ba/{jenis}
     * - {realisasi} boleh 0 untuk PREVIEW (belum tersimpan)
     */


    public function printAll(Request $request, Kontrak $kontrak, int $realisasi)
    {
        // === Muat data realisasi & relasi penting ===
        $realisasiModel = KontrakRealisasi::with([
            'kontrak.subKegiatan',
            'kontrak.rincians',
            'items',
            'beritaAcaras'
        ])
            ->where('id', $realisasi)
            ->where('kontrak_id', $kontrak->id)
            ->firstOrFail();

        // === Ambil item sesuai tipe (termin / sekaligus) ===
        $items = $realisasiModel->items;
        if ($realisasiModel->tipe === 'sekaligus' && $items->isEmpty()) {
            $items = $realisasiModel->kontrak->rincians->map(fn($rc) => (object) [
                'nama_barang' => $rc->nama_barang,
                'harga'       => (float) $rc->harga,
                'kuantitas'   => (float) $rc->kuantitas,
                'satuan'      => $rc->satuan,
            ]);
        }

        // === Ambil nama & NIP pejabat dari tabel pengelola_keuangans ===
        $pejabat = \App\Models\PengelolaKeuangan::whereIn('jabatan', ['PENGGUNA ANGGARAN', 'PENGURUS BARANG'])
            ->get(['jabatan', 'nama', 'nip'])
            ->keyBy('jabatan');

        $penggunaAnggaran    = $pejabat['PENGGUNA ANGGARAN']->nama ?? '-';
        $pengurusBarang      = $pejabat['PENGURUS BARANG']->nama ?? '-';
        $NIPpenggunaAnggaran = $pejabat['PENGGUNA ANGGARAN']->nip ?? '-';
        $NIPpengurusBarang   = $pejabat['PENGURUS BARANG']->nip ?? '-';

        // === Urutan BA yang diinginkan ===
        $order = [
            'pemeriksaan',
            'serah_terima',
            'pekerjaan',
            'administratif',
            'penerimaan',
            'pembayaran'
        ];

        // === Filter BA yang sudah punya nomor & tanggal ===
        $filteredBA = $realisasiModel->beritaAcaras
            ->filter(fn($ba) => filled($ba->nomor) && filled($ba->tanggal))
            ->sortBy(fn($ba) => array_search(strtolower($ba->jenis), $order))
            ->values();

        if ($filteredBA->isEmpty()) {
            return back()->with('error', 'Tidak ada Berita Acara yang memiliki nomor & tanggal untuk dicetak.');
        }

        // === Render setiap BA dalam urutan yang benar ===
        $pages = $filteredBA->map(function ($ba) use (
            $kontrak,
            $realisasiModel,
            $items,
            $penggunaAnggaran,
            $pengurusBarang,
            $NIPpenggunaAnggaran,
            $NIPpengurusBarang
        ) {
            $jenis = strtolower($ba->jenis);
            $view  = View::exists("ba.$jenis") ? "ba.$jenis" : "ba.default";

            return view($view, [
                'title'      => $this->mapTitles[$jenis] ?? ucfirst($jenis),
                'jenis'      => $jenis,
                'auto'       => false,
                'kontrak'    => $kontrak,
                'realisasi'  => $realisasiModel,
                'items'      => $items,
                'ba_nomor'   => $ba->nomor,
                'ba_tanggal' => $ba->tanggal ? \Carbon\Carbon::parse($ba->tanggal) : now(),
                'today'      => now(),
                'namaPA'     => $penggunaAnggaran,
                'namaPB'     => $pengurusBarang,
                'nipPA'      => $NIPpenggunaAnggaran,
                'nipPB'      => $NIPpengurusBarang,
            ])->render();
        });

        // === Gabungkan semua BA ke 1 tampilan ===
        return view('ba.print_all', ['pages' => $pages]);
    }


    public function show(Request $request, Kontrak $kontrak, int $realisasi, string $jenis)
    {
        $jenis = strtolower($jenis);
        abort_unless(array_key_exists($jenis, $this->mapTitles), 404, 'Jenis BA tidak dikenal');

        // Ambil realisasi jika > 0, dan pastikan milik kontrak yang sama
        if ($realisasi > 0) {
            $realisasiModel = KontrakRealisasi::with([
                'kontrak.subKegiatan',
                'kontrak.rincians',
                'items',
                'beritaAcaras'
            ])
                ->where('id', $realisasi)
                ->where('kontrak_id', $kontrak->id)
                ->firstOrFail();
        } else {
            // MODE PREVIEW: buat objek in-memory minimal
            $realisasiModel = new KontrakRealisasi([
                'kontrak_id'    => $kontrak->id,
                'tipe'          => 'termin',
                'termin_ke'     => null,
                'tanggal'       => now(),
                'periode'       => $request->query('periode'),
                'progres_fisik' => $request->query('progres_fisik'),
                'nominal'       => 0,
            ]);
            $realisasiModel->setRelation('kontrak', $kontrak->loadMissing(['subKegiatan', 'rincians']));
            $realisasiModel->setRelation('items', collect());
            $realisasiModel->setRelation('beritaAcaras', collect());
        }

        // BA tersimpan (jika ada), atau ambil dari query (preview)
        $ba = $realisasiModel->beritaAcaras->firstWhere('jenis', $jenis);
        $nomor   = $ba->nomor   ?? $request->query('nomor');
        $tanggal = $ba->tanggal ?? $request->query('tanggal');
        $tanggal = $tanggal ? Carbon::parse($tanggal) : null;

        $auto  = $request->boolean('auto', false);
        $view  = View::exists("ba.$jenis") ? "ba.$jenis" : "ba.default";

        // === Ambil nama pejabat dari tabel pengelola_keuangans ===
        $penggunaAnggaran = PengelolaKeuangan::where('jabatan', 'PENGGUNA ANGGARAN')->value('nama');
        $pengurusBarang = PengelolaKeuangan::where('jabatan', 'PENGURUS BARANG')->value('nama');
        $NIPpenggunaAnggaran = PengelolaKeuangan::where('jabatan', 'PENGGUNA ANGGARAN')->value('nip');
        $NIPpengurusBarang = PengelolaKeuangan::where('jabatan', 'PENGURUS BARANG')->value('nip');

        return view($view, [
            'title'      => $this->mapTitles[$jenis],
            'jenis'      => $jenis,
            'auto'       => $auto,
            'kontrak'    => $kontrak,
            'realisasi'  => $realisasiModel,
            'ba_nomor'   => $nomor,
            'ba_tanggal' => $tanggal,   // Carbon|null
            'today'      => Carbon::now(),
            'namaPA' => $penggunaAnggaran,
            'namaPB'   => $pengurusBarang,
            'nipPA' => $NIPpenggunaAnggaran,
            'nipPB'   => $NIPpengurusBarang,

            // Helpers ke view
            'fmtDate' => function ($d) {
                return $d ? Carbon::parse($d)->translatedFormat('d F Y') : '-';
            },
            'idr' => function ($n) {
                return 'Rp ' . number_format((float)$n, 2, ',', '.');
            },
            'roman' => function (int $n) {
                $map = ['M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40, 'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1];
                $r = '';
                foreach ($map as $s => $v) {
                    while ($n >= $v) {
                        $r .= $s;
                        $n -= $v;
                    }
                }
                return $r ?: 'I';
            },
        ]);
    }
}
