<?php

namespace App\Livewire\Belanja\Tu;

use App\Models\SppSpmTu;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;

#[Title('Belanja TU')]
class BelanjaTuList extends Component
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

        $sppSpmTus = SppSpmTu::with(['belanjaTus', 'spjTu'])
            ->where('tahun_bukti', $tahun)
            ->whereNotNull('tanggal_sp2d')
            ->when($this->search, function ($q) {
                $q->where(function ($query) {
                    $query->where('no_bukti', 'like', '%' . $this->search . '%')
                        ->orWhere('uraian', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('tanggal', 'desc')
            ->paginate($this->paginate);

        return view('livewire.belanja.tu.belanja-tu-list', [
            'sppSpmTus' => $sppSpmTus,
        ]);
    }
}
