<?php

namespace App\Livewire;

use App\Models\Rka;
use App\Models\Program;
use Livewire\Component;
use App\Models\Kegiatan;
use App\Models\SubKegiatan;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;

class ProgramHierarchy extends Component
{


    public $search = '';
    public $subKegiatans = [];
    public $selectedSubKegiatanId = null;

    #[On('refresh')]
    public function refreshAh()
    {

        $this->loadSubKegiatans();
    }
    public function mount()
    {
        $this->loadSubKegiatans();
    }

    public function updatedSearch()
    {
        $this->loadSubKegiatans();
    }

    public function loadSubKegiatans()
    {
        $tahun = session('tahun_anggaran', date('Y'));
        // Cari langsung SubKegiatan berdasarkan nama dan filter tahun anggaran (melalui relasi kegiatan.program)
        $this->subKegiatans = SubKegiatan::where('nama', 'like', '%' . $this->search . '%')
            ->whereHas('kegiatan.program', function ($query) use ($tahun) {
                $query->where('tahun_anggaran', $tahun);
            })
            ->with('rkas')
            ->get();
    }

    public function selectSubKegiatan($subKegiatanId)
    {
        // Toggle detail sub kegiatan: jika sudah dipilih, klik ulang untuk menutup detailnya
        if ($this->selectedSubKegiatanId === $subKegiatanId) {
            $this->selectedSubKegiatanId = null;
        } else {
            $this->selectedSubKegiatanId = $subKegiatanId;
        }
    }

    public function kirim($id)
    {
        $this->dispatch('kirim_rka', id: $id);
    }


    public function render()
    {
        return view('livewire.program-hierarchy', [
            'subKegiatans' => $this->subKegiatans,
        ]);
    }
}
