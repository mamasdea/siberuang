<?php

namespace App\Livewire\Belanja\Gu;

use App\Models\SpjGu;
use App\Models\SppSpmGu;
use App\Models\UangGiro;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Livewire\Laporan\LaporanSppSpmGu;

#[Title('SPP-SPM GU')]
class SppSpmGuManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $paginate = 10;

    // Form fields
    public $spp_spm_gu_id;
    public $no_bukti;
    public $no_spm_sipd;
    public $tanggal;
    public $uraian;
    public $total_nilai = 0;
    public $tanggal_sp2d;
    public $no_sp2d;
    public $isEdit = false;
    public $tahunTransaksi;

    // SP2D modal
    public $sp2dId;
    public $sp2d_no;
    public $sp2d_tanggal;

    // SPJ GU selection
    public $selectedSpjGuIds = [];
    public $deleteId;

    // Detail view
    public $detailSppSpmGu;

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

        $sppSpmGus = SppSpmGu::with(['spjGus.belanjas'])
            ->where('tahun_bukti', $tahun)
            ->where(function ($query) {
                $query->where('no_bukti', 'like', '%' . $this->search . '%')
                    ->orWhere('uraian', 'like', '%' . $this->search . '%');
            })
            ->orderBy('id', 'desc')
            ->paginate($this->paginate);

        // Get available SPJ GU (not yet linked to other SPP-SPM GU)
        $availableSpjGus = SpjGu::with('belanjas')
            ->whereYear('tanggal_spj', $tahun)
            ->whereDoesntHave('sppSpmGus', function ($q) {
                if ($this->spp_spm_gu_id) {
                    $q->where('spp_spm_gus.id', '!=', $this->spp_spm_gu_id);
                }
            })
            ->orderBy('nomor_spj', 'asc')
            ->get();

        // Calculate total nilai from selected SPJ GUs
        if (!empty($this->selectedSpjGuIds)) {
            $this->total_nilai = SpjGu::with('belanjas')
                ->whereIn('id', $this->selectedSpjGuIds)
                ->get()
                ->sum(function ($spj) {
                    return $spj->belanjas->sum('nilai');
                });
        } else {
            $this->total_nilai = 0;
        }

        $totalTransaksi = SppSpmGu::where('tahun_bukti', $tahun)->count();
        $totalNominal = SppSpmGu::where('tahun_bukti', $tahun)->sum('total_nilai');

        return view('livewire.belanja.gu.spp-spm-gu-manager', [
            'sppSpmGus' => $sppSpmGus,
            'availableSpjGus' => $availableSpjGus,
            'totalTransaksi' => $totalTransaksi,
            'totalNominal' => $totalNominal,
        ]);
    }

    public function openForm()
    {
        $this->resetInputFields();
        $this->isEdit = false;
        $this->js(<<<'JS'
            $('#sppSpmGuModal').modal('show');
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
                Rule::unique('spp_spm_gus', 'no_bukti')->where(function ($query) use ($tahunTransaksi) {
                    return $query->where('tahun_bukti', $tahunTransaksi);
                }),
            ],
            'tanggal' => 'required|date',
            'uraian' => 'nullable',
            'selectedSpjGuIds' => 'required|array|min:1',
        ], [
            'selectedSpjGuIds.required' => 'Pilih minimal 1 SPJ GU.',
            'selectedSpjGuIds.min' => 'Pilih minimal 1 SPJ GU.',
        ]);

        DB::beginTransaction();
        try {
            $sppSpmGu = SppSpmGu::create([
                'no_bukti' => $this->no_bukti,
                'no_spm_sipd' => $this->no_spm_sipd ?: null,
                'tanggal' => $this->tanggal,
                'tahun_bukti' => $tahunTransaksi,
                'uraian' => $this->uraian,
                'total_nilai' => $this->total_nilai,
            ]);

            $sppSpmGu->spjGus()->attach($this->selectedSpjGuIds);

            DB::commit();

            $this->resetInputFields();
            $this->js(<<<'JS'
                const Toast = Swal.mixin({
                    toast: true,
                    position: "top-end",
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true,
                });
                Toast.fire({ icon: "success", title: "SPP-SPM GU berhasil disimpan" });
                $('#sppSpmGuModal').modal('hide');
            JS);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $sppSpmGu = SppSpmGu::with('spjGus')->findOrFail($id);
        $this->spp_spm_gu_id = $sppSpmGu->id;
        $this->no_bukti = $sppSpmGu->no_bukti;
        $this->no_spm_sipd = $sppSpmGu->no_spm_sipd;
        $this->tanggal = $sppSpmGu->tanggal;
        $this->tahunTransaksi = $sppSpmGu->tahun_bukti ?? date('Y');
        $this->uraian = $sppSpmGu->uraian;
        $this->selectedSpjGuIds = $sppSpmGu->spjGus->pluck('id')->toArray();
        $this->tanggal_sp2d = $sppSpmGu->tanggal_sp2d;
        $this->isEdit = true;

        $this->js(<<<'JS'
            $('#sppSpmGuModal').modal('show');
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
                Rule::unique('spp_spm_gus', 'no_bukti')
                    ->where(function ($query) use ($tahunTransaksi) {
                        return $query->where('tahun_bukti', $tahunTransaksi);
                    })
                    ->ignore($this->spp_spm_gu_id),
            ],
            'tanggal' => 'required|date',
            'uraian' => 'nullable',
            'selectedSpjGuIds' => 'required|array|min:1',
        ], [
            'selectedSpjGuIds.required' => 'Pilih minimal 1 SPJ GU.',
            'selectedSpjGuIds.min' => 'Pilih minimal 1 SPJ GU.',
        ]);

        DB::beginTransaction();
        try {
            $sppSpmGu = SppSpmGu::findOrFail($this->spp_spm_gu_id);
            $sppSpmGu->update([
                'no_bukti' => $this->no_bukti,
                'no_spm_sipd' => $this->no_spm_sipd ?: null,
                'tanggal' => $this->tanggal,
                'tahun_bukti' => $tahunTransaksi,
                'uraian' => $this->uraian,
                'total_nilai' => $this->total_nilai,
            ]);

            $sppSpmGu->spjGus()->sync($this->selectedSpjGuIds);

            // Update uang_giros yang terkait jika sudah ada SP2D
            $uangGiro = UangGiro::where('spp_spm_gu_id', $sppSpmGu->id)->first();
            if ($uangGiro) {
                $uangGiro->update([
                    'no_bukti' => $this->no_bukti,
                    'uraian' => 'SP2D GU - ' . ($this->uraian ?? 'Ganti Uang Persediaan'),
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
                });
                Toast.fire({ icon: "success", title: "SPP-SPM GU berhasil diupdate" });
                $('#sppSpmGuModal').modal('hide');
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
        $sppSpmGu = SppSpmGu::find($this->deleteId);
        if ($sppSpmGu) {
            UangGiro::where('spp_spm_gu_id', $sppSpmGu->id)->delete();
            $sppSpmGu->spjGus()->detach();
            $sppSpmGu->delete();
        }

        $this->js(<<<'JS'
            const Toast = Swal.mixin({
                toast: true,
                position: "top-end",
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
            });
            Toast.fire({ icon: "error", title: "SPP-SPM GU berhasil dihapus" });
        JS);
    }

    public function showDetail($id)
    {
        $this->detailSppSpmGu = SppSpmGu::with(['spjGus.belanjas.rka.subKegiatan', 'spjGus.belanjas.penerimaan', 'spjGus.belanjas.pajak'])
            ->findOrFail($id);

        $this->js(<<<'JS'
            $('#detailSppSpmGuModal').modal('show');
        JS);
    }

    public function closeDetail()
    {
        $this->detailSppSpmGu = null;
        $this->js(<<<'JS'
            $('#detailSppSpmGuModal').modal('hide');
        JS);
    }

    public function toggleSpjGu($spjGuId)
    {
        if (in_array($spjGuId, $this->selectedSpjGuIds)) {
            $this->selectedSpjGuIds = array_values(array_diff($this->selectedSpjGuIds, [$spjGuId]));
        } else {
            $this->selectedSpjGuIds[] = $spjGuId;
        }
    }

    public function printSppSpmGu($id)
    {
        $data = new LaporanSppSpmGu;
        $paths = $data->getLaporanSppSpmGuPaths($id);
        $this->pathWord = $paths['word_path'];
        $this->pathpdf = $paths['pdf_path'];
        $this->js(<<<'JS'
            $('#viewSppSpmGu').modal("show")
        JS);
    }

    public function downloadSppSpmGu($id)
    {
        $data = new LaporanSppSpmGu;
        return $data->downloadLaporanSppSpmGu($id);
    }

    public function closeModalPdf()
    {
        $this->js(<<<'JS'
            $('#viewSppSpmGu').modal("hide")
        JS);
        Storage::disk('local')->delete('public/reports/spp-spm-gu/' . $this->pathWord);
        Storage::disk('local')->delete('public/reports/laporan_belanja_' . $this->pathpdf);
    }

    public function closeForm()
    {
        $this->resetInputFields();
        $this->js(<<<'JS'
            $('#sppSpmGuModal').modal('hide');
        JS);
    }

    private function createUangGiroFromSp2d(SppSpmGu $sppSpmGu)
    {
        UangGiro::create([
            'tipe' => 'GU',
            'spp_spm_gu_id' => $sppSpmGu->id,
            'no_bukti' => $sppSpmGu->no_bukti,
            'tanggal' => $sppSpmGu->tanggal_sp2d,
            'uraian' => 'SP2D GU - ' . ($sppSpmGu->uraian ?? 'Ganti Uang Persediaan'),
            'nominal' => $sppSpmGu->total_nilai,
        ]);
    }

    public function openSp2dModal($id)
    {
        $sppSpmGu = SppSpmGu::findOrFail($id);
        $this->sp2dId = $sppSpmGu->id;
        $this->sp2d_no = $sppSpmGu->no_sp2d;
        $this->sp2d_tanggal = $sppSpmGu->tanggal_sp2d;

        $this->js(<<<'JS'
            $('#sp2dModal').modal('show');
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
            $sppSpmGu = SppSpmGu::findOrFail($this->sp2dId);
            $sppSpmGu->update([
                'no_sp2d' => $this->sp2d_no,
                'tanggal_sp2d' => $this->sp2d_tanggal,
            ]);

            // Kelola uang_giros
            UangGiro::where('spp_spm_gu_id', $sppSpmGu->id)->delete();
            $this->createUangGiroFromSp2d($sppSpmGu);

            DB::commit();

            $this->sp2dId = null;
            $this->sp2d_no = null;
            $this->sp2d_tanggal = null;

            $this->js(<<<'JS'
                const Toast = Swal.mixin({ toast: true, position: "top-end", showConfirmButton: false, timer: 2000, timerProgressBar: true });
                Toast.fire({ icon: "success", title: "SP2D berhasil disimpan" });
                $('#sp2dModal').modal('hide');
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
            $('#sp2dModal').modal('hide');
        JS);
    }

    private function resetInputFields()
    {
        $this->spp_spm_gu_id = null;
        $this->no_bukti = '';
        $this->no_spm_sipd = null;
        $this->tanggal = null;
        $this->uraian = '';
        $this->total_nilai = 0;
        $this->tanggal_sp2d = null;
        $this->no_sp2d = null;
        $this->selectedSpjGuIds = [];
        $this->isEdit = false;
        $this->tahunTransaksi = date('Y');
    }
}
