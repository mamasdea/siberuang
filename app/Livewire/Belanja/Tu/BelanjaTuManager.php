<?php

namespace App\Livewire\Belanja\Tu;

use App\Models\BelanjaTu;
use App\Models\SppSpmTu;
use App\Models\SppSpmTuDetail;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Storage;

#[Title('Belanja TU')]
class BelanjaTuManager extends Component
{
    use WithPagination, WithFileUploads;

    protected $paginationTheme = 'bootstrap';

    public $sppSpmTuId;
    public $sppSpmTu = [];

    public $search = '';
    public $paginate = 10;
    public $bulan;

    public $belanja_tu_id, $no_bukti, $tanggal, $uraian, $nilai, $rka_id;
    public $isEdit = false;
    public $deleteId;
    public $availableRkas = [];
    public $fileArsip;
    public $uploadArsipId;
    public $previewArsipUrl;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPaginate()
    {
        $this->resetPage();
    }

    public function mount($sppSpmTuId)
    {
        $this->sppSpmTuId = $sppSpmTuId;

        $sppSpmTu = SppSpmTu::with(['details.rka', 'belanjaTus', 'spjTu'])->findOrFail($sppSpmTuId);

        $this->sppSpmTu = [
            'no_bukti' => $sppSpmTu->no_bukti,
            'total_nilai' => $sppSpmTu->total_nilai,
            'uraian' => $sppSpmTu->uraian,
            'tanggal_sp2d' => $sppSpmTu->tanggal_sp2d,
            'has_spj' => $sppSpmTu->spjTu !== null,
        ];

        $this->buildAvailableRkas();
    }

    private function buildAvailableRkas()
    {
        $sppSpmTu = SppSpmTu::with(['details.rka', 'belanjaTus'])->findOrFail($this->sppSpmTuId);

        $this->availableRkas = $sppSpmTu->details->map(function ($detail) use ($sppSpmTu) {
            $totalTerbelanja = $sppSpmTu->belanjaTus
                ->where('rka_id', $detail->rka_id)
                ->sum('nilai');

            return [
                'id' => $detail->rka_id,
                'kode_belanja' => $detail->rka->kode_belanja ?? '',
                'nama_belanja' => $detail->rka->nama_belanja ?? '',
                'nilai_diajukan' => $detail->nilai,
                'total_terbelanja' => $totalTerbelanja,
                'sisa' => $detail->nilai - $totalTerbelanja,
            ];
        })->toArray();
    }

    public function render()
    {
        $query = BelanjaTu::with(['rka', 'pajakTus', 'penerimaanTus'])
            ->where('spp_spm_tu_id', $this->sppSpmTuId);

        if ($this->bulan) {
            $query->whereMonth('tanggal', $this->bulan);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('no_bukti', 'like', '%' . $this->search . '%')
                    ->orWhere('uraian', 'like', '%' . $this->search . '%');
            });
        }

        $totalBelanja = (clone $query)->sum('nilai');
        $sisaTu = ($this->sppSpmTu['total_nilai'] ?? 0) - $totalBelanja;

        $belanjaTus = $query->orderBy('id', 'desc')
            ->paginate($this->paginate);

        return view('livewire.belanja.tu.belanja-tu-manager', [
            'belanjaTus' => $belanjaTus,
            'availableRkas' => $this->availableRkas,
            'totalBelanja' => $totalBelanja,
            'sisaTu' => $sisaTu,
        ]);
    }

    public function store()
    {
        if ($this->sppSpmTu['has_spj'] ?? false) {
            $this->js("Swal.fire({ icon: 'error', title: 'TU sudah di-SPJ-kan, tidak bisa entri belanja lagi', timer: 2500, showConfirmButton: false });");
            return;
        }

        $validatedData = $this->validate([
            'tanggal' => 'required|date|after_or_equal:' . $this->sppSpmTu['tanggal_sp2d'],
            'uraian' => 'required',
            'rka_id' => 'required',
            'nilai' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) {
                    $rka = collect($this->availableRkas)->firstWhere('id', $this->rka_id);
                    if (!$rka) {
                        return $fail("Rekening tidak ditemukan dalam SPP-SPM TU ini.");
                    }
                    $sisa = $rka['nilai_diajukan'] - $rka['total_terbelanja'];
                    if ($value > $sisa) {
                        $fail("Nilai tidak boleh melebihi sisa rekening sebesar Rp " . number_format($sisa, 2));
                    }
                }
            ],
        ]);

        // Generate Nomor Bukti per Tahun
        $year = date('Y', strtotime($validatedData['tanggal']));
        $lastBelanja = BelanjaTu::whereYear('tanggal', $year)
            ->orderBy('no_bukti', 'desc')
            ->first();

        $newNoBukti = $lastBelanja ? (int) $lastBelanja->no_bukti + 1 : 1;
        $formattedNoBukti = str_pad($newNoBukti, 4, '0', STR_PAD_LEFT);

        $validatedData['no_bukti'] = $formattedNoBukti;
        $validatedData['spp_spm_tu_id'] = $this->sppSpmTuId;

        BelanjaTu::create($validatedData);

        $this->buildAvailableRkas();
        $this->resetInputFields();

        $this->js(<<<'JS'
            const Toast = Swal.mixin({
                toast: true,
                position: "top-end",
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.onmouseenter = Swal.stopTimer;
                    toast.onmouseleave = Swal.resumeTimer;
                }
            });
            Toast.fire({
                icon: "success",
                title: "Data berhasil disimpan"
            });
            $('#belanjaTuModal').modal('hide');
        JS);
    }

    public function edit($id)
    {
        $belanjaTu = BelanjaTu::findOrFail($id);
        $this->belanja_tu_id = $belanjaTu->id;
        $this->no_bukti = $belanjaTu->no_bukti;
        $this->tanggal = $belanjaTu->tanggal;
        $this->uraian = $belanjaTu->uraian;
        $this->rka_id = $belanjaTu->rka_id;
        $this->nilai = $belanjaTu->nilai;
        $this->isEdit = true;

        $this->js(<<<'JS'
            $('#belanjaTuModal').modal('show');
        JS);
    }

    public function update()
    {
        $validatedData = $this->validate([
            'tanggal' => 'required|date|after_or_equal:' . $this->sppSpmTu['tanggal_sp2d'],
            'uraian' => 'required',
            'rka_id' => 'required',
            'nilai' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) {
                    $rka = collect($this->availableRkas)->firstWhere('id', $this->rka_id);
                    if (!$rka) {
                        return $fail("Rekening tidak ditemukan dalam SPP-SPM TU ini.");
                    }
                    // Exclude current record from total_terbelanja
                    $currentBelanja = BelanjaTu::find($this->belanja_tu_id);
                    $currentNilai = $currentBelanja ? $currentBelanja->nilai : 0;
                    $sisa = $rka['nilai_diajukan'] - ($rka['total_terbelanja'] - $currentNilai);
                    if ($value > $sisa) {
                        $fail("Nilai tidak boleh melebihi sisa rekening sebesar Rp " . number_format($sisa, 2));
                    }
                }
            ],
        ]);

        if ($belanjaTu = BelanjaTu::find($this->belanja_tu_id)) {
            $belanjaTu->update([
                'tanggal' => $this->tanggal,
                'uraian' => $this->uraian,
                'rka_id' => $this->rka_id,
                'nilai' => $this->nilai,
            ]);

            $this->buildAvailableRkas();
            $this->resetInputFields();

            $this->js(<<<'JS'
                const Toast = Swal.mixin({
                    toast: true,
                    position: "top-end",
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.onmouseenter = Swal.stopTimer;
                        toast.onmouseleave = Swal.resumeTimer;
                    }
                });
                Toast.fire({
                    icon: "success",
                    title: "Data berhasil diupdate"
                });
                $('#belanjaTuModal').modal('hide');
            JS);
        } else {
            session()->flash('error', 'Record belanja TU tidak ditemukan.');
        }
    }

    public function delete_confirmation($id)
    {
        $this->deleteId = $id;
        $this->js(<<<'JS'
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Apakah kamu ingin menghapus data ini? Proses ini tidak dapat dikembalikan.",
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
        $belanjaTu = BelanjaTu::find($this->deleteId);
        if ($belanjaTu) {
            $belanjaTu->delete();
        }

        $this->buildAvailableRkas();

        $this->js(<<<'JS'
            const Toast = Swal.mixin({
                toast: true,
                position: "top-end",
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.onmouseenter = Swal.stopTimer;
                    toast.onmouseleave = Swal.resumeTimer;
                }
            });
            Toast.fire({
                icon: "error",
                title: "Data berhasil dihapus"
            });
        JS);
    }

    public function toggleField($id, $field)
    {
        $belanjaTu = BelanjaTu::findOrFail($id);

        if (in_array($field, ['is_transfer', 'is_sipd'])) {
            $belanjaTu->update([
                $field => !$belanjaTu->$field,
            ]);
        }
    }

    public function resetInputFields()
    {
        $this->belanja_tu_id = null;
        $this->no_bukti = '';
        $this->tanggal = null;
        $this->uraian = '';
        $this->nilai = null;
        $this->rka_id = null;
        $this->isEdit = false;
    }

    public function openForm()
    {
        $this->resetInputFields();
        $this->isEdit = false;
        $this->js(<<<'JS'
            $('#belanjaTuModal').modal('show');
        JS);
    }

    public function closeForm()
    {
        $this->resetInputFields();
        $this->isEdit = false;
        $this->js(<<<'JS'
            $('#belanjaTuModal').modal('hide');
        JS);
    }

    // === Arsip ===
    public function openUploadModal($id)
    {
        $this->uploadArsipId = $id;
        $this->fileArsip = null;
        $this->js("$('#uploadArsipTuModal').modal('show');");
    }

    public function saveArsip()
    {
        $this->validate(['fileArsip' => 'required|file|mimes:pdf|max:10240']);

        $belanja = BelanjaTu::findOrFail($this->uploadArsipId);
        if ($belanja->arsip) {
            Storage::disk('gcs')->delete($belanja->arsip);
        }
        $belanja->update(['arsip' => $this->fileArsip->store('arsip-tu', 'gcs')]);

        $this->fileArsip = null;
        $this->uploadArsipId = null;
        $this->js(<<<'JS'
            Swal.fire({ icon: 'success', title: 'Arsip berhasil diupload!', timer: 1500, showConfirmButton: false });
            $('#uploadArsipTuModal').modal('hide');
        JS);
    }

    public function viewArsip($id)
    {
        $belanja = BelanjaTu::findOrFail($id);
        if ($belanja->arsip) {
            $this->previewArsipUrl = Storage::disk('gcs')->url($belanja->arsip);
            $this->uploadArsipId = $id;
            $this->js("$('#previewArsipTuModal').modal('show');");
        }
    }

    public function closeViewArsip()
    {
        $this->previewArsipUrl = null;
        $this->js("$('#previewArsipTuModal').modal('hide');");
    }

    public function updateArsipFromPreview()
    {
        $this->validate(['fileArsip' => 'required|file|mimes:pdf|max:10240']);

        $belanja = BelanjaTu::findOrFail($this->uploadArsipId);
        if ($belanja->arsip) {
            Storage::disk('gcs')->delete($belanja->arsip);
        }
        $belanja->update(['arsip' => $this->fileArsip->store('arsip-tu', 'gcs')]);

        $this->fileArsip = null;
        $this->previewArsipUrl = Storage::disk('gcs')->url($belanja->fresh()->arsip);
        $this->js("Swal.fire({ icon: 'success', title: 'Arsip berhasil diganti!', timer: 1500, showConfirmButton: false });");
    }

    public function closeUploadModal()
    {
        $this->fileArsip = null;
        $this->uploadArsipId = null;
        $this->js("$('#uploadArsipTuModal').modal('hide');");
    }
}
