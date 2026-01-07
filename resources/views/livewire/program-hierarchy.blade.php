@push('css')
    <x-styles.modern-ui />
@endpush

<div>
    <div class="px-1 py-2">
        <div class="search-box mb-4">
            <i class="fas fa-search search-icon"></i>
            <input type="text" wire:model.live="search" placeholder="Cari Sub Kegiatan..."
                class="form-control search-input" />
        </div>

        @if($subKegiatans->isEmpty())
             <div class="text-center py-5">
                 <div class="empty-state">
                     <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                     <p class="text-muted">Tidak ada data ditemukan</p>
                 </div>
             </div>
        @else
            @foreach ($subKegiatans as $subKegiatan)
                <div class="mb-2">
                    <div class="card-header bg-white px-3 py-2 cursor-pointer d-flex justify-content-between align-items-center shadow-sm"
                        wire:click="selectSubKegiatan({{ $subKegiatan->id }})" 
                        style="border: 1px solid #e2e8f0; border-radius: {{ $selectedSubKegiatanId === $subKegiatan->id ? '12px 12px 0 0' : '12px' }}; transition: all 0.2s;">
                        <div class="d-flex align-items-center flex-grow-1 overflow-hidden">
                            <span class="code-badge mr-3 flex-shrink-0">
                                {{ $subKegiatan->kode }}
                            </span>
                            <span class="font-weight-bold text-dark text-truncate" title="{{ $subKegiatan->nama }}">
                                {{ $subKegiatan->nama }}
                            </span>
                        </div>
                        <div class="pl-3">
                            <i class="fas fa-chevron-{{ $selectedSubKegiatanId === $subKegiatan->id ? 'up' : 'down' }} text-secondary"></i>
                        </div>
                    </div>
                    
                    @if ($selectedSubKegiatanId === $subKegiatan->id)
                        <div class="bg-light p-3 shadow-inner" style="border: 1px solid #e2e8f0; border-top: none; border-radius: 0 0 12px 12px;">
                            <div class="table-responsive bg-white rounded border">
                                <table class="table modern-table mb-0">
                                    <thead>
                                        <tr>
                                            <th width="20%">Kode Belanja</th>
                                            <th>Nama Belanja</th>
                                            <th width="25%" class="text-right">Sisa Anggaran</th>
                                            <th width="10%" class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if($subKegiatan->rkas->isEmpty())
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-3">Tidak ada rekening belanja</td>
                                            </tr>
                                        @else
                                            @foreach ($subKegiatan->rkas as $rka)
                                                <tr>
                                                    <td><span class="code-badge">{{ $rka->kode_belanja }}</span></td>
                                                    <td>{{ $rka->nama_belanja }}</td>
                                                    <td class="text-right">
                                                        <span class="amount-badge">Rp {{ number_format($rka->sisaAnggaran, 0, ',', '.') }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <button class="btn btn-modern-add btn-sm py-1 px-3"
                                                            wire:click="kirim({{ $rka->id }})" title="Pilih">
                                                            Pilih
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        @endif
    </div>
</div>
