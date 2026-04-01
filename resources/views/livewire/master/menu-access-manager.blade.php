@push('css')
    <x-styles.modern-ui />
@endpush

<div>
    <div class="modern-card fade-in-up">
        <div class="card-header-modern">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="page-title">Pengaturan Akses Menu</h3>
                    <p class="page-subtitle mb-0">Kelola hak akses menu berdasarkan role pengguna</p>
                </div>
            </div>
        </div>

        <div class="content-card">
            <div class="table-responsive">
                <table class="table modern-table">
                    <thead>
                        <tr>
                            <th width="50">No</th>
                            <th>Menu</th>
                            @foreach($roles as $role)
                                <th width="150" class="text-center">
                                    <span class="badge {{ $role === 'admin' ? 'badge-danger' : 'badge-primary' }}" style="font-size: 13px; padding: 6px 16px;">
                                        {{ ucfirst($role) }}
                                    </span>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($menus as $key => $label)
                            <tr>
                                <td><span class="code-badge">{{ $loop->iteration }}</span></td>
                                <td style="font-weight: 600;">
                                    <i class="fas fa-{{ $key === 'master' ? 'cogs' : ($key === 'anggaran' ? 'dollar-sign' : ($key === 'belanja' ? 'shopping-cart' : ($key === 'kontrak' ? 'file-invoice' : ($key === 'laporan' ? 'file-alt' : 'file-signature')))) }} mr-2 text-muted"></i>
                                    {{ $label }}
                                </td>
                                @foreach($roles as $role)
                                    <td class="text-center">
                                        @if($role === 'admin' && $key === 'master')
                                            {{-- Admin Master selalu aktif, tidak bisa diubah --}}
                                            <div class="custom-control custom-switch d-inline-block">
                                                <input type="checkbox" class="custom-control-input" id="perm_{{ $role }}_{{ $key }}" checked disabled>
                                                <label class="custom-control-label" for="perm_{{ $role }}_{{ $key }}"></label>
                                            </div>
                                            <br><small class="text-muted" style="font-size: 10px;">Terkunci</small>
                                        @else
                                            <div class="custom-control custom-switch d-inline-block">
                                                <input type="checkbox" class="custom-control-input" id="perm_{{ $role }}_{{ $key }}"
                                                    wire:click="togglePermission('{{ $role }}', '{{ $key }}')"
                                                    {{ ($permissions[$role][$key] ?? false) ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="perm_{{ $role }}_{{ $key }}"></label>
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="alert alert-info mt-3 d-flex align-items-center" style="border-radius: 8px;">
                <i class="fas fa-info-circle mr-2"></i>
                <span>Perubahan akses langsung berlaku. Menu "Master" untuk Admin tidak bisa dinonaktifkan untuk mencegah kehilangan akses.</span>
            </div>
        </div>
    </div>
</div>
