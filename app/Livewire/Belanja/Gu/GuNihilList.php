<?php

namespace App\Livewire\Belanja\Gu;

use App\Models\SpjGu;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;

#[Title('SPP-SPM GU Nihil')]
class GuNihilList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $paginate = 10;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $tahun = session('tahun_anggaran', date('Y'));

        // SPJ GU yang belum dibuatkan SPP-SPM GU (tidak punya sppSpmGus)
        $spjGus = SpjGu::with(['belanjas.pajak', 'nihil', 'sppSpmGus'])
            ->whereYear('tanggal_spj', $tahun)
            ->whereDoesntHave('sppSpmGus')
            ->when($this->search, function ($q) {
                $q->where(function ($query) {
                    $query->where('nomor_spj', 'like', '%' . $this->search . '%')
                        ->orWhere('keterangan', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('tanggal_spj', 'desc')
            ->paginate($this->paginate);

        return view('livewire.belanja.gu.gu-nihil-list', [
            'spjGus' => $spjGus,
        ]);
    }
}
