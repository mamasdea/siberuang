@push('css')
<style>
    :root {
        --primary-color: #2563eb;
        --primary-dark: #1e40af;
        --secondary-color: #64748b;
        --success-color: #10b981;
        --danger-color: #ef4444;
        --warning-color: #f59e0b;
        --light-bg: #f8fafc;
        --border-color: #e2e8f0;
        --text-primary: #1e293b;
        --text-secondary: #64748b;
    }

    body {
        background: var(--light-bg);
        color: var(--text-primary);
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    .modern-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border: 1px solid var(--border-color);
        overflow: hidden;
        margin-bottom: 24px;
    }

    .card-header-modern {
        background: white;
        border-bottom: 1px solid var(--border-color);
        padding: 32px;
    }

    .page-title {
        color: var(--text-primary);
        font-size: 24px;
        font-weight: 700;
        margin: 0;
        letter-spacing: -0.025em;
    }

    .page-subtitle {
        color: var(--text-secondary);
        font-size: 14px;
        margin-top: 4px;
        font-weight: 400;
    }

    .btn-modern-import {
        background: var(--primary-color);
        border: none;
        color: white;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 500;
        font-size: 14px;
        transition: all 0.2s ease;
    }

    .btn-modern-import:hover {
        background: var(--primary-dark);
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 4px 6px rgba(37, 99, 235, 0.2);
    }

    .modern-tabs {
        background: transparent;
        padding: 0 32px;
        border-bottom: 1px solid var(--border-color);
        margin: 0;
    }

    .modern-tabs .nav-link {
        border: none;
        border-radius: 0;
        padding: 16px 24px;
        font-weight: 500;
        color: var(--text-secondary);
        font-size: 14px;
        transition: all 0.2s ease;
        border-bottom: 2px solid transparent;
        background: transparent;
        margin: 0;
    }

    .modern-tabs .nav-link:hover {
        color: var(--primary-color);
        background: transparent;
        border-bottom-color: var(--primary-color);
    }

    .modern-tabs .nav-link.active {
        color: var(--primary-color);
        background: transparent;
        border-bottom-color: var(--primary-color);
        font-weight: 600;
    }

    .content-card {
        background: transparent;
        padding: 32px;
    }

    .section-header {
        margin-bottom: 24px;
    }

    .section-title {
        color: var(--text-primary);
        font-size: 18px;
        font-weight: 600;
        margin: 0;
    }

    .section-subtitle {
        color: var(--text-secondary);
        font-size: 13px;
        margin-top: 2px;
    }

    .btn-add-modern {
        background: var(--primary-color);
        border: none;
        color: white;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 500;
        font-size: 14px;
        transition: all 0.2s ease;
    }

    .btn-add-modern:hover {
        background: var(--primary-dark);
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 4px 6px rgba(37, 99, 235, 0.2);
    }

    .modern-table {
        border: 1px solid var(--border-color);
        border-radius: 12px;
        overflow: hidden;
        background: white;
    }

    .modern-table thead {
        background: #f8fafc;
        border-bottom: 1px solid var(--border-color);
    }

    .modern-table thead th {
        color: var(--text-secondary);
        font-weight: 600;
        padding: 12px 16px;
        border: none;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .modern-table tbody tr {
        transition: background 0.15s ease;
        border-bottom: 1px solid var(--border-color);
    }

    .modern-table tbody tr:last-child {
        border-bottom: none;
    }

    .modern-table tbody tr:hover {
        background: #f8fafc;
    }

    .modern-table tbody td {
        padding: 16px;
        vertical-align: middle;
        border: none;
        color: var(--text-primary);
    }

    .code-badge {
        background: #f1f5f9;
        color: var(--text-primary);
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 500;
        font-family: 'Monaco', 'Menlo', monospace;
    }

    .amount-badge {
        background: #f0fdf4;
        color: #166534;
        padding: 6px 12px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 14px;
        border: 1px solid #bbf7d0;
    }

    .btn-action-edit {
        background: #fffbeb;
        border: 1px solid #fcd34d;
        color: var(--warning-color);
        padding: 8px 12px;
        border-radius: 6px;
        transition: all 0.15s ease;
        margin: 0 3px;
        font-size: 14px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    }

    .btn-action-edit:hover {
        background: var(--warning-color);
        border-color: var(--warning-color);
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 4px 6px rgba(245, 158, 11, 0.2);
    }

    .btn-action-delete {
        background: #fef2f2;
        border: 1px solid #fca5a5;
        color: var(--danger-color);
        padding: 8px 12px;
        border-radius: 6px;
        transition: all 0.15s ease;
        margin: 0 3px;
        font-size: 14px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    }

    .btn-action-delete:hover {
        background: var(--danger-color);
        border-color: var(--danger-color);
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 4px 6px rgba(239, 68, 68, 0.2);
    }

    .btn-action-next {
        background: #eff6ff;
        border: 1px solid #93c5fd;
        color: var(--primary-color);
        padding: 8px 12px;
        border-radius: 6px;
        transition: all 0.15s ease;
        margin: 0 3px;
        font-size: 14px;
        font-weight: 500;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    }

    .btn-action-next:hover {
        background: var(--primary-color);
        border-color: var(--primary-color);
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 4px 6px rgba(37, 99, 235, 0.2);
    }

    .btn-group {
        display: inline-flex;
        gap: 6px;
    }

    .btn-group .btn {
        margin: 0 !important;
    }

    .empty-state {
        text-align: center;
        padding: 64px 32px;
        color: var(--text-secondary);
    }

    .empty-state i {
        font-size: 48px;
        color: #cbd5e1;
        margin-bottom: 16px;
    }

    .empty-state p {
        font-size: 14px;
        margin: 8px 0 16px 0;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }

    .fade-in-up {
        animation: fadeIn 0.3s ease-out;
    }

    .modal-content {
        border-radius: 12px;
        border: 1px solid var(--border-color);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }

    .modal-header {
        background: white;
        color: var(--text-primary);
        border-bottom: 1px solid var(--border-color);
        border-radius: 12px 12px 0 0;
        padding: 20px 24px;
    }

    .modal-title {
        font-weight: 600;
        font-size: 18px;
        color: var(--text-primary);
    }

    .modal-header .close {
        color: var(--text-secondary);
        opacity: 1;
        text-shadow: none;
        font-size: 24px;
        padding: 0;
        margin: 0;
    }

    .modal-header .close:hover {
        color: var(--text-primary);
    }

    .modal-body {
        padding: 24px;
    }

    .form-group label {
        font-weight: 500;
        color: var(--text-primary);
        margin-bottom: 8px;
        font-size: 14px;
    }

    .form-control {
        border-radius: 8px;
        border: 1px solid var(--border-color);
        padding: 10px 12px;
        transition: all 0.15s ease;
        font-size: 14px;
    }

    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .btn-primary {
        background: var(--primary-color);
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 500;
        font-size: 14px;
        transition: all 0.2s ease;
    }

    .btn-primary:hover {
        background: var(--primary-dark);
        transform: translateY(-1px);
        box-shadow: 0 4px 6px rgba(37, 99, 235, 0.2);
    }

    .btn-secondary {
        background: white;
        border: 1px solid var(--border-color);
        color: var(--text-secondary);
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 500;
        font-size: 14px;
        transition: all 0.2s ease;
    }

    .btn-secondary:hover {
        background: #f8fafc;
        border-color: var(--text-secondary);
        color: var(--text-primary);
    }

    /* Stats Cards */
    .stats-container {
        padding: 0 32px 24px 32px;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 16px;
    }

    .stat-card {
        background: white;
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 20px;
        transition: all 0.2s ease;
    }

    .stat-card:hover {
        border-color: var(--primary-color);
        box-shadow: 0 4px 6px rgba(37, 99, 235, 0.1);
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        margin-bottom: 12px;
    }

    .stat-icon.blue {
        background: #eff6ff;
        color: var(--primary-color);
    }

    .stat-icon.green {
        background: #f0fdf4;
        color: var(--success-color);
    }

    .stat-icon.purple {
        background: #faf5ff;
        color: #9333ea;
    }

    .stat-icon.orange {
        background: #fff7ed;
        color: #ea580c;
    }

    .stat-label {
        font-size: 13px;
        color: var(--text-secondary);
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 4px;
    }

    .stat-value {
        font-size: 28px;
        font-weight: 700;
        color: var(--text-primary);
        line-height: 1.2;
    }

    .stat-description {
        font-size: 12px;
        color: var(--text-secondary);
        margin-top: 4px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .card-header-modern {
            padding: 20px;
        }

        .page-title {
            font-size: 20px;
        }

        .page-subtitle {
            font-size: 13px;
        }

        .stats-container {
            padding: 0 20px 20px 20px;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 12px;
        }

        .stat-card {
            padding: 16px;
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            font-size: 18px;
            margin-bottom: 8px;
        }

        .stat-value {
            font-size: 22px;
        }

        .stat-label {
            font-size: 11px;
        }

        .modern-tabs {
            padding: 0 20px;
            overflow-x: auto;
            white-space: nowrap;
        }

        .modern-tabs .nav-link {
            padding: 12px 16px;
            font-size: 13px;
        }

        .content-card {
            padding: 20px;
        }

        .btn-action-edit,
        .btn-action-delete,
        .btn-action-next {
            padding: 6px 10px;
            font-size: 13px;
            margin: 0 2px;
        }

        .btn-group {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            justify-content: center;
        }
    }

    /* Custom File Upload Styles */
    .custom-file-upload-area {
        margin: 20px 0;
    }

    .file-upload-label {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 40px 20px;
        border: 2px dashed var(--border-color);
        border-radius: 12px;
        background: var(--light-bg);
        cursor: pointer;
        transition: all 0.3s ease;
        margin-bottom: 0;
    }

    .file-upload-label:hover {
        border-color: var(--primary-color);
        background: #f0f7ff;
    }

    .upload-icon {
        font-size: 48px;
        color: var(--primary-color);
        margin-bottom: 16px;
    }

    .upload-text {
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-bottom: 16px;
    }

    .upload-title {
        font-size: 16px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 4px;
    }

    .upload-subtitle {
        font-size: 13px;
        color: var(--text-secondary);
    }

    .btn-browse {
        display: inline-block;
        padding: 10px 24px;
        background: var(--primary-color);
        color: white;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .btn-browse:hover {
        background: var(--primary-dark);
        transform: translateY(-1px);
    }

    .file-input-hidden {
        display: none;
    }

    .selected-file-info {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 16px;
        background: #f0fdf4;
        border: 1px solid #86efac;
        border-radius: 8px;
        margin-top: 16px;
    }

    .selected-file-info i.fa-file-excel {
        font-size: 24px;
    }

    .selected-file-info .file-name {
        flex: 1;
        font-weight: 500;
        color: var(--text-primary);
    }

    .btn-remove-file {
        background: none;
        border: none;
        color: var(--danger-color);
        cursor: pointer;
        padding: 4px 8px;
        border-radius: 4px;
        transition: all 0.2s ease;
    }

    .btn-remove-file:hover {
        background: #fee2e2;
    }

    .file-error {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 12px;
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-radius: 8px;
        color: var(--danger-color);
        font-size: 14px;
        margin-top: 12px;
    }

    .btn-modern-primary {
        background: var(--primary-color);
        color: white;
        border: none;
        padding: 10px 24px;
        border-radius: 8px;
        font-weight: 500;
        font-size: 14px;
        transition: all 0.2s ease;
    }

    .btn-modern-primary:hover {
        background: var(--primary-dark);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
    }

    .btn-modern-primary:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }

    .file-loading {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 16px;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 8px;
        color: var(--primary-color);
        font-size: 14px;
        font-weight: 500;
        margin-top: 16px;
    }
</style>
@endpush

<div>
    <div class="modern-card fade-in-up">
        <div class="card-header-modern">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="page-title">Data Rencana Kerja Anggaran</h3>
                    <p class="page-subtitle mb-0">Kelola Program, Kegiatan, dan Anggaran Perangkat Daerah</p>
                </div>
                <div>
                    <button type="button" class="btn btn-modern-import" data-toggle="modal"
                        data-target="#importModalProgram">
                        <i class="fas fa-file-import mr-2"></i>Import
                    </button>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-folder"></i>
                </div>
                <div class="stat-label">Total Program</div>
                <div class="stat-value">{{ $totalPrograms }}</div>
                <div class="stat-description">Program aktif tahun {{ $tahun_anggaran }}</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-tasks"></i>
                </div>
                <div class="stat-label">Total Kegiatan</div>
                <div class="stat-value">{{ $totalKegiatans }}</div>
                <div class="stat-description">Kegiatan yang terdaftar</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon purple">
                    <i class="fas fa-list-ul"></i>
                </div>
                <div class="stat-label">Sub Kegiatan</div>
                <div class="stat-value">{{ $totalSubKegiatans }}</div>
                <div class="stat-description">Sub kegiatan yang terdaftar</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon orange">
                    <i class="fas fa-wallet"></i>
                </div>
                <div class="stat-label">Total Anggaran</div>
                <div class="stat-value">{{ number_format($totalAnggaran / 1000000, 1) }}M</div>
                <div class="stat-description">Rp {{ number_format($totalAnggaran, 0, ',', '.') }}</div>
            </div>
        </div>

        <!-- Modern Nav tabs -->
        <ul class="nav modern-tabs">
            <li class="nav-item">
                <a class="nav-link active" href="#dokumen" data-toggle="tab">Program</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#sasaran" data-toggle="tab" id="kegiatan">Kegiatan</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#subKegiatan" id="subkegiatantab" data-toggle="tab">Sub Kegiatan</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#rka" id="Rkatab" data-toggle="tab">Anggaran</a>
            </li>
        </ul>
        <!-- Tab content -->
        <div class="tab-content">
            <!-- Program Tab -->
            <div class="tab-pane active fade show" id="dokumen">
                <div class="content-card">
                    <div class="d-flex justify-content-between align-items-center section-header">
                        <div>
                            <h5 class="section-title">Daftar Program</h5>
                            <p class="section-subtitle mb-0">Kelola semua program kerja anggaran</p>
                        </div>
                        <div>
                            <button class="btn btn-add-modern" data-toggle="modal"
                                data-target="#programModal">
                                <i class="fas fa-plus mr-2"></i>Tambah Program
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table modern-table">
                            <thead>
                                <tr>
                                    <th width="140">Kode Program</th>
                                    <th>Nama Program</th>
                                    <th width="180">Pagu Program</th>
                                    <th width="200" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($programs as $program)
                                    <tr>
                                        <td>
                                            <span class="code-badge">{{ $program->kode }}</span>
                                        </td>
                                        <td style="font-weight: 500;">{{ $program->nama }}</td>
                                        <td>
                                            <span class="amount-badge">
                                                Rp {{ number_format($program->total, 0, ',', '.') }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <button wire:click="edit({{ $program->id }})"
                                                    class="btn btn-action-edit" title="Edit"
                                                    type="button">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button wire:click="delete_confirmation({{ $program->id }})"
                                                    class="btn btn-action-delete" title="Hapus"
                                                    type="button">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                                <button wire:click="next({{ $program->id }})"
                                                    class="btn btn-action-next" title="Lihat Kegiatan"
                                                    type="button">
                                                    <i class="fas fa-arrow-right"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4">
                                            <div class="empty-state">
                                                <i class="fas fa-folder-open"></i>
                                                <p>Belum ada data program</p>
                                                <button class="btn btn-add-modern btn-sm" data-toggle="modal"
                                                    data-target="#programModal">
                                                    <i class="fas fa-plus mr-2"></i>Tambah Program Pertama
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- Kegiatan Tab -->
            <div class="tab-pane fade" id="sasaran">
                <div class="content-card">
                    <livewire:anggaran.kegiatan-component :program_id="$programId" />
                </div>
            </div>

            <!-- Sub Kegiatan Tab -->
            <div class="tab-pane fade" id="subKegiatan">
                <div class="content-card">
                    <livewire:anggaran.sub-kegiatan-component :kegiatan_id="$kegiatanId" />
                </div>
            </div>

            <!-- RKA Tab -->
            <div class="tab-pane fade" id="rka">
                <div class="content-card">
                    <livewire:anggaran.r-k-a-component :sub_kegiatan_id="$subKegiatanId" />
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Form -->
    <div wire:ignore.self class="modal fade" id="programModal" tabindex="-1" aria-labelledby="programModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="programModalLabel">
                        {{ $isEditMode ? 'Edit Program' : 'Tambah Program' }}
                    </h5>
                    <button type="button" wire:click="resetAndCloseModal" class="close" data-dismiss="modal"
                        aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="{{ $isEditMode ? 'update' : 'store' }}">
                        <div class="form-group">
                            <label for="kode">Kode Program</label>
                            <input type="text" class="form-control @error('kode') is-invalid @enderror"
                                id="kode" wire:model="kode" placeholder="Contoh: 2.16.01">
                            @error('kode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="nama">Nama Program</label>
                            <textarea class="form-control @error('nama') is-invalid @enderror"
                                id="nama" wire:model="nama" rows="3" placeholder="Masukkan nama program..."></textarea>
                            @error('nama')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="d-flex justify-content-end mt-4">
                            <button type="button" wire:click="resetAndCloseModal"
                                class="btn btn-secondary mr-2">
                                Batal
                            </button>
                            <button type="submit" class="btn btn-primary">
                                {{ $isEditMode ? 'Update' : 'Simpan' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Form Import Excel-->
    <div wire:ignore.self class="modal fade" id="importModalProgram" tabindex="-1" role="dialog"
        aria-labelledby="importModalProgramLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalProgramLabel">
                        <i class="fas fa-file-excel"></i> Import Data Anggaran Excel
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                        wire:click="resetImportState">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Step 1: Upload File -->
                    @if (!$fileDetected)
                        <!-- Tahun Anggaran Selection -->
                        <div class="form-group">
                            <label for="tahun_anggaran_import">Tahun Anggaran <span class="text-danger">*</span></label>
                            <select class="form-control @error('tahun_anggaran_import') is-invalid @enderror"
                                    wire:model="tahun_anggaran_import" id="tahun_anggaran_import">
                                <option value="">-- Pilih Tahun Anggaran --</option>
                                @for ($year = date('Y') + 1; $year >= 2020; $year--)
                                    <option value="{{ $year }}">{{ $year }}</option>
                                @endfor
                            </select>
                            @error('tahun_anggaran_import')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> Pilih tahun anggaran yang akan digunakan untuk validasi data import
                            </small>
                        </div>

                        <div class="custom-file-upload-area">
                            @if (!$fileName)
                                <label for="file" class="file-upload-label">
                                    <div class="upload-icon">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                    </div>
                                    <div class="upload-text">
                                        <span class="upload-title">Pilih file Excel atau drag & drop disini</span>
                                        <span class="upload-subtitle">Format: .xlsx, .xls (Maksimal 10MB)</span>
                                    </div>
                                    <div class="upload-button-area">
                                        <span class="btn-browse">Browse File</span>
                                    </div>
                                </label>
                                <input type="file" wire:model="file" id="file" class="file-input-hidden" accept=".xlsx,.xls">

                                <!-- Loading indicator for file upload -->
                                <div wire:loading wire:target="file" class="file-loading">
                                    <i class="fas fa-spinner fa-spin"></i> Uploading file...
                                </div>
                            @else
                                <div class="selected-file-info">
                                    <i class="fas fa-file-excel text-success"></i>
                                    <span class="file-name">{{ $fileName }} ({{ $fileSize }} MB)</span>
                                    <button type="button" class="btn-remove-file" wire:click="removeFile">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            @endif

                            @error('file')
                                <div class="file-error">
                                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="text-right mt-3">
                            <button type="button" class="btn btn-modern-primary" wire:click="uploadAndDetect"
                                wire:loading.attr="disabled" @if(!$fileName || !$tahun_anggaran_import) disabled @endif>
                                <span wire:loading.remove wire:target="uploadAndDetect">
                                    <i class="fas fa-search"></i> Upload & Detect Format
                                </span>
                                <span wire:loading wire:target="uploadAndDetect">
                                    <i class="fas fa-spinner fa-spin"></i> Memproses...
                                </span>
                            </button>
                        </div>
                    @endif

                    <!-- Step 2: Show Detection Result & Preview -->
                    @if ($fileDetected)
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle"></i> Informasi File</h6>
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td width="150"><strong>Format Terdeteksi:</strong></td>
                                    <td>{{ $formatInfo['format_name'] ?? '' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Jumlah Sheet:</strong></td>
                                    <td>{{ $formatInfo['sheet_count'] ?? '' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        @if ($formatInfo['needs_conversion'] ?? false)
                                            <span class="badge badge-warning">Perlu Konversi</span>
                                        @elseif (($formatInfo['format'] ?? '') == 'template')
                                            <span class="badge badge-success">Siap Import</span>
                                        @else
                                            <span class="badge badge-danger">Format Tidak Dikenal</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                            <hr>
                            <p class="mb-0">{{ $formatInfo['message'] ?? '' }}</p>
                        </div>

                        <!-- Preview Data (jika perlu konversi) -->
                        @if ($showPreview && !empty($previewData))
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="fas fa-eye"></i> Preview Hasil Konversi</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-md-3">
                                            <div class="info-box bg-success">
                                                <span class="info-box-icon"><i class="fas fa-folder"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Programs</span>
                                                    <span
                                                        class="info-box-number">{{ $previewData['programs_count'] ?? 0 }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="info-box bg-info">
                                                <span class="info-box-icon"><i class="fas fa-tasks"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Kegiatans</span>
                                                    <span
                                                        class="info-box-number">{{ $previewData['kegiatans_count'] ?? 0 }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="info-box bg-warning">
                                                <span class="info-box-icon"><i class="fas fa-list"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Sub Kegiatans</span>
                                                    <span
                                                        class="info-box-number">{{ $previewData['sub_kegiatans_count'] ?? 0 }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="info-box bg-danger">
                                                <span class="info-box-icon"><i class="fas fa-money-bill"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Belanjas</span>
                                                    <span
                                                        class="info-box-number">{{ $previewData['belanjas_count'] ?? 0 }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Action Buttons -->
                        <div class="mt-3">
                            <button type="button" class="btn btn-secondary" wire:click="resetImportState">
                                <i class="fas fa-redo"></i> Upload File Lain
                            </button>
                        </div>
                    @endif
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"
                        wire:click="resetImportState">
                        <i class="fas fa-times"></i> Tutup
                    </button>

                    @if ($fileDetected)
                        @if ($formatInfo['needs_conversion'] ?? false)
                            <!-- Opsi untuk file yang perlu konversi -->
                            <button type="button" class="btn btn-info" wire:click="downloadConverted"
                                wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="downloadConverted">
                                    <i class="fas fa-download"></i> Convert & Download
                                </span>
                                <span wire:loading wire:target="downloadConverted">
                                    <i class="fas fa-spinner fa-spin"></i> Processing...
                                </span>
                            </button>
                            <button type="button" class="btn btn-success" wire:click="importConverted"
                                wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="importConverted">
                                    <i class="fas fa-check"></i> Import Langsung
                                </span>
                                <span wire:loading wire:target="importConverted">
                                    <i class="fas fa-spinner fa-spin"></i> Importing...
                                </span>
                            </button>
                        @elseif (($formatInfo['format'] ?? '') == 'template')
                            <!-- Opsi untuk file yang sudah dalam format template -->
                            <button type="button" class="btn btn-success" wire:click="import"
                                wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="import">
                                    <i class="fas fa-check"></i> Import Data
                                </span>
                                <span wire:loading wire:target="import">
                                    <i class="fas fa-spinner fa-spin"></i> Importing...
                                </span>
                            </button>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var myModal = document.getElementById('programModal');
            var kodeInput = document.getElementById('kode');
            var namaInput = document.getElementById('nama');

            myModal.addEventListener('shown.bs.modal', function() {
                if (kodeInput) {
                    kodeInput.focus();
                } else if (namaInput) {
                    namaInput.focus();
                }
            });

            // Initialize tooltips
            $('[data-toggle="tooltip"]').tooltip();

            // Debug: Check if jQuery and Bootstrap loaded
            console.log('jQuery loaded:', typeof $ !== 'undefined');
            console.log('Bootstrap loaded:', typeof $.fn.tab !== 'undefined');
        });

        // Livewire 3 hook to reinitialize tooltips after updates
        document.addEventListener('livewire:init', function () {
            console.log('Livewire initialized successfully');

            Livewire.hook('morph.updated', ({ component, cleanup }) => {
                // Reinitialize tooltips after Livewire updates
                $('[data-toggle="tooltip"]').tooltip();
            });

            // Debug: Listen to all Livewire events
            Livewire.hook('commit', ({ component, commit, respond, succeed, fail }) => {
                console.log('Livewire commit:', component);
            });
        });

        // Tab switching function
        function switchToTab(tabId) {
            console.log('Switching to tab:', tabId);

            // Remove active class from all tabs
            $('.nav-link').removeClass('active');
            $('.tab-pane').removeClass('show active');

            // Add active class to target tab
            $('a[href="#' + tabId + '"]').addClass('active');
            $('#' + tabId).addClass('show active');

            console.log('Tab switched successfully');
        }

        // Listen for custom events from Livewire
        window.addEventListener('switch-tab', event => {
            console.log('Custom switch-tab event received:', event.detail.tab);
            switchToTab(event.detail.tab);
        });
    </script>
@endpush
