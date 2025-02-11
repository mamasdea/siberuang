<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Penerima;

class SelectPenerimaTable extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedPenerima = null;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function selectPenerima($id)
    {
        $this->selectedPenerima = $id;
        $this->dispatch('kirim_penerima', id: $id);

        // Logic tambahan jika diperlukan, seperti mengirim event atau memperbarui data
    }



    public function render()
    {
        $penerimas = Penerima::where('nama', 'like', '%' . $this->search . '%')
            ->paginate(10);

        return view('livewire.select-penerima-table', compact('penerimas'));
    }
}
