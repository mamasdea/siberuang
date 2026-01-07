<?php

namespace App\Livewire\Kontrak;

use App\Models\Kontrak;
use Livewire\Component;
use App\Models\SubKegiatan;
use Livewire\WithPagination;
use App\Models\RincianKontrak;
use Livewire\Attributes\Title;
use App\Models\RincianRealisasiKontrak;

#[Title('Manajemen Kontrak')]
class KontrakManage extends Component
{
    use WithPagination;

    // Filter & paging
    public string $search = '';
    public int $paginate = 10;

    // Form fields
    public ?int $kontrak_id = null;
    public bool $isEdit = false;

    public string $nomor_kontrak = '';
    public string $tanggal_kontrak = '';
    public ?string $jangka_waktu = null; // satu kolom saja

    public ?int $sub_kegiatan_id = null; // FK
    public ?string $keperluan = null;
    public ?string $id_kontrak_lkpp = null;

    public string $nama_perusahaan = '';
    public ?string $bentuk_perusahaan = null;
    public ?string $alamat_perusahaan = null;
    public ?string $nama_pimpinan = null;
    public ?string $npwp_perusahaan = null;

    public $nilai = null; // otomatis dari rincian
    public ?string $nama_bank = null;
    public ?string $nama_pemilik_rekening = null;
    public ?string $nomor_rekening = null;

    // Dropdown
    public $listSubKegiatan = [];

    // ====== Rincian dinamis ======
    public array $items = []; // [{nama_barang, kuantitas, satuan, harga, total_harga}]
    public string $item_nama = '';
    public $item_qty = null;
    public string $item_satuan = '';
    public $item_harga = null;

    public function mount()
    {
        $this->listSubKegiatan = SubKegiatan::orderBy('kode')->get(['id', 'kode', 'nama']);
    }

    protected function rules(): array
    {
        $unique = 'unique:kontraks,nomor_kontrak';
        if ($this->kontrak_id)
            $unique .= ',' . $this->kontrak_id;

        return [
            'nomor_kontrak' => ['required', 'string', 'max:255', $unique],
            'tanggal_kontrak' => ['required', 'date'],
            'jangka_waktu' => ['nullable', 'string', 'max:255'],

            'sub_kegiatan_id' => ['required', 'exists:sub_kegiatans,id'],
            'keperluan' => ['nullable', 'string'],
            'id_kontrak_lkpp' => ['nullable', 'string', 'max:255'],

            'nama_perusahaan' => ['required', 'string', 'max:255'],
            'bentuk_perusahaan' => ['nullable', 'string', 'max:20'],
            'alamat_perusahaan' => ['nullable', 'string'],
            'nama_pimpinan' => ['nullable', 'string', 'max:255'],
            'npwp_perusahaan' => ['nullable', 'string', 'max:30'],

            // nilai akan diisi otomatis sebelum create/update
            'nilai' => ['required', 'numeric', 'min:0'],
            'nama_bank' => ['nullable', 'string', 'max:255'],
            'nama_pemilik_rekening' => ['nullable', 'string', 'max:255'],
            'nomor_rekening' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }
    public function updatingPaginate()
    {
        $this->resetPage();
    }

    protected $paginationTheme = 'bootstrap';

    public function render()
    {
        $tahun = session('tahun_anggaran', date('Y'));

        $query = Kontrak::query()
            ->whereYear('tanggal_kontrak', $tahun)   // ✅ FILTER TAHUN
            ->when($this->search, function ($q) {
                $s = "%{$this->search}%";
                $q->where(function ($w) use ($s) {
                    $w->where('nomor_kontrak', 'like', $s)
                        ->orWhere('nama_perusahaan', 'like', $s)
                        ->orWhere('id_kontrak_lkpp', 'like', $s);
                })->orWhereHas('subKegiatan', function ($r) use ($s) {
                    $r->where('nama', 'like', $s)
                        ->orWhere('kode', 'like', $s);
                });
            });

        // Hitung Statistik
        $totalKontrak = (clone $query)->count();
        $totalNilai = (clone $query)->sum('nilai');

        $rows = $query->with('subKegiatan')
            ->orderBy('tanggal_kontrak', 'desc')
            ->paginate($this->paginate);

        return view('livewire.kontrak.kontrak-manage', compact('rows', 'totalKontrak', 'totalNilai'));
    }


    /* =====================  Rincian helpers  ===================== */

    public function addItem()
    {
        $this->validate([
            'item_nama' => 'required|string|max:255',
            'item_qty' => 'required|numeric|min:0.01',
            'item_satuan' => 'nullable|string|max:50',
            'item_harga' => 'required|numeric|min:0',
        ]);

        $total = (float) $this->item_qty * (float) $this->item_harga;

        $this->items[] = [
            'nama_barang' => $this->item_nama,
            'kuantitas' => (float) $this->item_qty,
            'satuan' => $this->item_satuan,
            'harga' => (float) $this->item_harga,
            'total_harga' => $total,
        ];

        // reset form item
        $this->item_nama = '';
        $this->item_qty = null;
        $this->item_satuan = '';
        $this->item_harga = null;

        $this->recalcNilaiFromItems();
    }

    public function removeItem(int $index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        $this->recalcNilaiFromItems();
    }

    public function updatedItems()
    {
        // kalau user mengedit kolom di tabel (kalau nanti dibuat editable), tetap hitung
        foreach ($this->items as &$it) {
            $it['total_harga'] = (float) ($it['kuantitas'] ?? 0) * (float) ($it['harga'] ?? 0);
        }
        unset($it);
        $this->recalcNilaiFromItems();
    }

    public function getTotalRincianProperty(): float
    {
        return array_sum(array_map(fn($i) => (float) ($i['total_harga'] ?? 0), $this->items));
    }

    protected function recalcNilaiFromItems(): void
    {
        $this->nilai = $this->totalRincian; // nilai otomatis
    }

    /* =====================  CRUD KONTRAK  ===================== */

    public function resetInputFields()
    {
        $this->reset([
            'kontrak_id',
            'isEdit',
            'nomor_kontrak',
            'tanggal_kontrak',
            'jangka_waktu',
            'sub_kegiatan_id',
            'keperluan',
            'id_kontrak_lkpp',
            'nama_perusahaan',
            'bentuk_perusahaan',
            'alamat_perusahaan',
            'nama_pimpinan',
            'npwp_perusahaan',
            'nilai',
            'nama_bank',
            'nama_pemilik_rekening',
            'nomor_rekening',
        ]);

        $this->items = [];
        $this->nilai = 0;
    }

    public function store()
    {
        // pastikan nilai = total rincian
        $this->recalcNilaiFromItems();
        $data = $this->validate();

        // buat kontrak
        $kontrak = Kontrak::create($data);

        // simpan rincian
        foreach ($this->items as $it) {
            RincianKontrak::create($it + ['kontrak_id' => $kontrak->id]);
        }

        // recalc nilai (pengaman dobel)
        $kontrak->recalcNilai();

        $this->resetInputFields();

        $this->js(<<<'JS'
            $('#modalForm').modal('hide');
            const Toast = Swal.mixin({toast:true,position:"top-end",showConfirmButton:false,timer:2000,timerProgressBar:true});
            Toast.fire({icon:"success", title:"Data berhasil disimpan"});
        JS);
    }

    public function edit(int $id)
    {
        $k = Kontrak::with('rincians')->findOrFail($id);

        $this->fill([
            'kontrak_id' => $k->id,
            'nomor_kontrak' => $k->nomor_kontrak,
            'tanggal_kontrak' => optional($k->tanggal_kontrak)->format('Y-m-d'),
            'jangka_waktu' => $k->jangka_waktu,
            'sub_kegiatan_id' => $k->sub_kegiatan_id,
            'keperluan' => $k->keperluan,
            'id_kontrak_lkpp' => $k->id_kontrak_lkpp,
            'nama_perusahaan' => $k->nama_perusahaan,
            'bentuk_perusahaan' => $k->bentuk_perusahaan,
            'alamat_perusahaan' => $k->alamat_perusahaan,
            'nama_pimpinan' => $k->nama_pimpinan,
            'npwp_perusahaan' => $k->npwp_perusahaan,
            'nilai' => (float) $k->nilai,
            'nama_bank' => $k->nama_bank,
            'nama_pemilik_rekening' => $k->nama_pemilik_rekening,
            'nomor_rekening' => $k->nomor_rekening,
        ]);

        // muat rincian ke form dinamis
        $this->items = $k->rincians->map(function ($r) {
            return [
                'nama_barang' => $r->nama_barang,
                'kuantitas' => (float) $r->kuantitas,
                'satuan' => (string) $r->satuan,
                'harga' => (float) $r->harga,
                'total_harga' => (float) $r->total_harga,
            ];
        })->toArray();

        $this->isEdit = true;
        $this->js("$('#modalForm').modal('show');");
    }

    public function update()
    {
        // pastikan nilai = total rincian
        $this->recalcNilaiFromItems();
        $this->validate();

        $k = Kontrak::findOrFail($this->kontrak_id);
        $k->update($this->only(array_keys($this->rules())));

        // replace rincian
        $k->rincians()->delete();
        foreach ($this->items as $it) {
            RincianKontrak::create($it + ['kontrak_id' => $k->id]);
        }
        $k->recalcNilai();

        $this->resetInputFields();

        $this->js(<<<'JS'
            const Toast = Swal.mixin({toast:true,position:"top-end",showConfirmButton:false,timer:2000,timerProgressBar:true});
            Toast.fire({icon:"success", title:"Data berhasil diupdate"});
            $('#modalForm').modal('hide');
        JS);
    }

    public function delete_confirmation(int $id)
    {
        $this->kontrak_id = $id;

        $this->js(<<<'JS'
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Proses ini tidak dapat dikembalikan.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Ya, hapus!"
            }).then((result) => {
                if (result.isConfirmed) { $wire.delete() }
            });
        JS);
    }

    public function delete()
    {
        // Pastikan id ada
        if (!$this->kontrak_id)
            return;

        // Cek: ada rincian realisasi untuk kontrak ini?
        $hasRincianRealisasi = RincianRealisasiKontrak::whereHas('realisasi', function ($q) {
            $q->where('kontrak_id', $this->kontrak_id);
        })->exists();

        if ($hasRincianRealisasi) {
            // TOLAK penghapusan
            $this->js(<<<'JS'
            Swal.fire({
                icon: 'error',
                title: 'Tidak bisa dihapus',
                text: 'Kontrak sudah memiliki rincian realisasi. Hapus realisasi terlebih dahulu bila memang diperlukan.'
            });
        JS);
            return;
        }

        // Aman dihapus
        Kontrak::destroy($this->kontrak_id);
        $this->kontrak_id = null;

        $this->js(<<<'JS'
        const Toast = Swal.mixin({toast:true,position:"top-end",showConfirmButton:false,timer:2000,timerProgressBar:true});
        Toast.fire({icon:"error", title:"Data berhasil dihapus"});
    JS);
    }
}
