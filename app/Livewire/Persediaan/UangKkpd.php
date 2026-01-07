<?php

namespace App\Livewire\Persediaan;

use App\Models\UangKkpd as ModelUangKkpd;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\WithPagination;

#[Title('Uang KKPD')]
class UangKkpd extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $no_bukti, $tanggal, $uraian, $nominal, $uangKkpdId;
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
        // default dari session tahun anggaran, fallback tahun sekarang
        $this->tahun = (int) session('tahun_anggaran', date('Y'));

        $this->refreshListTahun();
    }

    public function render()
    {
        $uangKkpds = ModelUangKkpd::query()
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

        return view('livewire.persediaan.uang-kkpd', [
            'uangKkpds' => $uangKkpds,
            'totalTransaksi' => ModelUangKkpd::query()
                ->when($this->tahun, fn($q) => $q->whereYear('tanggal', $this->tahun))
                ->when($this->search, function ($q) {
                    $q->where(function ($query) {
                        $query->where('no_bukti', 'like', '%' . $this->search . '%')
                            ->orWhere('uraian', 'like', '%' . $this->search . '%')
                            ->orWhere('tanggal', 'like', '%' . $this->search . '%');
                    });
                })
                ->count(),
            'totalNominal' => ModelUangKkpd::query()
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
        $this->listTahun = ModelUangKkpd::query()
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
        $this->uangKkpdId = null;
        $this->isEditMode = false;
    }

    public function store()
    {
        $this->validate();

        ModelUangKkpd::create([
            'no_bukti' => $this->no_bukti,
            'tanggal' => $this->tanggal,
            'uraian' => $this->uraian,
            'nominal' => $this->nominal,
        ]);

        $this->refreshListTahun();

        $this->resetInput();

        $this->js(<<<'JS'
            Swal.fire({ icon: 'success', title: 'Data berhasil disimpan!', timer: 1500, showConfirmButton: false });
            $('#uangKkpdModal').modal('hide');
        JS);
    }

    public function edit($id)
    {
        $row = ModelUangKkpd::findOrFail($id);

        $this->no_bukti = $row->no_bukti;
        $this->tanggal = $row->tanggal;
        $this->uraian = $row->uraian;
        $this->nominal = $row->nominal;
        $this->uangKkpdId = $row->id;

        $this->isEditMode = true;
    }

    public function update()
    {
        $this->validate();

        $row = ModelUangKkpd::findOrFail($this->uangKkpdId);
        $row->update([
            'no_bukti' => $this->no_bukti,
            'tanggal' => $this->tanggal,
            'uraian' => $this->uraian,
            'nominal' => $this->nominal,
        ]);

        $this->refreshListTahun();

        $this->resetInput();

        $this->js(<<<'JS'
            Swal.fire({ icon: 'success', title: 'Data berhasil diperbarui!', timer: 1500, showConfirmButton: false });
            $('#uangKkpdModal').modal('hide');
        JS);
    }

    public function deleteConfirmation($id)
    {
        $this->uangKkpdId = $id;

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
        ModelUangKkpd::destroy($this->uangKkpdId);

        $this->refreshListTahun();

        $this->js(<<<'JS'
            Swal.fire({ icon: 'error', title: 'Data berhasil dihapus!', timer: 1500, showConfirmButton: false });
        JS);
    }
}
