<?php

namespace App\Livewire\Belanja\Tu;

use App\Models\Rka;
use App\Models\Belanja;
use App\Models\BelanjaKkpd;
use App\Models\BelanjaLsDetails;
use App\Models\SppSpmTu;
use App\Models\SppSpmTuDetail;
use App\Models\UangGiro;
use App\Models\SubKegiatan;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

#[Title('SPP-SPM TU')]
class SppSpmTuManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $paginate = 10;

    // Form fields
    public $spp_spm_tu_id;
    public $no_bukti;
    public $no_spm_sipd;
    public $tanggal;
    public $uraian;
    public $total_nilai = 0;
    public $sub_kegiatan_id;
    public $sub_kegiatan_kode;
    public $sub_kegiatan_nama;
    public $rkas = [];
    public $isEdit = false;
    public $tahunTransaksi;
    public $deleteId;
    public $pathWord;
    public $pathpdf;

    // SP2D modal
    public $sp2dId;
    public $sp2d_no;
    public $sp2d_tanggal;

    // Listener untuk menangkap event dari modal Sub Kegiatan
    protected $listeners = ['subKegiatanSelected' => 'setSubKegiatan'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPaginate()
    {
        $this->resetPage();
    }

    public function updatedTanggal($value)
    {
        $this->tahunTransaksi = $value ? date('Y', strtotime($value)) : date('Y');
    }

    public function mount()
    {
        $this->resetInputFields();
    }

    public function render()
    {
        $tahun = session('tahun_anggaran', date('Y'));

        $query = SppSpmTu::with(['details.rka', 'belanjaTus', 'spjTu', 'nihil'])
            ->where('tahun_bukti', $tahun)
            ->where(function ($query) {
                $query->where('no_bukti', 'like', '%' . $this->search . '%')
                    ->orWhere('uraian', 'like', '%' . $this->search . '%');
            });

        $totalTransaksi = (clone $query)->count();
        $totalNominal = (clone $query)->sum('total_nilai');

        $sppSpmTus = $query->orderBy('no_bukti', 'asc')
            ->paginate($this->paginate);

        return view('livewire.belanja.tu.spp-spm-tu-manager', [
            'sppSpmTus' => $sppSpmTus,
            'totalTransaksi' => $totalTransaksi,
            'totalNominal' => $totalNominal,
        ]);
    }

    public function setSubKegiatan($id, $kode, $nama)
    {
        $this->sub_kegiatan_id = $id;
        $this->sub_kegiatan_kode = $kode;
        $this->sub_kegiatan_nama = $nama;

        $rkasData = Rka::where('sub_kegiatan_id', $id)->get();

        $this->rkas = $rkasData->map(function ($rka) {
            // Total transaksi GU dari tabel belanjas
            $totalGU = Belanja::where('rka_id', $rka->id)->sum('nilai');
            // Total transaksi KKPD
            $totalKkpd = BelanjaKkpd::where('rka_id', $rka->id)->sum('nilai');
            // Total transaksi LS dari tabel belanja_ls_details
            $totalLS = BelanjaLsDetails::where('rka_id', $rka->id)->sum('nilai');
            // Total transaksi TU dari tabel spp_spm_tu_details
            // Saat edit, exclude detail dari record yang sedang diedit agar sisa anggaran tidak terhitung dua kali
            $totalTU = SppSpmTuDetail::where('rka_id', $rka->id)
                ->when($this->spp_spm_tu_id, function ($query) {
                    return $query->where('spp_spm_tu_id', '!=', $this->spp_spm_tu_id);
                })
                ->sum('nilai');
            $sisa = $rka->anggaran - $totalGU - $totalKkpd - $totalLS - $totalTU;
            return [
                'id' => $rka->id,
                'nama_belanja' => $rka->nama_belanja,
                'kode_belanja' => $rka->kode_belanja,
                'anggaran' => $rka->anggaran,
                'initial_sisa' => $sisa,
                'nilai' => 0,
            ];
        })->toArray();

        $this->closeModal();
    }

    public function store()
    {
        $tahunTransaksi = $this->tanggal ? date('Y', strtotime($this->tanggal)) : date('Y');

        $this->validate([
            'no_bukti' => [
                'required',
                'string',
                'min:4',
                Rule::unique('spp_spm_tus', 'no_bukti')->where(function ($query) use ($tahunTransaksi) {
                    return $query->where('tahun_bukti', $tahunTransaksi);
                }),
            ],
            'tanggal' => 'required|date',
            'sub_kegiatan_id' => 'required|exists:sub_kegiatans,id',
        ]);

        $totalTransaksi = array_sum(array_column($this->rkas, 'nilai'));
        if ($totalTransaksi <= 0) {
            return $this->addError('rkas', 'Masukkan nilai transaksi minimal pada salah satu RKAS.');
        }
        $this->total_nilai = $totalTransaksi;

        // Validasi tiap baris: nilai tidak boleh melebihi sisa anggaran
        foreach ($this->rkas as $rkaItem) {
            if ($rkaItem['nilai'] > 0 && $rkaItem['nilai'] > $rkaItem['initial_sisa']) {
                return $this->addError('rkas', "Nilai untuk RKAS {$rkaItem['nama_belanja']} tidak boleh melebihi sisa anggaran (Rp " . number_format($rkaItem['initial_sisa'], 2, ',', '.') . ").");
            }
        }

        DB::beginTransaction();
        try {
            $sppSpmTu = SppSpmTu::create([
                'no_bukti' => $this->no_bukti,
                'no_spm_sipd' => $this->no_spm_sipd ?: null,
                'tanggal' => $this->tanggal,
                'tahun_bukti' => $tahunTransaksi,
                'uraian' => $this->uraian,
                'total_nilai' => $this->total_nilai,
                'sub_kegiatan_id' => $this->sub_kegiatan_id,
            ]);

            foreach ($this->rkas as $rkaItem) {
                if ($rkaItem['nilai'] > 0) {
                    SppSpmTuDetail::create([
                        'spp_spm_tu_id' => $sppSpmTu->id,
                        'rka_id' => $rkaItem['id'],
                        'nilai' => $rkaItem['nilai'],
                    ]);
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
                    title: "Data SPP-SPM TU berhasil disimpan"
                });
                $('#sppSpmTuModal').modal('hide');
            JS);

            $this->resetInputFields();
            $this->dispatch('refresh');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $sppSpmTu = SppSpmTu::with('details')->findOrFail($id);
        $this->spp_spm_tu_id = $sppSpmTu->id;
        $this->no_bukti = $sppSpmTu->no_bukti;
        $this->no_spm_sipd = $sppSpmTu->no_spm_sipd;
        $this->tanggal = $sppSpmTu->tanggal;
        $this->tahunTransaksi = $sppSpmTu->tahun_bukti ?? date('Y');
        $this->uraian = $sppSpmTu->uraian;
        $this->total_nilai = $sppSpmTu->total_nilai;
        $this->sub_kegiatan_id = $sppSpmTu->sub_kegiatan_id;

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
        foreach ($sppSpmTu->details as $detail) {
            foreach ($this->rkas as &$rkaItem) {
                if ($rkaItem['id'] == $detail->rka_id) {
                    $rkaItem['nilai'] = $detail->nilai;
                }
            }
            unset($rkaItem);
        }

        $this->isEdit = true;

        $this->js(<<<'JS'
            $('#sppSpmTuModal').modal('show');
        JS);
    }

    public function update()
    {
        $tahunTransaksi = $this->tanggal ? date('Y', strtotime($this->tanggal)) : date('Y');

        $this->validate([
            'no_bukti' => [
                'required',
                'string',
                'min:4',
                Rule::unique('spp_spm_tus', 'no_bukti')
                    ->where(function ($query) use ($tahunTransaksi) {
                        return $query->where('tahun_bukti', $tahunTransaksi);
                    })
                    ->ignore($this->spp_spm_tu_id),
            ],
            'tanggal' => 'required|date',
            'sub_kegiatan_id' => 'required|exists:sub_kegiatans,id',
        ]);

        $sumDetails = array_sum(array_column($this->rkas, 'nilai'));
        if ($sumDetails <= 0) {
            return $this->addError('rkas', 'Masukkan nilai transaksi minimal pada salah satu RKAS.');
        }
        $this->total_nilai = $sumDetails;

        // Validasi tiap baris: nilai tidak boleh melebihi sisa anggaran
        foreach ($this->rkas as $rkaItem) {
            if ($rkaItem['nilai'] > $rkaItem['initial_sisa']) {
                return $this->addError('rkas', "Nilai untuk RKAS {$rkaItem['nama_belanja']} tidak boleh melebihi sisa anggaran (Rp " . number_format($rkaItem['initial_sisa'], 2, ',', '.') . ").");
            }
        }

        DB::beginTransaction();
        try {
            $sppSpmTu = SppSpmTu::findOrFail($this->spp_spm_tu_id);
            $sppSpmTu->update([
                'no_bukti' => $this->no_bukti,
                'no_spm_sipd' => $this->no_spm_sipd ?: null,
                'tanggal' => $this->tanggal,
                'tahun_bukti' => $tahunTransaksi,
                'uraian' => $this->uraian,
                'total_nilai' => $this->total_nilai,
                'sub_kegiatan_id' => $this->sub_kegiatan_id,
            ]);

            // Hapus detail lama
            SppSpmTuDetail::where('spp_spm_tu_id', $this->spp_spm_tu_id)->delete();

            // Simpan detail baru
            foreach ($this->rkas as $rkaItem) {
                if ($rkaItem['nilai'] > 0) {
                    SppSpmTuDetail::create([
                        'spp_spm_tu_id' => $this->spp_spm_tu_id,
                        'rka_id' => $rkaItem['id'],
                        'nilai' => $rkaItem['nilai'],
                    ]);
                }
            }

            // Update uang_giros yang terkait jika sudah ada SP2D
            $uangGiro = UangGiro::where('spp_spm_tu_id', $sppSpmTu->id)->first();
            if ($uangGiro) {
                $uangGiro->update([
                    'no_bukti' => $this->no_bukti,
                    'uraian' => 'SP2D TU - ' . ($this->uraian ?? 'Tambahan Uang Persediaan'),
                    'nominal' => $this->total_nilai,
                ]);
            }

            DB::commit();

            $this->resetInputFields();
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
                    title: "Data SPP-SPM TU berhasil diupdate"
                });
                $('#sppSpmTuModal').modal('hide');
            JS);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function delete_confirmation($id)
    {
        $this->deleteId = $id;
        $this->js(<<<'JS'
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data ini akan dihapus dan proses ini tidak dapat dikembalikan.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Ya, hapus!"
            }).then((result) => {
                if (result.isConfirmed) {
                    $wire.delete()
                }
            });
        JS);
    }

    public function delete()
    {
        $sppSpmTu = SppSpmTu::find($this->deleteId);
        if ($sppSpmTu) {
            UangGiro::where('spp_spm_tu_id', $sppSpmTu->id)->delete();
            $sppSpmTu->delete();
        }

        $this->js(<<<'JS'
            const Toast = Swal.mixin({
                toast: true,
                position: "top-end",
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
            });
            Toast.fire({ icon: "error", title: "Data SPP-SPM TU berhasil dihapus" });
        JS);
    }

    public function openSp2dModal($id)
    {
        $sppSpmTu = SppSpmTu::findOrFail($id);
        $this->sp2dId = $sppSpmTu->id;
        $this->sp2d_no = $sppSpmTu->no_sp2d;
        $this->sp2d_tanggal = $sppSpmTu->tanggal_sp2d;

        $this->js(<<<'JS'
            $('#sp2dTuModal').modal('show');
        JS);
    }

    public function saveSp2d()
    {
        $this->validate([
            'sp2d_no' => 'required|string',
            'sp2d_tanggal' => 'required|date',
        ], [
            'sp2d_no.required' => 'Nomor SP2D wajib diisi.',
            'sp2d_tanggal.required' => 'Tanggal SP2D wajib diisi.',
        ]);

        DB::beginTransaction();
        try {
            $sppSpmTu = SppSpmTu::findOrFail($this->sp2dId);
            $sppSpmTu->update([
                'no_sp2d' => $this->sp2d_no,
                'tanggal_sp2d' => $this->sp2d_tanggal,
            ]);

            // Kelola uang_giros
            UangGiro::where('spp_spm_tu_id', $sppSpmTu->id)->delete();
            $this->createUangGiroFromSp2d($sppSpmTu);

            DB::commit();

            $this->sp2dId = null;
            $this->sp2d_no = null;
            $this->sp2d_tanggal = null;

            $this->js(<<<'JS'
                const Toast = Swal.mixin({ toast: true, position: "top-end", showConfirmButton: false, timer: 2000, timerProgressBar: true });
                Toast.fire({ icon: "success", title: "SP2D berhasil disimpan" });
                $('#sp2dTuModal').modal('hide');
            JS);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function closeSp2dModal()
    {
        $this->sp2dId = null;
        $this->sp2d_no = null;
        $this->sp2d_tanggal = null;
        $this->js(<<<'JS'
            $('#sp2dTuModal').modal('hide');
        JS);
    }

    private function createUangGiroFromSp2d(SppSpmTu $sppSpmTu)
    {
        UangGiro::create([
            'tipe' => 'TU',
            'spp_spm_tu_id' => $sppSpmTu->id,
            'no_bukti' => $sppSpmTu->no_bukti,
            'tanggal' => $sppSpmTu->tanggal_sp2d,
            'uraian' => 'SP2D TU - ' . ($sppSpmTu->uraian ?? 'Tambahan Uang Persediaan'),
            'nominal' => $sppSpmTu->total_nilai,
        ]);
    }

    public function closeForm()
    {
        $this->resetInputFields();
        $this->js(<<<'JS'
            $('#sppSpmTuModal').modal('hide');
        JS);
    }

    public function openForm()
    {
        $this->resetInputFields();
        $this->isEdit = false;
        $this->js(<<<'JS'
            $('#sppSpmTuModal').modal('show');
        JS);
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
            $('#viewSppSpmTu').modal("hide")
        JS);
        Storage::disk('local')->delete('public/reports/spp-spm-tu/' . $this->pathWord);
        Storage::disk('local')->delete('public/reports/spp-spm-tu/' . $this->pathpdf);
    }

    private function resetInputFields()
    {
        $this->spp_spm_tu_id = null;
        $this->no_bukti = '';
        $this->no_spm_sipd = null;
        $this->tanggal = null;
        $this->uraian = '';
        $this->total_nilai = 0;
        $this->sub_kegiatan_id = null;
        $this->sub_kegiatan_kode = null;
        $this->sub_kegiatan_nama = null;
        $this->rkas = [];
        $this->isEdit = false;
        $this->deleteId = null;
        $this->pathWord = null;
        $this->pathpdf = null;
        $this->tahunTransaksi = date('Y');
    }
}
