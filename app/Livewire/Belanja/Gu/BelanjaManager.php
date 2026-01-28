<?php

namespace App\Livewire\Belanja\Gu;

use App\Models\Rka;
use App\Models\Belanja;
use Livewire\Component;
use App\Models\Penerima;
use App\Models\BelanjaKkpd;
use App\Models\SubKegiatan;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use App\Livewire\Laporan\LaporanNPD;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;
use App\Livewire\Laporan\LaporanBelanja;

#[Title('Belanja GU Giro')]
class BelanjaManager extends Component
{
    use WithPagination;
    use WithFileUploads;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $paginate = 10;

    public $belanja_id, $no_bukti, $tanggal, $uraian, $rka_id, $nilai;
    public $isEdit = false;
    public $formVisible = false;
    public $open = false;
    public $rka;
    public $rincian_subkegiatan = false;
    public $subkegiatan, $kodebelanja, $sisaanggaran;
    public $belanjaId;
    public $totalPenerimaan;
    public $totalPajak;
    public $bulan;
    public $selectedBelanjaId;

    public $pathWord;
    public $pathpdf;
    public $fileArsip;
    public $existingArsip;

    public $sub_kegiatan_kode, $sub_kegiatan_nama, $sisa_anggaran;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPaginate()
    {
        $this->resetPage();
    }

    public function updated($property)
    {
        // $property: The name of the current property that was updated
    }

    public function openPreview($belanjaId)
    {
        $this->selectedBelanjaId = $belanjaId;
        $this->dispatch('openPreview', $belanjaId);
        $this->js(<<<'JS'
        $('#previewBelanjaModal').modal('show');
    JS);
    }
    public function closePreview()
    {
        $this->js(<<<'JS'
        $('#previewBelanjaModal').modal('hide');
    JS);
    }

    public function render()
    {
        $sisaAnggaran = null;
        $tahun = session('tahun_anggaran', date('Y')); // Ambil tahun anggaran yang dipilih
        $bulan = $this->bulan ?? date('m'); // Ambil bulan yang dipilih, default ke bulan sekarang

        // Query belanja sesuai tahun anggaran dan bulan
        // Query belanja sesuai tahun anggaran dan bulan
        $query = Belanja::with(['penerimaan', 'pajak', 'rka.subKegiatan.kegiatan.program'])
            ->whereHas('rka.subKegiatan.kegiatan.program', function ($query) use ($tahun) {
                $query->where('tahun_anggaran', $tahun);
            })
            ->whereMonth('tanggal', $bulan) // Filter berdasarkan bulan
            ->where(function ($query) {
                $query->where('id', 'like', '%' . $this->search . '%')
                    ->orWhere('uraian', 'like', '%' . $this->search . '%')
                    ->orWhere('nilai', 'like', '%' . $this->search . '%')
                    ->orWhere('no_bukti', 'like', '%' . $this->search . '%');
            });

        // Hitung Statistik
        $totalTransaksi = (clone $query)->count();
        $totalNominal = (clone $query)->sum('nilai');

        $belanjas = $query->orderBy('id', 'desc')
            ->paginate($this->paginate);

        // Perhitungan total penerimaan dan pajak untuk setiap belanja
        foreach ($belanjas as $belanja) {
            $belanja->total_penerimaan = $belanja->penerimaan ? $belanja->penerimaan->sum('nominal') : 0;
            $belanja->total_pajak = $belanja->pajak ? $belanja->pajak->sum('nominal') : 0;
        }

        return view('livewire.belanja.gu.belanja-manager', [
            'belanja' => $belanjas,
            'penerimas' => Penerima::all(),
            'sisaAnggaran' => $sisaAnggaran,
            'totalPenerimaan' => $this->totalPenerimaan,
            'totalPajak' => $this->totalPajak,
            'bulan' => $bulan,
            'totalTransaksi' => $totalTransaksi,
            'totalNominal' => $totalNominal,
        ]);
    }


    public function toggleField($id, $field)
    {
        $belanja = Belanja::findOrFail($id);

        if (in_array($field, ['is_transfer', 'is_sipd'])) {
            $belanja->update([
                $field => !$belanja->$field // Toggle nilai 0/1
            ]);
        }
    }

    public function store()
    {


        // Validasi data yang diterima dari form
        $validatedData = $this->validate([
            'tanggal' => 'required|date',
            'uraian' => 'required',
            'rka_id' => 'required',
            'nilai' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) {
                    $rka = Rka::find($this->rka_id);
                    if (!$rka) {
                        return $fail("RKAS tidak ditemukan.");
                    }
                    // Hitung total transaksi GU dan LS (exclude transaksi yang sedang diedit)
                    $totalGU = Belanja::where('rka_id', $this->rka_id)->sum('nilai');
                    $totalKkpd = BelanjaKkpd::where('rka_id', $this->rka_id)->sum('nilai');
                    $totalLS = \App\Models\BelanjaLsDetails::where('rka_id', $this->rka_id)
                        ->where('id', '!=', $this->belanja_id)
                        ->sum('nilai');
                    $sisaAnggaran = $rka->anggaran - $totalGU - $totalKkpd - $totalLS;
                    if ($value > $sisaAnggaran) {
                        $fail("Nilai tidak boleh melebihi sisa anggaran sebesar Rp " . number_format($sisaAnggaran, 2));
                    }
                }
            ],
        ]);

        // Generate Nomor Bukti per Tahun
        $year = date('Y', strtotime($validatedData['tanggal']));
        $lastBelanja = Belanja::whereYear('tanggal', $year)
            ->orderBy('no_bukti', 'desc')
            ->first();

        $newNoBukti = $lastBelanja ? (int) $lastBelanja->no_bukti + 1 : 1;
        $formattedNoBukti = str_pad($newNoBukti, 4, '0', STR_PAD_LEFT);

        if ($this->fileArsip) {
            $validatedData['arsip'] = $this->fileArsip->store('arsip', 'gcs');
        }

        // Tambahkan nomor bukti yang sudah diformat ke dalam data yang divalidasi
        $validatedData['no_bukti'] = $formattedNoBukti;

        // Simpan data ke tabel Belanja
        Belanja::create($validatedData);

        // Tampilkan notifikasi sukses
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
        $('#belanjaModal').modal('hide');
    JS);

        // Reset input fields dan sembunyikan form
        $this->resetInputFields();
        $this->formVisible = false;
        $this->dispatch('refresh');
    }

    public function edit($id)
    {
        $belanja = Belanja::findOrFail($id);
        $this->belanja_id = $belanja->id; // Periksa nama kolom yang benar
        $this->no_bukti = $belanja->no_bukti;
        $this->tanggal = $belanja->tanggal;
        $this->uraian = $belanja->uraian;
        $this->rka_id = $belanja->rka_id;
        $this->nilai = $belanja->nilai;
        $this->existingArsip = $belanja->arsip;
        $this->fileArsip = null;

        // Menemukan data RKA dan Sub Kegiatan terkait
        $rka = Rka::with('subKegiatan')->find($belanja->rka_id);
        if ($rka) {
            $this->rka = $rka;
            $this->sub_kegiatan_kode = $rka->subKegiatan->kode ?? ''; // Penanganan jika subKegiatan tidak ada
            $this->sub_kegiatan_nama = $rka->subKegiatan->nama ?? ''; // Penanganan jika subKegiatan tidak ada
            $this->sisa_anggaran = $rka->anggaran - $rka->belanjas->sum('nilai');
        }

        $this->isEdit = true;
        $this->formVisible = true;
        $this->rincian_subkegiatan = true;
        $this->js(<<<'JS'
            $('#belanjaModal').modal('show');
         JS);
    }


    public function update()
    {
        // Validasi data
        $validatedData = $this->validate([
            'no_bukti' => 'required',
            'tanggal' => 'required|date',
            'uraian' => 'required',
            'rka_id' => 'required',
            'nilai' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) {
                    // Dapatkan anggaran dari rka_id terkait
                    $anggaran = Rka::find($this->rka_id)->anggaran;

                    // Hitung total belanja yang sudah digunakan untuk rka_id terkait
                    $totalBelanja = Belanja::where('rka_id', $this->rka_id)
                        ->where('id', '!=', $this->belanja_id) // Exclude current belanja
                        ->sum('nilai');

                    // Hitung sisa anggaran
                    $sisaAnggaran = $anggaran - $totalBelanja;

                    if ($value > $sisaAnggaran) {
                        $fail("Nilai tidak boleh melebihi sisa anggaran sebesar Rp " . number_format($sisaAnggaran, 2));
                    }
                }
            ],

        ]);


        // Temukan record belanja yang ingin diupdate
        if ($belanja = Belanja::find($this->belanja_id)) {
            $belanja->update([
                'no_bukti' => $this->no_bukti,
                'tanggal' => $this->tanggal,
                'uraian' => $this->uraian,
                'rka_id' => $this->rka_id,
                'nilai' => $this->nilai,

            ]); // Gunakan update() untuk mengupdate data

            if ($this->fileArsip) {
                if ($belanja->arsip) {
                    Storage::disk('gcs')->delete($belanja->arsip);
                }
                $belanja->update(['arsip' => $this->fileArsip->store('arsip', 'gcs')]);
            }
            $this->resetInputFields();
            $this->isEdit = false;
            $this->formVisible = false;
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
                $('#belanjaModal').modal('hide');
             JS);
        } else {
            session()->flash('error', 'Record belanja tidak ditemukan.');
        }
    }



    public function resetInputFields()
    {
        $this->no_bukti = '';
        $this->tanggal = null;
        $this->uraian = '';
        $this->nilai = null;
        $this->fileArsip = null;
        $this->existingArsip = null;
        $this->isEdit = false;
        $this->rka_id = null;
        $this->rincian_subkegiatan = false;
        $this->subkegiatan = '';
        $this->kodebelanja = '';
        $this->sisaanggaran = '';
        $this->formVisible = false;
    }

    public function openForm()
    {
        $this->resetInputFields();
        $this->isEdit = false;

        $this->refreshHierarchy();
    }
    public function closeForm()
    {
        $this->resetInputFields();
        $this->isEdit = false;
        $this->js(<<<'JS'
            $('#belanjaModal').modal('hide');
         JS);
    }



    public function openModal()
    {
        $this->js(<<<'JS'
            $('#subkegiatanModal').modal('show');
        JS);
        $this->dispatch('refresh');
    }

    public function closeModal()
    {
        $this->js(<<<'JS'
            $('#subkegiatanModal').modal('hide');
        JS);
    }
    public function closeModalPdf()
    {
        $this->js(<<<'JS'
        $('#viewBelanja').modal("hide")
    JS);
        Storage::disk('local')->delete('public/reports/laporan_belanja_' . $this->pathWord);
        Storage::disk('local')->delete('public/reports/laporan_belanja_' . $this->pathpdf);
    }
    public function refreshHierarchy()
    {
        $this->dispatch('refresh');
    }

    #[On('kirim_rka')]
    public function updatePostList($id)
    {
        $this->rka = Rka::find($id);
        $this->rka_id = $id;
        $this->closeModal();
        $this->rincian_subkegiatan = true;
    }

    public function delete_confirmation($id)
    {

        $this->belanjaId = $id;
        $this->js(<<<'JS'
        Swal.fire({
            title: 'Apakah Anda yakin?',
                text: "Apakah kamu ingin menghapus data ini? proses ini tidak dapat dikembalikan.",
                 icon: "warning",
                // imageUrl: "/icon-warning.png",
                // imageWidth: 90,
                // imageHeight: 85,
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, delete it!"
                }).then((result) => {
                if (result.isConfirmed) {
                    $wire.delete()
                }
                });
        JS);
    }

    public function delete()
    {
        $belanja = Belanja::find($this->belanjaId);
        if ($belanja) {
            if ($belanja->arsip) {
                Storage::disk('gcs')->delete($belanja->arsip);
            }
            $belanja->delete();
        }
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

    public function printTai($id)
    {
        $data = new LaporanBelanja;
        $this->pathWord = $data->getKwitansiDinasPaths($id)['word_path'];
        $this->pathpdf = $data->getKwitansiDinasPaths($id)['pdf_path'];
        $this->js(<<<'JS'
            $('#viewBelanja').modal("show")
        JS);
    }
    public function downloadTai($id)
    {
        $data = new LaporanBelanja;

        return $data->downloadKwitansiDinas($id);
    }

    public function getArsipUrl($path)
    {
        if (!$path)
            return '';
        return route('helper.show-picture', ['path' => $path, 'disk' => 'gcs']);
    }

    public $uploadBelanjaId;
    public $previewArsipUrl;

    public function updateArsipFromPreview()
    {
        $this->validate([
            'fileArsip' => 'required|mimes:pdf|max:20480',
        ]);

        $belanja = Belanja::find($this->uploadBelanjaId);
        if ($belanja) {
            // if ($belanja->arsip) {
            //     Storage::disk('gcs')->delete($belanja->arsip);
            // }
            if (!empty($belanja->arsip)) {
                if (Storage::exists($belanja->arsip)) {
                    Storage::delete($belanja->arsip);
                }
            }
            $path = $this->fileArsip->store('arsip');
            $belanja->update(['arsip' => $path]);

            $this->previewArsipUrl = $this->getArsipUrl($path);
            $this->fileArsip = null;

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
                    title: "Arsip berhasil diperbarui"
                });
            JS);
        }
    }

    public function viewArsip($id)
    {
        $this->uploadBelanjaId = $id;
        $this->fileArsip = null;
        $belanja = Belanja::find($id);
        if ($belanja && $belanja->arsip) {
            $this->previewArsipUrl = $this->getArsipUrl($belanja->arsip);
            $this->js(<<<'JS'
                $('#previewArsipModal').modal('show');
            JS);
        }
    }

    public function closeViewArsip()
    {
        $this->previewArsipUrl = null;
        $this->js(<<<'JS'
            $('#previewArsipModal').modal('hide');
        JS);
    }

    public function openUploadModal($id)
    {
        $this->uploadBelanjaId = $id;
        $this->fileArsip = null;
        $this->js(<<<'JS'
            $('#uploadArsipModal').modal('show');
        JS);
    }

    public function closeUploadModal()
    {
        $this->uploadBelanjaId = null;
        $this->fileArsip = null;
        $this->js(<<<'JS'
            $('#uploadArsipModal').modal('hide');
        JS);
    }

    public function saveArsip()
    {
        $this->validate([
            'fileArsip' => 'required|mimes:pdf|max:20480',
        ]);

        $belanja = Belanja::find($this->uploadBelanjaId);
        if ($belanja) {
            if ($belanja->arsip) {
                Storage::disk('gcs')->delete($belanja->arsip);
            }
            $path = $this->fileArsip->store('arsip');
            $belanja->update(['arsip' => $path]);

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
                    title: "Arsip berhasil diupload"
                });
                $('#uploadArsipModal').modal('hide');
            JS);
        }
        $this->uploadBelanjaId = null;
        $this->fileArsip = null;
    }
}
