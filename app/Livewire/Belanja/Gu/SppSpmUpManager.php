<?php

namespace App\Livewire\Belanja\Gu;

use App\Models\SppSpmUp;
use App\Models\UangGiro;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Livewire\Laporan\LaporanSppSpmUp;

#[Title('SPP-SPM UP')]
class SppSpmUpManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $paginate = 10;

    // Form fields
    public $spp_spm_up_id;
    public $no_bukti;
    public $no_spm_sipd;
    public $tanggal;
    public $uraian;
    public $total_nilai;
    public $tanggal_sp2d;
    public $no_sp2d;
    public $isEdit = false;
    public $tahunTransaksi;
    public $deleteId;

    // SP2D modal
    public $sp2dId;
    public $sp2d_no;
    public $sp2d_tanggal;

    // Print/Download
    public $pathWord;
    public $pathpdf;

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
        $this->tahunTransaksi = date('Y');
    }

    public function render()
    {
        $tahun = session('tahun_anggaran', date('Y'));

        $sppSpmUps = SppSpmUp::where('tahun_bukti', $tahun)
            ->where(function ($query) {
                $query->where('no_bukti', 'like', '%' . $this->search . '%')
                    ->orWhere('uraian', 'like', '%' . $this->search . '%');
            })
            ->orderBy('id', 'desc')
            ->paginate($this->paginate);

        $totalTransaksi = SppSpmUp::where('tahun_bukti', $tahun)->count();
        $totalNominal = SppSpmUp::where('tahun_bukti', $tahun)->sum('total_nilai');

        return view('livewire.belanja.gu.spp-spm-up-manager', [
            'sppSpmUps' => $sppSpmUps,
            'totalTransaksi' => $totalTransaksi,
            'totalNominal' => $totalNominal,
        ]);
    }

    public function openForm()
    {
        $this->resetInputFields();
        $this->isEdit = false;
        $this->js(<<<'JS'
            $('#sppSpmUpModal').modal('show');
        JS);
    }

    public function store()
    {
        $tahunTransaksi = $this->tanggal ? date('Y', strtotime($this->tanggal)) : date('Y');

        $this->validate([
            'no_bukti' => [
                'required',
                'string',
                'min:4',
                Rule::unique('spp_spm_ups', 'no_bukti')->where(function ($query) use ($tahunTransaksi) {
                    return $query->where('tahun_bukti', $tahunTransaksi);
                }),
            ],
            'tanggal' => 'required|date',
            'total_nilai' => 'required|numeric|min:1',
            'uraian' => 'nullable',
        ]);

        DB::beginTransaction();
        try {
            $sppSpmUp = SppSpmUp::create([
                'no_bukti' => $this->no_bukti,
                'no_spm_sipd' => $this->no_spm_sipd ?: null,
                'tanggal' => $this->tanggal,
                'tahun_bukti' => $tahunTransaksi,
                'uraian' => $this->uraian,
                'total_nilai' => $this->total_nilai,
            ]);

            DB::commit();

            $this->resetInputFields();
            $this->js(<<<'JS'
                const Toast = Swal.mixin({
                    toast: true, position: "top-end", showConfirmButton: false, timer: 2000, timerProgressBar: true,
                });
                Toast.fire({ icon: "success", title: "SPP-SPM UP berhasil disimpan" });
                $('#sppSpmUpModal').modal('hide');
            JS);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $sppSpmUp = SppSpmUp::findOrFail($id);
        $this->spp_spm_up_id = $sppSpmUp->id;
        $this->no_bukti = $sppSpmUp->no_bukti;
        $this->no_spm_sipd = $sppSpmUp->no_spm_sipd;
        $this->tanggal = $sppSpmUp->tanggal;
        $this->tahunTransaksi = $sppSpmUp->tahun_bukti ?? date('Y');
        $this->uraian = $sppSpmUp->uraian;
        $this->total_nilai = $sppSpmUp->total_nilai;
        $this->tanggal_sp2d = $sppSpmUp->tanggal_sp2d;
        $this->isEdit = true;

        $this->js(<<<'JS'
            $('#sppSpmUpModal').modal('show');
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
                Rule::unique('spp_spm_ups', 'no_bukti')
                    ->where(function ($query) use ($tahunTransaksi) {
                        return $query->where('tahun_bukti', $tahunTransaksi);
                    })
                    ->ignore($this->spp_spm_up_id),
            ],
            'tanggal' => 'required|date',
            'total_nilai' => 'required|numeric|min:1',
            'uraian' => 'nullable',
        ]);

        DB::beginTransaction();
        try {
            $sppSpmUp = SppSpmUp::findOrFail($this->spp_spm_up_id);

            $sppSpmUp->update([
                'no_bukti' => $this->no_bukti,
                'no_spm_sipd' => $this->no_spm_sipd ?: null,
                'tanggal' => $this->tanggal,
                'tahun_bukti' => $tahunTransaksi,
                'uraian' => $this->uraian,
                'total_nilai' => $this->total_nilai,
            ]);

            DB::commit();

            $this->resetInputFields();
            $this->js(<<<'JS'
                const Toast = Swal.mixin({
                    toast: true, position: "top-end", showConfirmButton: false, timer: 2000, timerProgressBar: true,
                });
                Toast.fire({ icon: "success", title: "SPP-SPM UP berhasil diupdate" });
                $('#sppSpmUpModal').modal('hide');
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
        $sppSpmUp = SppSpmUp::find($this->deleteId);
        if ($sppSpmUp) {
            UangGiro::where('spp_spm_up_id', $sppSpmUp->id)->delete();
            $sppSpmUp->delete();
        }

        $this->js(<<<'JS'
            const Toast = Swal.mixin({
                toast: true, position: "top-end", showConfirmButton: false, timer: 2000, timerProgressBar: true,
            });
            Toast.fire({ icon: "error", title: "SPP-SPM UP berhasil dihapus" });
        JS);
    }

    public function printSppSpmUp($id)
    {
        $data = new LaporanSppSpmUp;
        $paths = $data->getLaporanSppSpmUpPaths($id);
        $this->pathWord = $paths['word_path'];
        $this->pathpdf = $paths['pdf_path'];
        $this->js(<<<'JS'
            $('#viewSppSpmUp').modal("show")
        JS);
    }

    public function downloadSppSpmUp($id)
    {
        $data = new LaporanSppSpmUp;
        return $data->downloadLaporanSppSpmUp($id);
    }

    public function closeModalPdf()
    {
        $this->js(<<<'JS'
            $('#viewSppSpmUp').modal("hide")
        JS);
        Storage::disk('local')->delete('public/reports/spp-spm-up/' . $this->pathWord);
        Storage::disk('local')->delete('public/reports/laporan_belanja_' . $this->pathpdf);
    }

    public function closeForm()
    {
        $this->resetInputFields();
        $this->js(<<<'JS'
            $('#sppSpmUpModal').modal('hide');
        JS);
    }

    private function createUangGiroFromSp2d(SppSpmUp $sppSpmUp)
    {
        UangGiro::create([
            'tipe' => 'UP',
            'spp_spm_up_id' => $sppSpmUp->id,
            'no_bukti' => $sppSpmUp->no_bukti,
            'tanggal' => $sppSpmUp->tanggal_sp2d,
            'uraian' => 'SP2D UP - ' . ($sppSpmUp->uraian ?? 'Uang Persediaan'),
            'nominal' => $sppSpmUp->total_nilai,
        ]);
    }

    public function openSp2dModal($id)
    {
        $sppSpmUp = SppSpmUp::findOrFail($id);
        $this->sp2dId = $sppSpmUp->id;
        $this->sp2d_no = $sppSpmUp->no_sp2d;
        $this->sp2d_tanggal = $sppSpmUp->tanggal_sp2d;

        $this->js(<<<'JS'
            $('#sp2dUpModal').modal('show');
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
            $sppSpmUp = SppSpmUp::findOrFail($this->sp2dId);
            $sppSpmUp->update([
                'no_sp2d' => $this->sp2d_no,
                'tanggal_sp2d' => $this->sp2d_tanggal,
            ]);

            UangGiro::where('spp_spm_up_id', $sppSpmUp->id)->delete();
            $this->createUangGiroFromSp2d($sppSpmUp);

            DB::commit();

            $this->sp2dId = null;
            $this->sp2d_no = null;
            $this->sp2d_tanggal = null;

            $this->js(<<<'JS'
                const Toast = Swal.mixin({ toast: true, position: "top-end", showConfirmButton: false, timer: 2000, timerProgressBar: true });
                Toast.fire({ icon: "success", title: "SP2D berhasil disimpan" });
                $('#sp2dUpModal').modal('hide');
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
            $('#sp2dUpModal').modal('hide');
        JS);
    }

    private function resetInputFields()
    {
        $this->spp_spm_up_id = null;
        $this->no_bukti = '';
        $this->no_spm_sipd = null;
        $this->tanggal = null;
        $this->uraian = '';
        $this->total_nilai = null;
        $this->tanggal_sp2d = null;
        $this->no_sp2d = null;
        $this->isEdit = false;
        $this->tahunTransaksi = date('Y');
    }
}
