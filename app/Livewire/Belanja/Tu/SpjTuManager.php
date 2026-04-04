<?php

namespace App\Livewire\Belanja\Tu;

use App\Models\BelanjaTu;
use App\Models\SpjTu;
use App\Models\SppSpmTu;
use Livewire\Component;
use Livewire\Attributes\Title;

#[Title('SPJ TU')]
class SpjTuManager extends Component
{
    public $sppSpmTuId;
    public $sppSpmTu = [];
    public $spjTu = null;
    public $nomor_spj;
    public $tanggal_spj;
    public $keterangan;
    public $isEdit = false;
    public $belanjaTus = [];

    public function mount($sppSpmTuId)
    {
        $this->sppSpmTuId = $sppSpmTuId;

        $sppSpmTu = SppSpmTu::with(['belanjaTus.rka', 'spjTu', 'nihil'])->findOrFail($sppSpmTuId);
        $this->sppSpmTu = array_merge($sppSpmTu->toArray(), [
            'has_nihil' => $sppSpmTu->nihil !== null,
        ]);
        $this->belanjaTus = $sppSpmTu->belanjaTus->toArray();

        if ($sppSpmTu->spjTu) {
            $this->spjTu = $sppSpmTu->spjTu->toArray();
            $this->nomor_spj = $sppSpmTu->spjTu->nomor_spj;
            $this->tanggal_spj = $sppSpmTu->spjTu->tanggal_spj;
            $this->keterangan = $sppSpmTu->spjTu->keterangan;
        }
    }

    public function render()
    {
        $totalBelanja = collect($this->belanjaTus)->sum('nilai');
        $sisaTu = ($this->sppSpmTu['total_nilai'] ?? 0) - $totalBelanja;

        return view('livewire.belanja.tu.spj-tu-manager', [
            'totalBelanja' => $totalBelanja,
            'sisaTu' => $sisaTu,
        ]);
    }

    public function store()
    {
        $this->validate([
            'tanggal_spj' => 'required|date',
        ]);

        if (count($this->belanjaTus) === 0) {
            $this->js(<<<'JS'
                const Toast = Swal.mixin({
                    toast: true,
                    position: "top-end",
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true,
                });
                Toast.fire({ icon: "error", title: "Belum ada belanja TU yang diinput" });
            JS);
            return;
        }

        // Auto-generate nomor SPJ
        $year = date('Y', strtotime($this->tanggal_spj));
        $lastSpj = SpjTu::whereYear('tanggal_spj', $year)
            ->orderBy('nomor_spj', 'desc')
            ->first();

        $newNo = $lastSpj ? ((int) $lastSpj->nomor_spj + 1) : 1;
        $formattedNo = str_pad($newNo, 4, '0', STR_PAD_LEFT);

        $spjTu = SpjTu::create([
            'spp_spm_tu_id' => $this->sppSpmTuId,
            'nomor_spj' => $formattedNo,
            'tanggal_spj' => $this->tanggal_spj,
            'keterangan' => $this->keterangan,
        ]);

        $this->spjTu = $spjTu->toArray();
        $this->nomor_spj = $spjTu->nomor_spj;
        $this->isEdit = false;

        $this->js(<<<'JS'
            const Toast = Swal.mixin({
                toast: true,
                position: "top-end",
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
            });
            Toast.fire({ icon: "success", title: "SPJ TU berhasil disimpan" });
        JS);
    }

    public function edit()
    {
        if (!$this->spjTu || ($this->sppSpmTu['has_nihil'] ?? false)) {
            return;
        }

        $spjTu = SpjTu::findOrFail($this->spjTu['id']);
        $this->nomor_spj = $spjTu->nomor_spj;
        $this->tanggal_spj = $spjTu->tanggal_spj;
        $this->keterangan = $spjTu->keterangan;
        $this->isEdit = true;
    }

    public function update()
    {
        $this->validate([
            'tanggal_spj' => 'required|date',
        ]);

        $spjTu = SpjTu::findOrFail($this->spjTu['id']);
        $spjTu->update([
            'tanggal_spj' => $this->tanggal_spj,
            'keterangan' => $this->keterangan,
        ]);

        $this->spjTu = $spjTu->fresh()->toArray();
        $this->isEdit = false;

        $this->js(<<<'JS'
            const Toast = Swal.mixin({
                toast: true,
                position: "top-end",
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
            });
            Toast.fire({ icon: "success", title: "SPJ TU berhasil diupdate" });
        JS);
    }

    public function delete_confirmation()
    {
        $this->js(<<<'JS'
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Apakah kamu ingin menghapus SPJ TU ini? Proses ini tidak dapat dikembalikan.",
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
        if (!$this->spjTu || ($this->sppSpmTu['has_nihil'] ?? false)) {
            return;
        }

        SpjTu::destroy($this->spjTu['id']);

        $this->spjTu = null;
        $this->nomor_spj = null;
        $this->tanggal_spj = null;
        $this->keterangan = null;
        $this->isEdit = false;

        $this->js(<<<'JS'
            const Toast = Swal.mixin({
                toast: true,
                position: "top-end",
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
            });
            Toast.fire({ icon: "error", title: "SPJ TU berhasil dihapus" });
        JS);
    }
}
