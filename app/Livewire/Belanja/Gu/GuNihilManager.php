<?php

namespace App\Livewire\Belanja\Gu;

use App\Models\SpjGu;
use App\Models\SppSpmGuNihil;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Storage;

#[Title('SPP-SPM GU Nihil')]
class GuNihilManager extends Component
{
    use WithFileUploads;

    public $spjGuId;
    public $spjGu = [];
    public $nihil = null;
    public $no_spp;
    public $no_sts;
    public $no_spm_sipd;
    public $no_spm_gu_nihil_sipd;
    public $tanggal;
    public $uraian;
    public $nilai_setor = 0;
    public $isEdit = false;
    public $nihilId;
    public $fileBuktiSetor;
    public $existingBuktiSetor;

    // SP2D
    public $sp2d_no;
    public $sp2d_tanggal;

    public function mount($spjGuId)
    {
        $this->spjGuId = $spjGuId;

        $spjGu = SpjGu::with(['belanjas.pajak', 'nihil'])->findOrFail($spjGuId);

        $totalBelanja = $spjGu->belanjas->sum('nilai');
        $totalPajak = $spjGu->belanjas->sum(function ($b) {
            return $b->pajak->sum('nominal');
        });

        // Sisa = total belanja - total pajak yang disetor (net yang harus dikembalikan)
        // Atau sesuai kebutuhan: nilai_setor = sisa UP yang tidak terpakai
        $this->nilai_setor = $totalBelanja;

        $this->spjGu = [
            'nomor_spj' => $spjGu->nomor_spj,
            'tanggal_spj' => $spjGu->tanggal_spj,
            'periode_awal' => $spjGu->periode_awal,
            'periode_akhir' => $spjGu->periode_akhir,
            'keterangan' => $spjGu->keterangan,
            'total_belanja' => $totalBelanja,
            'total_pajak' => $totalPajak,
            'jumlah_belanja' => $spjGu->belanjas->count(),
        ];

        if ($spjGu->nihil) {
            $this->nihil = $spjGu->nihil->toArray();
            $this->nihilId = $spjGu->nihil->id;
            $this->no_spp = $spjGu->nihil->no_spp;
            $this->no_sts = $spjGu->nihil->no_sts;
            $this->no_spm_sipd = $spjGu->nihil->no_spm_sipd;
            $this->no_spm_gu_nihil_sipd = $spjGu->nihil->no_spm_gu_nihil_sipd;
            $this->tanggal = $spjGu->nihil->tanggal;
            $this->uraian = $spjGu->nihil->uraian;
            $this->nilai_setor = $spjGu->nihil->nilai_setor;
            $this->existingBuktiSetor = $spjGu->nihil->bukti_setor;
            $this->sp2d_no = $spjGu->nihil->no_sp2d;
            $this->sp2d_tanggal = $spjGu->nihil->tanggal_sp2d;
        }
    }

    public function render()
    {
        return view('livewire.belanja.gu.gu-nihil-manager');
    }

    public function store()
    {
        $this->validate([
            'no_spp' => 'required',
            'tanggal' => 'required|date',
        ]);

        $buktiSetorPath = null;
        if ($this->fileBuktiSetor) {
            $buktiSetorPath = $this->fileBuktiSetor->store('bukti-setor-gu-nihil', 'gcs');
        }

        $tahunTransaksi = $this->tanggal ? date('Y', strtotime($this->tanggal)) : date('Y');

        $nihil = SppSpmGuNihil::create([
            'spj_gu_id' => $this->spjGuId,
            'no_spp' => $this->no_spp,
            'no_sts' => $this->no_sts,
            'no_spm_sipd' => $this->no_spm_sipd,
            'no_spm_gu_nihil_sipd' => $this->no_spm_gu_nihil_sipd,
            'tanggal' => $this->tanggal,
            'tahun_bukti' => $tahunTransaksi,
            'uraian' => $this->uraian,
            'nilai_setor' => $this->nilai_setor,
            'bukti_setor' => $buktiSetorPath,
        ]);

        $this->nihil = $nihil->toArray();
        $this->nihilId = $nihil->id;
        $this->existingBuktiSetor = $buktiSetorPath;
        $this->isEdit = false;
        $this->fileBuktiSetor = null;

        $this->js(<<<'JS'
            const Toast = Swal.mixin({ toast: true, position: "top-end", showConfirmButton: false, timer: 2000, timerProgressBar: true });
            Toast.fire({ icon: "success", title: "SPP-SPM GU Nihil berhasil disimpan" });
        JS);
    }

    public function edit()
    {
        $this->isEdit = true;
    }

    public function update()
    {
        $this->validate([
            'no_spp' => 'required',
            'tanggal' => 'required|date',
        ]);

        $nihil = SppSpmGuNihil::findOrFail($this->nihilId);

        $updateData = [
            'no_spp' => $this->no_spp,
            'no_sts' => $this->no_sts,
            'no_spm_sipd' => $this->no_spm_sipd,
            'no_spm_gu_nihil_sipd' => $this->no_spm_gu_nihil_sipd,
            'tanggal' => $this->tanggal,
            'uraian' => $this->uraian,
            'nilai_setor' => $this->nilai_setor,
        ];

        if ($this->fileBuktiSetor) {
            if ($nihil->bukti_setor) {
                Storage::disk('gcs')->delete($nihil->bukti_setor);
            }
            $updateData['bukti_setor'] = $this->fileBuktiSetor->store('bukti-setor-gu-nihil', 'gcs');
        }

        $nihil->update($updateData);
        $this->nihil = $nihil->fresh()->toArray();
        $this->existingBuktiSetor = $nihil->bukti_setor;
        $this->isEdit = false;
        $this->fileBuktiSetor = null;

        $this->js(<<<'JS'
            const Toast = Swal.mixin({ toast: true, position: "top-end", showConfirmButton: false, timer: 2000, timerProgressBar: true });
            Toast.fire({ icon: "success", title: "SPP-SPM GU Nihil berhasil diupdate" });
        JS);
    }

    public function delete_confirmation()
    {
        $this->js(<<<'JS'
            Swal.fire({ title: 'Yakin hapus?', text: "Data tidak dapat dikembalikan!", icon: "warning", showCancelButton: true, confirmButtonColor: "#d33", confirmButtonText: "Hapus" }).then((r) => { if(r.isConfirmed) $wire.delete() });
        JS);
    }

    public function delete()
    {
        $nihil = SppSpmGuNihil::find($this->nihilId);
        if ($nihil) {
            if ($nihil->bukti_setor) {
                Storage::disk('gcs')->delete($nihil->bukti_setor);
            }
            $nihil->delete();
        }

        $this->nihil = null;
        $this->nihilId = null;
        $this->no_spp = null;
        $this->no_sts = null;
        $this->isEdit = false;

        $this->js(<<<'JS'
            const Toast = Swal.mixin({ toast: true, position: "top-end", showConfirmButton: false, timer: 2000, timerProgressBar: true });
            Toast.fire({ icon: "error", title: "Data berhasil dihapus" });
        JS);
    }

    public function openSp2dModal()
    {
        $this->js("$('#sp2dGuNihilModal').modal('show');");
    }

    public function saveSp2d()
    {
        $this->validate([
            'sp2d_no' => 'required|string',
            'sp2d_tanggal' => 'required|date',
        ]);

        $nihil = SppSpmGuNihil::findOrFail($this->nihilId);
        $nihil->update([
            'no_sp2d' => $this->sp2d_no,
            'tanggal_sp2d' => $this->sp2d_tanggal,
        ]);

        $this->nihil = $nihil->fresh()->toArray();

        $this->js(<<<'JS'
            const Toast = Swal.mixin({ toast: true, position: "top-end", showConfirmButton: false, timer: 2000, timerProgressBar: true });
            Toast.fire({ icon: "success", title: "SP2D berhasil disimpan" });
            $('#sp2dGuNihilModal').modal('hide');
        JS);
    }

    public function closeSp2dModal()
    {
        $this->js("$('#sp2dGuNihilModal').modal('hide');");
    }
}
