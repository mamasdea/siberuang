<?php

namespace App\Livewire;

use App\Models\Program;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;

class TabAnggaran extends Component
{
    public $activeTab = 'program';
    public $tabs = ['program', 'kegiatan', 'subkegiatan', 'belanja'];
    public $programId;
    public $kegiatanId;
    public $subKegiatanId;

    public function nextTab()
    {
        $currentIndex = array_search($this->activeTab, $this->tabs);
        if ($currentIndex !== false && isset($this->tabs[$currentIndex + 1])) {
            $this->activeTab = $this->tabs[$currentIndex + 1];
        }
    }

    public function render()
    {

        return view('livewire.tab-anggaran');
    }
}
