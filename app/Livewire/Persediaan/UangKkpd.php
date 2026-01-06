<?php

namespace App\Livewire\Persediaan;

use App\Models\UangKkpd as ModelUangKkpd;
use Livewire\Component;
use Livewire\Attributes\Title;

#[Title('Uang KKPD')]
class UangKkpd extends Component
{
    public $uangKkpds, $no_bukti, $tanggal, $uraian, $nominal, $uangKkpdId;
    public $isEditMode = false;

    // ===== Filter Tahun =====
    public ?int $tahun = null; // null = semua tahun
    public array $listTahun = [];

    protected $rules = [
        'no_bukti' => 'required|string|max:255',
        'tanggal'  => 'required|date',
        'uraian'   => 'required|string|max:255',
        'nominal'  => 'required|numeric|min:0',
    ];

    public function mount()
    {
        // default dari session tahun anggaran, fallback tahun sekarang
        $this->tahun = (int) session('tahun_anggaran', date('Y'));

        $this->refreshListTahun();
        $this->loadData();
    }

    public function render()
    {
        $this->loadData();
        return view('livewire.persediaan.uang-kkpd');
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

    protected function loadData(): void
    {
        $this->uangKkpds = ModelUangKkpd::query()
            ->when($this->tahun, fn($q) => $q->whereYear('tanggal', $this->tahun))
            ->orderBy('tanggal', 'desc')
            ->get();
    }

    public function updatedTahun()
    {
        $this->loadData();
    }

    public function resetInput()
    {
        $this->no_bukti = '';
        $this->tanggal  = '';
        $this->uraian   = '';
        $this->nominal  = '';
        $this->uangKkpdId = null;
        $this->isEditMode = false;
    }

    public function store()
    {
        $this->validate();

        ModelUangKkpd::create([
            'no_bukti' => $this->no_bukti,
            'tanggal'  => $this->tanggal,
            'uraian'   => $this->uraian,
            'nominal'  => $this->nominal,
        ]);

        $this->refreshListTahun();
        $this->loadData();

        $this->resetInput();

        $this->js(<<<'JS'
            Swal.fire({ icon: 'success', title: 'Data berhasil disimpan!', timer: 1500, showConfirmButton: false });
            $('#uangKkpdModal').modal('hide');
        JS);
    }

    public function edit($id)
    {
        $row = ModelUangKkpd::findOrFail($id);

        $this->no_bukti  = $row->no_bukti;
        $this->tanggal   = $row->tanggal;
        $this->uraian    = $row->uraian;
        $this->nominal   = $row->nominal;
        $this->uangKkpdId = $row->id;

        $this->isEditMode = true;
    }

    public function update()
    {
        $this->validate();

        $row = ModelUangKkpd::findOrFail($this->uangKkpdId);
        $row->update([
            'no_bukti' => $this->no_bukti,
            'tanggal'  => $this->tanggal,
            'uraian'   => $this->uraian,
            'nominal'  => $this->nominal,
        ]);

        $this->refreshListTahun();
        $this->loadData();

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
        $this->loadData();

        $this->js(<<<'JS'
            Swal.fire({ icon: 'error', title: 'Data berhasil dihapus!', timer: 1500, showConfirmButton: false });
        JS);
    }
}
