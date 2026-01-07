<?php

namespace App\Livewire\Persediaan;

use App\Models\UangGiro as ModelUangGiro;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\WithPagination;

#[Title('Uang Giro')]
class UangGiro extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $no_bukti, $tanggal, $uraian, $nominal, $uangGiroId;
    public $isEditMode = false;

    // ===== Filter & Search =====
    public $search = '';
    public ?int $tahun = null; // null = semua tahun
    public array $listTahun = [];

    protected $rules = [
        'no_bukti' => 'required|string|max:255',
        'tanggal' => 'required|date',
        'uraian' => 'required|string|max:255',
        'nominal' => 'required|numeric|min:0',
    ];

    public function mount()
    {
        // default dari session kalau ada, kalau tidak pakai tahun sekarang
        $this->tahun = (int) session('tahun_anggaran', date('Y'));

        $this->refreshListTahun();
    }

    public function render()
    {
        $uangGiros = ModelUangGiro::query()
            ->when($this->tahun, fn($q) => $q->whereYear('tanggal', $this->tahun))
            ->when($this->search, function ($q) {
                $q->where(function ($query) {
                    $query->where('no_bukti', 'like', '%' . $this->search . '%')
                        ->orWhere('uraian', 'like', '%' . $this->search . '%')
                        ->orWhere('tanggal', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('tanggal', 'desc')
            ->paginate(10);

        return view('livewire.persediaan.uang-giro', [
            'uangGiros' => $uangGiros,
            'totalTransaksi' => ModelUangGiro::query()
                ->when($this->tahun, fn($q) => $q->whereYear('tanggal', $this->tahun))
                ->when($this->search, function ($q) {
                    $q->where(function ($query) {
                        $query->where('no_bukti', 'like', '%' . $this->search . '%')
                            ->orWhere('uraian', 'like', '%' . $this->search . '%')
                            ->orWhere('tanggal', 'like', '%' . $this->search . '%');
                    });
                })
                ->count(),
            'totalNominal' => ModelUangGiro::query()
                ->when($this->tahun, fn($q) => $q->whereYear('tanggal', $this->tahun))
                ->when($this->search, function ($q) {
                    $q->where(function ($query) {
                        $query->where('no_bukti', 'like', '%' . $this->search . '%')
                            ->orWhere('uraian', 'like', '%' . $this->search . '%')
                            ->orWhere('tanggal', 'like', '%' . $this->search . '%');
                    });
                })
                ->sum('nominal'),
        ]);
    }

    protected function refreshListTahun(): void
    {
        // Daftar tahun yang ada pada data uang giro
        $this->listTahun = ModelUangGiro::query()
            ->selectRaw('YEAR(tanggal) as tahun')
            ->distinct()
            ->orderByDesc('tahun')
            ->pluck('tahun')
            ->map(fn($t) => (int) $t)
            ->toArray();
    }

    public function updatedTahun()
    {
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function resetInput()
    {
        $this->no_bukti = '';
        $this->tanggal = '';
        $this->uraian = '';
        $this->nominal = '';
        $this->uangGiroId = null;
        $this->isEditMode = false;
    }

    public function store()
    {
        $this->validate();

        ModelUangGiro::create([
            'no_bukti' => $this->no_bukti,
            'tanggal' => $this->tanggal,
            'uraian' => $this->uraian,
            'nominal' => $this->nominal,
        ]);

        // refresh tahun list (kalau input tahun baru)
        $this->refreshListTahun();

        $this->resetInput();

        $this->js(<<<'JS'
            Swal.fire({ icon: 'success', title: 'Data berhasil disimpan!', timer: 1500, showConfirmButton: false });
            $('#uangGiroModal').modal('hide');
        JS);
    }

    public function edit($id)
    {
        $uangGiro = ModelUangGiro::findOrFail($id);

        $this->no_bukti = $uangGiro->no_bukti;
        $this->tanggal = $uangGiro->tanggal;
        $this->uraian = $uangGiro->uraian;
        $this->nominal = $uangGiro->nominal;
        $this->uangGiroId = $uangGiro->id;

        $this->isEditMode = true;

        // jika Anda pakai modal bootstrap, biasanya perlu show modal dari JS di blade
        // $this->js("$('#uangGiroModal').modal('show');");
    }

    public function update()
    {
        $this->validate();

        $uangGiro = ModelUangGiro::findOrFail($this->uangGiroId);
        $uangGiro->update([
            'no_bukti' => $this->no_bukti,
            'tanggal' => $this->tanggal,
            'uraian' => $this->uraian,
            'nominal' => $this->nominal,
        ]);

        $this->refreshListTahun();
        $this->loadData();

        $this->resetInput();

        $this->js(<<<'JS'
            Swal.fire({ icon: 'success', title: 'Data berhasil diperbarui!', timer: 1500, showConfirmButton: false });
            $('#uangGiroModal').modal('hide');
        JS);
    }

    public function deleteConfirmation($id)
    {
        $this->uangGiroId = $id;

        $this->js(<<<'JS'
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data yang dihapus tidak bisa dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $wire.delete();
                }
            });
        JS);
    }

    public function delete()
    {
        ModelUangGiro::destroy($this->uangGiroId);

        $this->refreshListTahun();
        $this->loadData();

        $this->js(<<<'JS'
            Swal.fire({ icon: 'error', title: 'Data berhasil dihapus!', timer: 1500, showConfirmButton: false });
        JS);
    }
}
