<?php

namespace App\Livewire\Belanja\Tu;

use App\Models\SpjTu;
use App\Models\SppSpmTu;
use App\Models\SppSpmTuNihil;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Storage;

#[Title('SPP-SPM TU Nihil')]
class SppSpmTuNihilManager extends Component
{
    use WithFileUploads;

    public $sppSpmTuId;
    public $sppSpmTu = [];
    public $nihil = null;
    public $no_spp;
    public $no_sts;
    public $no_spm_sipd;
    public $no_spm_tu_nihil_sipd;
    public $tanggal;
    public $uraian;
    public $nilai_setor = 0;
    public $isEdit = false;
    public $nihilId;
    public $fileBuktiSetor;
    public $existingBuktiSetor;

    // SP2D
    public $sp2dId;
    public $sp2d_no;
    public $sp2d_tanggal;

    public function mount($sppSpmTuId)
    {
        $this->sppSpmTuId = $sppSpmTuId;

        $sppSpmTu = SppSpmTu::with(['belanjaTus', 'spjTu', 'nihil'])->findOrFail($sppSpmTuId);

        $totalBelanja = $sppSpmTu->belanjaTus->sum('nilai');
        $this->nilai_setor = $sppSpmTu->total_nilai - $totalBelanja;

        $this->sppSpmTu = array_merge($sppSpmTu->toArray(), [
            'has_spj' => $sppSpmTu->spjTu !== null,
            'total_belanja' => $totalBelanja,
        ]);

        if ($sppSpmTu->nihil) {
            $this->nihil = $sppSpmTu->nihil->toArray();
            $this->nihilId = $sppSpmTu->nihil->id;
            $this->no_spp = $sppSpmTu->nihil->no_spp;
            $this->no_sts = $sppSpmTu->nihil->no_sts;
            $this->no_spm_sipd = $sppSpmTu->nihil->no_spm_sipd;
            $this->no_spm_tu_nihil_sipd = $sppSpmTu->nihil->no_spm_tu_nihil_sipd;
            $this->tanggal = $sppSpmTu->nihil->tanggal;
            $this->uraian = $sppSpmTu->nihil->uraian;
            $this->existingBuktiSetor = $sppSpmTu->nihil->bukti_setor;
            $this->sp2d_no = $sppSpmTu->nihil->no_sp2d;
            $this->sp2d_tanggal = $sppSpmTu->nihil->tanggal_sp2d;
        }
    }

    public function render()
    {
        return view('livewire.belanja.tu.spp-spm-tu-nihil-manager', [
            'nihilData' => $this->nihil,
            'sppSpmTuInfo' => $this->sppSpmTu,
            'nilaiSetor' => $this->nilai_setor,
        ]);
    }

    public function store()
    {
        $this->validate([
            'no_spp' => 'required',
            'tanggal' => 'required|date',
        ]);

        // SPJ TU must exist first
        $spjTu = SpjTu::where('spp_spm_tu_id', $this->sppSpmTuId)->first();
        if (!$spjTu) {
            $this->js(<<<'JS'
                const Toast = Swal.mixin({
                    toast: true,
                    position: "top-end",
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true,
                });
                Toast.fire({ icon: "error", title: "SPJ TU harus dibuat terlebih dahulu" });
            JS);
            return;
        }

        // Sisa must be > 0
        if ($this->nilai_setor <= 0) {
            $this->js(<<<'JS'
                const Toast = Swal.mixin({
                    toast: true,
                    position: "top-end",
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true,
                });
                Toast.fire({ icon: "error", title: "Tidak ada sisa TU yang perlu disetorkan" });
            JS);
            return;
        }

        $buktiSetorPath = null;
        if ($this->fileBuktiSetor) {
            $buktiSetorPath = $this->fileBuktiSetor->store('bukti-setor-tu', 'gcs');
        }

        $nihil = SppSpmTuNihil::create([
            'spp_spm_tu_id' => $this->sppSpmTuId,
            'no_spp' => $this->no_spp,
            'no_sts' => $this->no_sts,
            'no_spm_sipd' => $this->no_spm_sipd,
            'no_spm_tu_nihil_sipd' => $this->no_spm_tu_nihil_sipd,
            'tanggal' => $this->tanggal,
            'uraian' => $this->uraian,
            'nilai_setor' => $this->nilai_setor,
            'bukti_setor' => $buktiSetorPath,
        ]);

        $this->nihil = $nihil->toArray();
        $this->nihilId = $nihil->id;
        $this->isEdit = false;

        $this->js(<<<'JS'
            const Toast = Swal.mixin({
                toast: true,
                position: "top-end",
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
            });
            Toast.fire({ icon: "success", title: "TU Nihil berhasil disimpan" });
        JS);
    }

    public function edit()
    {
        if (!$this->nihil) {
            return;
        }

        $nihil = SppSpmTuNihil::findOrFail($this->nihilId);
        $this->no_bukti = $nihil->no_bukti;
        $this->no_spm_sipd = $nihil->no_spm_sipd;
        $this->tanggal = $nihil->tanggal;
        $this->uraian = $nihil->uraian;
        $this->isEdit = true;
    }

    public function update()
    {
        $this->validate([
            'no_spp' => 'required',
            'tanggal' => 'required|date',
        ]);

        $nihil = SppSpmTuNihil::findOrFail($this->nihilId);

        // Recalculate nilai_setor
        $sppSpmTu = SppSpmTu::with('belanjaTus')->findOrFail($this->sppSpmTuId);
        $totalBelanja = $sppSpmTu->belanjaTus->sum('nilai');
        $this->nilai_setor = $sppSpmTu->total_nilai - $totalBelanja;

        $updateData = [
            'no_spp' => $this->no_spp,
            'no_sts' => $this->no_sts,
            'no_spm_sipd' => $this->no_spm_sipd,
            'no_spm_tu_nihil_sipd' => $this->no_spm_tu_nihil_sipd,
            'tanggal' => $this->tanggal,
            'uraian' => $this->uraian,
            'nilai_setor' => $this->nilai_setor,
        ];

        if ($this->fileBuktiSetor) {
            if ($nihil->bukti_setor) {
                Storage::disk('gcs')->delete($nihil->bukti_setor);
            }
            $updateData['bukti_setor'] = $this->fileBuktiSetor->store('bukti-setor-tu', 'gcs');
        }

        $nihil->update($updateData);

        $this->nihil = $nihil->fresh()->toArray();
        $this->isEdit = false;

        $this->js(<<<'JS'
            const Toast = Swal.mixin({
                toast: true,
                position: "top-end",
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
            });
            Toast.fire({ icon: "success", title: "TU Nihil berhasil diupdate" });
        JS);
    }

    public function delete_confirmation()
    {
        $this->js(<<<'JS'
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Apakah kamu ingin menghapus TU Nihil ini? Proses ini tidak dapat dikembalikan.",
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
        if (!$this->nihilId) {
            return;
        }

        SppSpmTuNihil::destroy($this->nihilId);

        $this->nihil = null;
        $this->nihilId = null;
        $this->no_bukti = null;
        $this->no_spm_sipd = null;
        $this->tanggal = null;
        $this->uraian = null;
        $this->sp2d_no = null;
        $this->sp2d_tanggal = null;
        $this->isEdit = false;

        $this->js(<<<'JS'
            const Toast = Swal.mixin({
                toast: true,
                position: "top-end",
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
            });
            Toast.fire({ icon: "error", title: "TU Nihil berhasil dihapus" });
        JS);
    }

    public function openSp2dModal()
    {
        if ($this->nihil) {
            $this->sp2d_no = $this->nihil['no_sp2d'] ?? null;
            $this->sp2d_tanggal = $this->nihil['tanggal_sp2d'] ?? null;
        }

        $this->js(<<<'JS'
            $('#sp2dNihilModal').modal('show');
        JS);
    }

    public function saveSp2d()
    {
        if (!$this->nihilId) {
            return;
        }

        $nihil = SppSpmTuNihil::findOrFail($this->nihilId);
        $nihil->update([
            'no_sp2d' => $this->sp2d_no,
            'tanggal_sp2d' => $this->sp2d_tanggal,
        ]);

        $this->nihil = $nihil->fresh()->toArray();

        $this->js(<<<'JS'
            const Toast = Swal.mixin({
                toast: true,
                position: "top-end",
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
            });
            Toast.fire({ icon: "success", title: "SP2D Nihil berhasil disimpan" });
            $('#sp2dNihilModal').modal('hide');
        JS);
    }

    public function closeSp2dModal()
    {
        $this->sp2d_no = null;
        $this->sp2d_tanggal = null;

        $this->js(<<<'JS'
            $('#sp2dNihilModal').modal('hide');
        JS);
    }
}
