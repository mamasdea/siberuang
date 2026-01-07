<?php

namespace App\Livewire\Belanja\Kkpd;

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
use App\Livewire\Laporan\LaporanBelanja;
use App\Livewire\Laporan\LaporanBelanjaKkpd;

#[Title('Belanja GU KKPD')]
class BelanjaKkpdsManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $paginate = 10;

    public function updatingSearch()
    {
        $this->resetPage();
    }
    public $belanjas;
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

    public $sub_kegiatan_kode, $sub_kegiatan_nama, $sisa_anggaran;

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
        $query = BelanjaKkpd::with(['penerimaankkpd', 'pajakkkpd', 'rka.subKegiatan.kegiatan.program'])
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
            $belanja->total_penerimaan = $belanja->penerimaankkpd ? $belanja->penerimaankkpd->sum('nominal') : 0;
            $belanja->total_pajak = $belanja->pajakkkpd ? $belanja->pajakkkpd->sum('nominal') : 0;
        }

        return view('livewire.belanja.kkpd.belanja-kkpds-manager', [
            'belanjaKkpds' => $belanjas,
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
        $belanja = BelanjaKkpd::findOrFail($id);

        if (in_array($field, ['is_transfer', 'is_sipd'])) {
            $belanja->update([
                $field => !$belanja->$field // Toggle nilai 0/1
            ]);
        }
    }

    public function store()
    {
        // Ambil nomor bukti terakhir dari database
        $lastNoBukti = BelanjaKkpd::orderBy('no_bukti', 'desc')->first();

        // Jika belum ada nomor bukti, mulai dari 1
        $newNoBukti = $lastNoBukti ? (int) $lastNoBukti->no_bukti + 1 : 1;

        // Format nomor bukti menjadi 4 digit (contoh: 0001)
        $formattedNoBukti = str_pad($newNoBukti, 4, '0', STR_PAD_LEFT);

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

        // Tambahkan nomor bukti yang sudah diformat ke dalam data yang divalidasi
        $validatedData['no_bukti'] = $formattedNoBukti;

        // Simpan data ke tabel Belanja
        BelanjaKkpd::create($validatedData);

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
        $belanja = BelanjaKkpd::findOrFail($id);
        $this->belanja_id = $belanja->id; // Periksa nama kolom yang benar
        $this->no_bukti = $belanja->no_bukti;
        $this->tanggal = $belanja->tanggal;
        $this->uraian = $belanja->uraian;
        $this->rka_id = $belanja->rka_id;
        $this->nilai = $belanja->nilai;

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
                    $totalBelanjaKkpd = BelanjaKkpd::where('rka_id', $this->rka_id)
                        ->where('id', '!=', $this->belanja_id) // Exclude current belanja
                        ->sum('nilai');

                    // Hitung sisa anggaran
                    $sisaAnggaran = $anggaran - $totalBelanjaKkpd;

                    if ($value > $sisaAnggaran) {
                        $fail("Nilai tidak boleh melebihi sisa anggaran sebesar Rp " . number_format($sisaAnggaran, 2));
                    }
                }
            ],

        ]);


        // Temukan record belanja yang ingin diupdate
        if ($belanja = BelanjaKkpd::find($this->belanja_id)) {
            $belanja->update([
                'no_bukti' => $this->no_bukti,
                'tanggal' => $this->tanggal,
                'uraian' => $this->uraian,
                'rka_id' => $this->rka_id,
                'nilai' => $this->nilai,

            ]); // Gunakan update() untuk mengupdate data
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
        // ModelRekeningBelanjaKkpd::find($id)->delete();
        BelanjaKkpd::destroy($this->belanjaId);
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
        $data = new LaporanBelanjaKkpd;
        $this->pathWord = $data->getKwitansiDinasPaths($id)['word_path'];
        $this->pathpdf = $data->getKwitansiDinasPaths($id)['pdf_path'];
        $this->js(<<<'JS'
                $('#viewBelanja').modal("show")
            JS);
    }
    public function downloadTai($id)
    {
        $data = new LaporanBelanjaKkpd;

        return $data->downloadKwitansiDinas($id);
    }
}
