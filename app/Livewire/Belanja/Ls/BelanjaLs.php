<?php

namespace App\Livewire\Belanja\Ls;

use App\Models\Rka;
use Livewire\Component;
use App\Models\Penerima;
use App\Models\SubKegiatan;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use App\Models\BelanjaLsDetails;
use Illuminate\Support\Facades\DB;
use App\Livewire\Laporan\LaporanLs;
use Illuminate\Support\Facades\Storage;
use App\Models\BelanjaLs as ModelBelanjaLs;

#[Title('Belanja LS')]
class BelanjaLs extends Component
{
    use WithPagination;

    public $search = '';
    public $paginate = 10;
    public $belanjas;

    // Header transaksi LS
    public $belanja_id, $no_bukti, $tanggal, $uraian;
    // Total nilai dihitung otomatis sebagai jumlah nilai transaksi LS (dari masing-masing RKAS)
    public $total_nilai;

    /**
     * Array $rkas akan menyimpan data detail RKAS yang telah difilter berdasarkan Sub Kegiatan.
     * Setiap item memiliki struktur:
     *  - id, nama_belanja, kode_belanja, anggaran,
     *  - initial_sisa (sisa anggaran awal: anggaran - total GU - total LS),
     *  - nilai (input transaksi LS, default 0)
     */
    public $rkas = [];

    public $isEdit = false;
    public $formVisible = false;
    public $open = false;

    // Pilihan Sub Kegiatan
    public $sub_kegiatan_id, $sub_kegiatan_kode, $sub_kegiatan_nama;

    // Properti tambahan
    public $belanjaId; // untuk delete_confirmation
    public $pathWord;
    public $pathpdf;

    // Listener untuk menangkap event dari modal Sub Kegiatan
    protected $listeners = ['subKegiatanSelected' => 'setSubKegiatan'];

    public function mount()
    {
        $this->resetInputFields();
    }

    public function render()
    {
        $tahun = session('tahun_anggaran', date('Y'));

        // Query header transaksi LS dengan relasi ke detail dan turunannya.
        $belanjas = ModelBelanjaLs::with(['details.rka.subKegiatan.kegiatan.program'])
            ->whereHas('details', function ($query) use ($tahun) {
                $query->whereHas('rka', function ($query2) use ($tahun) {
                    $query2->whereHas('subKegiatan', function ($query3) use ($tahun) {
                        $query3->whereHas('kegiatan', function ($query4) use ($tahun) {
                            $query4->whereHas('program', function ($query5) use ($tahun) {
                                $query5->where('tahun_anggaran', $tahun);
                            });
                        });
                    });
                });
            })
            ->where(function ($query) {
                $query->where('uraian', 'like', '%' . $this->search . '%')
                    ->orWhere('no_bukti', 'like', '%' . $this->search . '%');
            })
            ->orderBy('id', 'asc')
            ->paginate($this->paginate);

        return view('livewire.belanja.ls.belanja-ls', [
            'belanja'   => $belanjas,
            'penerimas' => Penerima::all(),
        ]);
    }


    public function setSubKegiatan($id, $kode, $nama)
    {
        $this->sub_kegiatan_id  = $id;
        $this->sub_kegiatan_kode = $kode;
        $this->sub_kegiatan_nama = $nama;

        $rkasData = Rka::where('sub_kegiatan_id', $id)->get();

        $this->rkas = $rkasData->map(function ($rka) {
            // Total transaksi GU dari tabel belanjas
            $totalGU = \App\Models\Belanja::where('rka_id', $rka->id)->sum('nilai');
            $totalKkpd = \App\Models\BelanjaKkpd::where('rka_id', $rka->id)->sum('nilai');
            // Total transaksi LS dari tabel belanja_ls_details
            $totalLS = BelanjaLsDetails::where('rka_id', $rka->id)->sum('nilai');
            $sisa = $rka->anggaran - $totalGU - $totalKkpd - $totalLS;
            return [
                'id'            => $rka->id,
                'nama_belanja'  => $rka->nama_belanja,
                'kode_belanja'  => $rka->kode_belanja,
                'anggaran'      => $rka->anggaran,
                'initial_sisa'  => $sisa, // simpan sisa anggaran awal
                'nilai'         => 0,     // default nilai transaksi LS
            ];
        })->toArray();

        $this->closeModal();
    }

    public function store()
    {
        // $lastNoBukti = ModelBelanjaLs::orderBy('no_bukti', 'desc')->first();
        // $newNoBukti = $lastNoBukti ? (int)$lastNoBukti->no_bukti + 1 : 1;
        // $formattedNoBukti = str_pad($newNoBukti, 4, '0', STR_PAD_LEFT);

        $validatedData = $this->validate([
            'no_bukti'         => 'required|unique:belanja_ls, no_bukti',
            'tanggal'         => 'required|date',
            'uraian'          => 'required',
            'sub_kegiatan_id' => 'required|exists:sub_kegiatans,id',
        ]);

        $totalTransaksi = array_sum(array_column($this->rkas, 'nilai'));
        if ($totalTransaksi <= 0) {
            return $this->addError('rkas', 'Masukkan nilai transaksi minimal pada salah satu RKAS.');
        }
        $this->total_nilai = $totalTransaksi;

        DB::beginTransaction();
        try {
            $belanjaLS = ModelBelanjaLs::create([
                'no_bukti'        => $this->no_bukti,
                'tanggal'         => $this->tanggal,
                'uraian'          => $this->uraian,
                'total_nilai'     => $this->total_nilai,
                'sub_kegiatan_id' => $this->sub_kegiatan_id,
            ]);

            foreach ($this->rkas as $rkaItem) {
                if ($rkaItem['nilai'] > 0) {
                    // Validasi: nilai tidak boleh melebihi sisa (dinamis: initial_sisa - input nilai)
                    // Karena input 'nilai' diupdate, cek jika nilai > initial_sisa.
                    if ($rkaItem['nilai'] > $rkaItem['initial_sisa']) {
                        return $this->addError('rkas', "Nilai untuk RKAS {$rkaItem['nama_belanja']} tidak boleh melebihi sisa anggaran (Rp " . number_format($rkaItem['initial_sisa'], 2, ',', '.') . ").");
                    }
                    BelanjaLsDetails::create([
                        'belanja_ls_id' => $belanjaLS->id,
                        'rka_id'        => $rkaItem['id'],
                        'nilai'         => $rkaItem['nilai'],
                    ]);
                    // Update anggaran RKAS secara opsional jika diperlukan
                    // $rka = Rka::find($rkaItem['id']);
                    // if ($rka) {
                    //     $rka->decrement('anggaran', $rkaItem['nilai']);
                    // }
                }
            }

            DB::commit();

            $this->js(<<<'JS'
                const Toast = Swal.mixin({
                    toast: true,
                    position: "top-end",
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.onmouseenter = Swal.stopTimer;
                        toast.onmouseleave = Swal.resumeTimer;
                    }
                });
                Toast.fire({
                    icon: "success",
                    title: "Data transaksi LS berhasil disimpan"
                });
                $('#belanjaModal').modal('hide');
            JS);

            $this->resetInputFields();
            $this->formVisible = false;
            $this->dispatch('refresh');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
            dd($e);
        }
    }

    public function edit($id)
    {
        $belanjaLS = ModelBelanjaLs::with('details')->findOrFail($id);
        $this->belanja_id   = $belanjaLS->id;
        $this->no_bukti     = $belanjaLS->no_bukti;
        $this->tanggal      = $belanjaLS->tanggal;
        $this->uraian       = $belanjaLS->uraian;
        $this->total_nilai  = $belanjaLS->total_nilai;
        $this->sub_kegiatan_id = $belanjaLS->sub_kegiatan_id;

        // Jika sub_kegiatan_id belum ada di header, ambil dari detail pertama
        if (!$this->sub_kegiatan_id && $belanjaLS->details->isNotEmpty()) {
            $firstDetail = $belanjaLS->details->first();
            $rka = Rka::find($firstDetail->rka_id);
            if ($rka) {
                $this->sub_kegiatan_id = $rka->sub_kegiatan_id;
            }
        }

        if ($this->sub_kegiatan_id) {
            $subKegiatan = SubKegiatan::find($this->sub_kegiatan_id);
            if ($subKegiatan) {
                $this->sub_kegiatan_kode = $subKegiatan->kode;
                $this->sub_kegiatan_nama = $subKegiatan->nama;
            }
        }

        // Muat ulang data RKAS berdasarkan sub kegiatan yang dipilih
        $this->setSubKegiatan($this->sub_kegiatan_id, $this->sub_kegiatan_kode, $this->sub_kegiatan_nama);

        // Override nilai pada tiap baris RKAS dengan data detail yang tersimpan
        foreach ($belanjaLS->details as $detail) {
            foreach ($this->rkas as &$rkaItem) {
                if ($rkaItem['id'] == $detail->rka_id) {
                    $rkaItem['nilai'] = $detail->nilai;
                }
            }
            unset($rkaItem);
        }

        $this->isEdit = true;
        $this->formVisible = true;

        $this->js(<<<'JS'
            $('#belanjaModal').modal('show');
        JS);
    }

    public function update()
    {
        $validatedData = $this->validate([
            'no_bukti'         => 'required|unique:belanja_ls,no_bukti,except,' . $this->no_bukti,
            'tanggal'          => 'required|date',
            'uraian'           => 'required',
            'sub_kegiatan_id'  => 'required|exists:sub_kegiatans,id',
        ]);

        $sumDetails = array_sum(array_column($this->rkas, 'nilai'));
        if ($sumDetails <= 0) {
            return $this->addError('rkas', 'Masukkan nilai transaksi minimal pada salah satu RKAS.');
        }
        $this->total_nilai = $sumDetails;

        // Validasi tiap baris: nilai tidak boleh melebihi sisa anggaran (dihitung berdasarkan initial_sisa)
        foreach ($this->rkas as $index => $rkaItem) {
            if ($rkaItem['nilai'] > $rkaItem['initial_sisa']) {
                return $this->addError('rkas', "Nilai untuk RKAS {$rkaItem['nama_belanja']} tidak boleh melebihi sisa anggaran (Rp " . number_format($rkaItem['initial_sisa'], 2, ',', '.') . ").");
            }
        }

        DB::beginTransaction();
        try {
            if ($belanjaLS = ModelBelanjaLs::find($this->belanja_id)) {
                $belanjaLS->update([
                    'no_bukti'        => $this->no_bukti,
                    'tanggal'         => $this->tanggal,
                    'uraian'          => $this->uraian,
                    'total_nilai'     => $this->total_nilai,
                    'sub_kegiatan_id' => $this->sub_kegiatan_id,
                ]);

                // Hapus detail lama
                BelanjaLsDetails::where('belanja_ls_id', $this->belanja_id)->delete();

                // Simpan detail baru
                foreach ($this->rkas as $rkaItem) {
                    if ($rkaItem['nilai'] > 0) {
                        BelanjaLsDetails::create([
                            'belanja_ls_id' => $this->belanja_id,
                            'rka_id'        => $rkaItem['id'],
                            'nilai'         => $rkaItem['nilai'],
                        ]);
                    }
                }
            }
            DB::commit();

            $this->resetInputFields();
            $this->isEdit = false;
            $this->formVisible = false;
            $this->js(<<<'JS'
                const Toast = Swal.mixin({
                    toast: true,
                    position: "top-end",
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.onmouseenter = Swal.stopTimer;
                        toast.onmouseleave = Swal.resumeTimer;
                    }
                });
                Toast.fire({
                    icon: "success",
                    title: "Data transaksi LS berhasil diupdate"
                });
                $('#belanjaModal').modal('hide');
            JS);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function delete_confirmation($id)
    {
        $this->belanjaId = $id;
        $this->js(<<<'JS'
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data ini akan dihapus dan proses ini tidak dapat dikembalikan.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, delete it!"
            }).then((result) => {
                if (result.isConfirmed) {
                    $wire.delete()
                }
            });
        JS);
    }

    public function delete()
    {
        ModelBelanjaLs::destroy($this->belanjaId);
        $this->js(<<<'JS'
            const Toast = Swal.mixin({
                toast: true,
                position: "top-end",
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.onmouseenter = Swal.stopTimer;
                    toast.onmouseleave = Swal.resumeTimer;
                }
            });
            Toast.fire({
                icon: "error",
                title: "Data berhasil dihapus"
            });
        JS);
    }

    private function resetInputFields()
    {
        $this->no_bukti = '';
        $this->tanggal = null;
        $this->uraian = '';
        $this->total_nilai = null;
        $this->rkas = [];
        $this->isEdit = false;
        $this->belanja_id = null;
        $this->formVisible = false;
        $this->sub_kegiatan_id = null;
        $this->sub_kegiatan_kode = null;
        $this->sub_kegiatan_nama = null;
    }

    public function closeForm()
    {
        $this->resetInputFields();
        $this->isEdit = false;
        $this->formVisible = false;
        $this->js(<<<'JS'
            $('#belanjaModal').modal('hide');
        JS);
    }

    public function openForm()
    {
        $this->resetInputFields();
        $this->isEdit = false;
        $this->formVisible = true;
        $this->dispatch('refresh');
    }

    public function openModal()
    {
        $this->js(<<<'JS'
            $('#subkegiatanModal').modal('show');
        JS);
        $this->dispatch('refresh');
    }

    public function closeModal()
    {
        $this->js(<<<'JS'
            $('#subkegiatanModal').modal('hide');
        JS);
    }
    public function closeModalPdf()
    {
        $this->js(<<<'JS'
        $('#viewBelanja').modal("hide")
    JS);
        Storage::disk('local')->delete('public/reports/ls/ls_' . $this->pathWord);
        Storage::disk('local')->delete('public/reports/ls/ls_' . $this->pathpdf);
    }

    public function printTaiLs($id)
    {
        $data = new LaporanLs;
        $this->pathWord = $data->getLaporanlsPaths($id)['word_path'];
        $this->pathpdf = $data->getLaporanlsPaths($id)['pdf_path'];
        $this->js(<<<'JS'
                $('#viewBelanja').modal("show")
            JS);
    }

    public function downloadTaiLs($id)
    {
        $data = new LaporanLs;

        return $data->downloadLaporanLs($id);
    }
}
