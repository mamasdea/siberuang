<?php

namespace App\Livewire\Persediaan;

use App\Models\UangGiro as ModelUangGiro;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\WithPagination;

#[Title('Daftar SP2D')]
class UangGiro extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $paginate = 10;
    public $deleteId;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $tahun = session('tahun_anggaran', date('Y'));

        $query = ModelUangGiro::with(['sppSpmUp', 'sppSpmGu'])
            ->whereYear('tanggal', $tahun)
            ->when($this->search, function ($q) {
                $q->where(function ($query) {
                    $query->where('no_bukti', 'like', '%' . $this->search . '%')
                        ->orWhere('uraian', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('tanggal', 'asc');

        $totalTransaksi = (clone $query)->count();
        $totalNominal = (clone $query)->sum('nominal');

        return view('livewire.persediaan.uang-giro', [
            'uangGiros' => $query->paginate($this->paginate),
            'totalTransaksi' => $totalTransaksi,
            'totalNominal' => $totalNominal,
        ]);
    }

    public function deleteConfirmation($id)
    {
        $this->deleteId = $id;
        $this->js(<<<'JS'
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data manual ini akan dihapus.",
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
        $uangGiro = ModelUangGiro::find($this->deleteId);
        if ($uangGiro && !$uangGiro->spp_spm_up_id && !$uangGiro->spp_spm_gu_id) {
            $uangGiro->delete();
        }

        $this->js(<<<'JS'
            const Toast = Swal.mixin({ toast: true, position: "top-end", showConfirmButton: false, timer: 2000, timerProgressBar: true });
            Toast.fire({ icon: "error", title: "Data berhasil dihapus" });
        JS);
    }
}
