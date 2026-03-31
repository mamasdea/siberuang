<?php

namespace App\Livewire\Belanja\Gu;

use App\Models\Belanja;
use App\Models\SpjGu;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;

#[Title('SPJ GU')]
class SpjGuManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $paginate = 10;

    // Form fields
    public $spj_gu_id;
    public $nomor_spj;
    public $tanggal_spj;
    public $periode_awal;
    public $periode_akhir;
    public $keterangan;
    public $isEdit = false;

    // Belanja selection
    public $selectedBelanjaIds = [];
    public $searchBelanja = '';
    public $deleteId;

    // Detail view
    public $detailSpjGu;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingSearchBelanja()
    {
        $this->resetPage('belanjaPage');
    }

    public function render()
    {
        $tahun = session('tahun_anggaran', date('Y'));

        $spjGus = SpjGu::with(['belanjas.rka.subKegiatan.kegiatan.program', 'belanjas.pajak'])
            ->whereYear('tanggal_spj', $tahun)
            ->where(function ($query) {
                $query->where('nomor_spj', 'like', '%' . $this->search . '%')
                    ->orWhere('keterangan', 'like', '%' . $this->search . '%');
            })
            ->orderBy('id', 'desc')
            ->paginate($this->paginate);

        // Get available belanja (not yet assigned to any SPJ GU, filtered by periode)
        $availableBelanjas = collect();
        if ($this->periode_awal && $this->periode_akhir) {
            $query = Belanja::with(['rka.subKegiatan.kegiatan.program', 'penerimaan', 'pajak'])
                ->whereHas('rka.subKegiatan.kegiatan.program', function ($q) use ($tahun) {
                    $q->where('tahun_anggaran', $tahun);
                })
                ->whereBetween('tanggal', [$this->periode_awal, $this->periode_akhir]);

            // Exclude belanja already in other SPJ GU (except current one being edited)
            $query->whereDoesntHave('spjGus', function ($q) {
                if ($this->spj_gu_id) {
                    $q->where('spj_gus.id', '!=', $this->spj_gu_id);
                }
            });

            if ($this->searchBelanja) {
                $query->where(function ($q) {
                    $q->where('no_bukti', 'like', '%' . $this->searchBelanja . '%')
                        ->orWhere('uraian', 'like', '%' . $this->searchBelanja . '%');
                });
            }

            $availableBelanjas = $query->orderBy('tanggal', 'asc')->get();
        }

        $totalSpj = SpjGu::whereYear('tanggal_spj', $tahun)->count();
        $totalNilaiSpj = SpjGu::whereYear('tanggal_spj', $tahun)
            ->with('belanjas')
            ->get()
            ->sum(function ($spj) {
                return $spj->belanjas->sum('nilai');
            });

        return view('livewire.belanja.gu.spj-gu-manager', [
            'spjGus' => $spjGus,
            'availableBelanjas' => $availableBelanjas,
            'totalSpj' => $totalSpj,
            'totalNilaiSpj' => $totalNilaiSpj,
        ]);
    }

    public function openForm()
    {
        $this->resetInputFields();
        $this->isEdit = false;
        $this->js(<<<'JS'
            $('#spjGuModal').modal('show');
        JS);
    }

    public function store()
    {
        $this->validate([
            'tanggal_spj' => 'required|date',
            'periode_awal' => 'required|date',
            'periode_akhir' => 'required|date|after_or_equal:periode_awal',
            'keterangan' => 'nullable',
            'selectedBelanjaIds' => 'required|array|min:1',
        ], [
            'selectedBelanjaIds.required' => 'Pilih minimal 1 belanja GU.',
            'selectedBelanjaIds.min' => 'Pilih minimal 1 belanja GU.',
        ]);

        // Generate nomor SPJ
        $year = date('Y', strtotime($this->tanggal_spj));
        $lastSpj = SpjGu::whereYear('tanggal_spj', $year)
            ->orderBy('nomor_spj', 'desc')
            ->first();

        $newNo = $lastSpj ? ((int) $lastSpj->nomor_spj + 1) : 1;
        $formattedNo = str_pad($newNo, 4, '0', STR_PAD_LEFT);

        $spjGu = SpjGu::create([
            'nomor_spj' => $formattedNo,
            'tanggal_spj' => $this->tanggal_spj,
            'periode_awal' => $this->periode_awal,
            'periode_akhir' => $this->periode_akhir,
            'keterangan' => $this->keterangan,
        ]);

        $spjGu->belanjas()->attach($this->selectedBelanjaIds);

        $this->resetInputFields();
        $this->js(<<<'JS'
            const Toast = Swal.mixin({
                toast: true,
                position: "top-end",
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
            });
            Toast.fire({ icon: "success", title: "SPJ GU berhasil disimpan" });
            $('#spjGuModal').modal('hide');
        JS);
    }

    public function edit($id)
    {
        $spjGu = SpjGu::with('belanjas')->findOrFail($id);
        $this->spj_gu_id = $spjGu->id;
        $this->nomor_spj = $spjGu->nomor_spj;
        $this->tanggal_spj = $spjGu->tanggal_spj;
        $this->periode_awal = $spjGu->periode_awal;
        $this->periode_akhir = $spjGu->periode_akhir;
        $this->keterangan = $spjGu->keterangan;
        $this->selectedBelanjaIds = $spjGu->belanjas->pluck('id')->toArray();
        $this->isEdit = true;

        $this->js(<<<'JS'
            $('#spjGuModal').modal('show');
        JS);
    }

    public function update()
    {
        $this->validate([
            'tanggal_spj' => 'required|date',
            'periode_awal' => 'required|date',
            'periode_akhir' => 'required|date|after_or_equal:periode_awal',
            'keterangan' => 'nullable',
            'selectedBelanjaIds' => 'required|array|min:1',
        ], [
            'selectedBelanjaIds.required' => 'Pilih minimal 1 belanja GU.',
            'selectedBelanjaIds.min' => 'Pilih minimal 1 belanja GU.',
        ]);

        $spjGu = SpjGu::findOrFail($this->spj_gu_id);
        $spjGu->update([
            'tanggal_spj' => $this->tanggal_spj,
            'periode_awal' => $this->periode_awal,
            'periode_akhir' => $this->periode_akhir,
            'keterangan' => $this->keterangan,
        ]);

        $spjGu->belanjas()->sync($this->selectedBelanjaIds);

        $this->resetInputFields();
        $this->js(<<<'JS'
            const Toast = Swal.mixin({
                toast: true,
                position: "top-end",
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
            });
            Toast.fire({ icon: "success", title: "SPJ GU berhasil diupdate" });
            $('#spjGuModal').modal('hide');
        JS);
    }

    public function delete_confirmation($id)
    {
        $this->deleteId = $id;
        $this->js(<<<'JS'
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Apakah kamu ingin menghapus SPJ GU ini? Proses ini tidak dapat dikembalikan.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Ya, hapus!"
            }).then((result) => {
                if (result.isConfirmed) {
                    $wire.delete()
                }
            });
        JS);
    }

    public function delete()
    {
        $spjGu = SpjGu::find($this->deleteId);
        if ($spjGu) {
            $spjGu->belanjas()->detach();
            $spjGu->delete();
        }

        $this->js(<<<'JS'
            const Toast = Swal.mixin({
                toast: true,
                position: "top-end",
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
            });
            Toast.fire({ icon: "error", title: "SPJ GU berhasil dihapus" });
        JS);
    }

    public function showDetail($id)
    {
        $this->detailSpjGu = SpjGu::with(['belanjas.rka.subKegiatan', 'belanjas.penerimaan', 'belanjas.pajak'])
            ->findOrFail($id);

        $this->js(<<<'JS'
            $('#detailSpjGuModal').modal('show');
        JS);
    }

    public function closeDetail()
    {
        $this->detailSpjGu = null;
        $this->js(<<<'JS'
            $('#detailSpjGuModal').modal('hide');
        JS);
    }

    public function closeForm()
    {
        $this->resetInputFields();
        $this->js(<<<'JS'
            $('#spjGuModal').modal('hide');
        JS);
    }

    public function toggleBelanja($belanjaId)
    {
        if (in_array($belanjaId, $this->selectedBelanjaIds)) {
            $this->selectedBelanjaIds = array_values(array_diff($this->selectedBelanjaIds, [$belanjaId]));
        } else {
            $this->selectedBelanjaIds[] = $belanjaId;
        }
    }

    public function selectAllBelanja($ids)
    {
        $ids = is_array($ids) ? $ids : json_decode($ids, true);
        foreach ($ids as $id) {
            if (!in_array($id, $this->selectedBelanjaIds)) {
                $this->selectedBelanjaIds[] = $id;
            }
        }
    }

    public function deselectAllBelanja()
    {
        $this->selectedBelanjaIds = [];
    }

    private function resetInputFields()
    {
        $this->spj_gu_id = null;
        $this->nomor_spj = '';
        $this->tanggal_spj = null;
        $this->periode_awal = null;
        $this->periode_akhir = null;
        $this->keterangan = '';
        $this->selectedBelanjaIds = [];
        $this->searchBelanja = '';
        $this->isEdit = false;
    }
}
