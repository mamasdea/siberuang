<?php

namespace App\Livewire\Master;

use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use App\Models\PengelolaKeuangan as ModelsPengelolaKeuangan;

#[Title('Master')]
class PengelolaKeuangan extends Component
{
    use WithPagination;

    public $search   = '';
    public $paginate = 10;
    public $pengelola, $nama, $nip, $jabatan, $bidang, $pengelola_id;
    public $tahun_anggaran, $tanggal_mulai, $tanggal_selesai, $keterangan;
    public $isEdit = false;

    public function render()
    {
        $pengelola = ModelsPengelolaKeuangan::where(function ($query) {
            $query->where('jabatan', 'like', '%' . $this->search . '%')
                ->orWhere('nama',    'like', '%' . $this->search . '%')
                ->orWhere('nip',     'like', '%' . $this->search . '%');
        })
            ->orderBy('jabatan', 'asc')
            ->orderBy('tanggal_mulai', 'asc')
            ->paginate($this->paginate);

        return view('livewire.master.pengelola-keuangan', ['asu' => $pengelola]);
    }

    public function resetInputFields()
    {
        $this->nama           = '';
        $this->nip            = '';
        $this->jabatan        = '';
        $this->bidang         = '';
        $this->tahun_anggaran = '';
        $this->tanggal_mulai  = '';
        $this->tanggal_selesai = '';
        $this->keterangan     = '';
        $this->pengelola_id   = null;
        $this->isEdit         = false;
        $this->resetValidation();
    }

    private function aturanValidasi(): array
    {
        return [
            'nama'            => 'required|string|max:255',
            'nip'             => 'required|string|max:255',
            'jabatan'         => 'required|string|max:255',
            'bidang'          => 'required|string|max:255',
            'tahun_anggaran'  => 'required|digits:4|integer',
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'keterangan'      => 'nullable|string|max:255',
        ];
    }

    /** Cari semua record yang tumpang tindih (PPTK dikecualikan). */
    private function cariOverlap(): \Illuminate\Database\Eloquent\Collection
    {
        if (strtoupper($this->jabatan) === 'PPTK') {
            return new \Illuminate\Database\Eloquent\Collection();
        }

        $query = ModelsPengelolaKeuangan::where('jabatan', $this->jabatan)
            ->where('tanggal_mulai', '<=', $this->tanggal_selesai)
            ->where('tanggal_selesai', '>=', $this->tanggal_mulai);

        if ($this->pengelola_id) {
            $query->where('id', '!=', $this->pengelola_id);
        }

        return $query->get();
    }

    /** Bangun pesan konfirmasi untuk Swal berisi detail siapa yang tumpang tindih. */
    private function pesanKonfirmasiSplit(\Illuminate\Database\Eloquent\Collection $overlaps): string
    {
        $lines = $overlaps->map(function ($r) {
            $m = Carbon::parse($r->tanggal_mulai)->format('d/m/Y');
            $s = Carbon::parse($r->tanggal_selesai)->format('d/m/Y');
            return "{$r->nama} ({$m} s/d {$s})";
        })->implode('\n');

        return "Tanggal yang diinput memotong masa aktif:\n{$lines}\n\nSistem akan otomatis menyesuaikan tanggal mereka. Lanjutkan?";
    }

    /**
     * Lakukan pemecahan (split) pada semua record yang tumpang tindih,
     * lalu simpan record baru.
     * Dipanggil dari JS setelah user konfirmasi di Swal.
     */
    public function storeDenganSplit()
    {
        // Validasi ulang karena ini dipanggil terpisah dari store()
        $this->validate($this->aturanValidasi());

        $overlaps    = $this->cariOverlap();
        $newMulai    = Carbon::parse($this->tanggal_mulai);
        $newSelesai  = Carbon::parse($this->tanggal_selesai);

        foreach ($overlaps as $existing) {
            $exMulai   = Carbon::parse($existing->tanggal_mulai);
            $exSelesai = Carbon::parse($existing->tanggal_selesai);

            $potongKiri  = $newMulai->gt($exMulai);   // periode baru mulai SETELAH existing mulai
            $potongKanan = $newSelesai->lt($exSelesai); // periode baru selesai SEBELUM existing selesai

            if ($potongKiri && $potongKanan) {
                // PLT berada DI TENGAH Definitif → belah jadi 2
                // Bagian kiri: pangkas tanggal_selesai existing
                $existing->update(['tanggal_selesai' => $newMulai->copy()->subDay()->toDateString()]);

                // Bagian kanan: buat record baru salinan existing mulai sehari setelah PLT selesai
                ModelsPengelolaKeuangan::create([
                    'nama'            => $existing->nama,
                    'nip'             => $existing->nip,
                    'jabatan'         => $existing->jabatan,
                    'bidang'          => $existing->bidang,
                    'tahun_anggaran'  => $existing->tahun_anggaran,
                    'keterangan'      => $existing->keterangan,
                    'tanggal_mulai'   => $newSelesai->copy()->addDay()->toDateString(),
                    'tanggal_selesai' => $exSelesai->toDateString(),
                ]);

            } elseif ($potongKiri) {
                // Baru memotong di EKOR existing → pangkas kanan existing
                $existing->update(['tanggal_selesai' => $newMulai->copy()->subDay()->toDateString()]);

            } elseif ($potongKanan) {
                // Baru memotong di KEPALA existing → geser kiri existing
                $existing->update(['tanggal_mulai' => $newSelesai->copy()->addDay()->toDateString()]);

            } else {
                // Baru mencakup SELURUH existing → hapus existing
                $existing->delete();
            }
        }

        // Simpan record baru (PLT / pengganti)
        ModelsPengelolaKeuangan::create([
            'nama'            => $this->nama,
            'nip'             => $this->nip,
            'jabatan'         => $this->jabatan,
            'bidang'          => $this->bidang,
            'tahun_anggaran'  => $this->tahun_anggaran,
            'tanggal_mulai'   => $this->tanggal_mulai,
            'tanggal_selesai' => $this->tanggal_selesai,
            'keterangan'      => $this->keterangan ?: null,
        ]);

        $this->resetInputFields();
        $this->js("$('#pengelolaModal').modal('hide')");
        $this->js(<<<'JS'
            Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true })
                .fire({ icon: 'success', title: 'Data disimpan & masa aktif disesuaikan otomatis' });
        JS);
    }

    public function store()
    {
        $this->validate($this->aturanValidasi());

        $overlaps = $this->cariOverlap();

        if ($overlaps->isNotEmpty()) {
            $pesan = $this->pesanKonfirmasiSplit($overlaps);
            $this->js("Swal.fire({
                icon: 'warning',
                title: 'Tanggal Tumpang Tindih',
                text: " . json_encode($pesan) . ",
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, sesuaikan otomatis',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) { \$wire.storeDenganSplit() }
            })");
            return;
        }

        ModelsPengelolaKeuangan::create([
            'nama'            => $this->nama,
            'nip'             => $this->nip,
            'jabatan'         => $this->jabatan,
            'bidang'          => $this->bidang,
            'tahun_anggaran'  => $this->tahun_anggaran,
            'tanggal_mulai'   => $this->tanggal_mulai,
            'tanggal_selesai' => $this->tanggal_selesai,
            'keterangan'      => $this->keterangan ?: null,
        ]);

        $this->resetInputFields();
        $this->js("$('#pengelolaModal').modal('hide')");
        $this->js(<<<'JS'
            Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 2000, timerProgressBar: true })
                .fire({ icon: 'success', title: 'Data berhasil disimpan' });
        JS);
    }

    public function edit($id)
    {
        $pengelola             = ModelsPengelolaKeuangan::findOrFail($id);
        $this->pengelola_id    = $id;
        $this->nama            = $pengelola->nama;
        $this->nip             = $pengelola->nip;
        $this->jabatan         = $pengelola->jabatan;
        $this->bidang          = $pengelola->bidang;
        $this->tahun_anggaran  = $pengelola->tahun_anggaran;
        $this->tanggal_mulai   = $pengelola->tanggal_mulai?->format('Y-m-d');
        $this->tanggal_selesai = $pengelola->tanggal_selesai?->format('Y-m-d');
        $this->keterangan      = $pengelola->keterangan;
        $this->isEdit          = true;
        $this->js("$('#pengelolaModal').modal('show')");
    }

    /**
     * Sama seperti storeDenganSplit tapi untuk update record yang sedang diedit.
     */
    public function updateDenganSplit()
    {
        $this->validate($this->aturanValidasi());

        $overlaps    = $this->cariOverlap(); // sudah exclude self via pengelola_id
        $newMulai    = Carbon::parse($this->tanggal_mulai);
        $newSelesai  = Carbon::parse($this->tanggal_selesai);

        foreach ($overlaps as $existing) {
            $exMulai   = Carbon::parse($existing->tanggal_mulai);
            $exSelesai = Carbon::parse($existing->tanggal_selesai);

            $potongKiri  = $newMulai->gt($exMulai);
            $potongKanan = $newSelesai->lt($exSelesai);

            if ($potongKiri && $potongKanan) {
                $existing->update(['tanggal_selesai' => $newMulai->copy()->subDay()->toDateString()]);
                ModelsPengelolaKeuangan::create([
                    'nama'            => $existing->nama,
                    'nip'             => $existing->nip,
                    'jabatan'         => $existing->jabatan,
                    'bidang'          => $existing->bidang,
                    'tahun_anggaran'  => $existing->tahun_anggaran,
                    'keterangan'      => $existing->keterangan,
                    'tanggal_mulai'   => $newSelesai->copy()->addDay()->toDateString(),
                    'tanggal_selesai' => $exSelesai->toDateString(),
                ]);
            } elseif ($potongKiri) {
                $existing->update(['tanggal_selesai' => $newMulai->copy()->subDay()->toDateString()]);
            } elseif ($potongKanan) {
                $existing->update(['tanggal_mulai' => $newSelesai->copy()->addDay()->toDateString()]);
            } else {
                $existing->delete();
            }
        }

        ModelsPengelolaKeuangan::findOrFail($this->pengelola_id)->update([
            'nama'            => $this->nama,
            'nip'             => $this->nip,
            'jabatan'         => $this->jabatan,
            'bidang'          => $this->bidang,
            'tahun_anggaran'  => $this->tahun_anggaran,
            'tanggal_mulai'   => $this->tanggal_mulai,
            'tanggal_selesai' => $this->tanggal_selesai,
            'keterangan'      => $this->keterangan ?: null,
        ]);

        $this->resetInputFields();
        $this->js("$('#pengelolaModal').modal('hide')");
        $this->js(<<<'JS'
            Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true })
                .fire({ icon: 'success', title: 'Data diupdate & masa aktif disesuaikan otomatis' });
        JS);
    }

    public function update()
    {
        $this->validate($this->aturanValidasi());

        $overlaps = $this->cariOverlap();

        if ($overlaps->isNotEmpty()) {
            $pesan = $this->pesanKonfirmasiSplit($overlaps);
            $this->js("Swal.fire({
                icon: 'warning',
                title: 'Tanggal Tumpang Tindih',
                text: " . json_encode($pesan) . ",
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, sesuaikan otomatis',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) { \$wire.updateDenganSplit() }
            })");
            return;
        }

        ModelsPengelolaKeuangan::findOrFail($this->pengelola_id)->update([
            'nama'            => $this->nama,
            'nip'             => $this->nip,
            'jabatan'         => $this->jabatan,
            'bidang'          => $this->bidang,
            'tahun_anggaran'  => $this->tahun_anggaran,
            'tanggal_mulai'   => $this->tanggal_mulai,
            'tanggal_selesai' => $this->tanggal_selesai,
            'keterangan'      => $this->keterangan ?: null,
        ]);

        $this->resetInputFields();
        $this->js("$('#pengelolaModal').modal('hide')");
        $this->js(<<<'JS'
            Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 2000, timerProgressBar: true })
                .fire({ icon: 'success', title: 'Data berhasil diupdate' });
        JS);
    }

    public function delete_confirmation($id)
    {
        $this->pengelola_id = $id;
        $this->js(<<<'JS'
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data yang dihapus tidak dapat dikembalikan.",
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
        ModelsPengelolaKeuangan::destroy($this->pengelola_id);
        $this->js(<<<'JS'
            Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 2000, timerProgressBar: true })
                .fire({ icon: 'success', title: 'Data berhasil dihapus' });
        JS);
    }
}
