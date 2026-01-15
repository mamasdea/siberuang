<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\SubKegiatan;

class SubKegiatanHierarchy extends Component
{
    use WithPagination;

    public $searchsub = '';
    public $paginatesub = 5;
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function selectSubKegiatan($id)
    {
        $subkegiatan = SubKegiatan::find($id);
        if ($subkegiatan) {
            $this->dispatch('subKegiatanSelected', $subkegiatan->id, $subkegiatan->kode, $subkegiatan->nama);
        }
    }

    public function render()
    {
        $tahun = session('tahun_anggaran', date('Y'));
        
        $subKegiatans = SubKegiatan::query()
            ->whereHas('kegiatan', function ($query) use ($tahun) {
                $query->whereHas('program', function ($query2) use ($tahun) {
                    $query2->where('tahun_anggaran', $tahun);
                });
            })
            ->where('nama', 'like', '%' . $this->searchsub . '%')
            ->orderBy('id')
            ->paginate($this->paginatesub);

        return view('livewire.sub-kegiatan-hierarchy', compact('subKegiatans'));
    }
}
