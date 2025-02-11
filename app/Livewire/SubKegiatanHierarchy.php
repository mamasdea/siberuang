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
        $subKegiatans = SubKegiatan::query()
            ->Where('nama', 'like', '%' . $this->searchsub . '%')
            ->orderBy('id')
            ->paginate($this->paginatesub);

        return view('livewire.sub-kegiatan-hierarchy', compact('subKegiatans'));
    }
}
