@push('css')
    <x-styles.modern-ui />
@endpush

<div>
    <div class="modern-card fade-in-up">
        <div class="card-header-modern">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                     <h3 class="page-title">User Management</h3>
                     <p class="page-subtitle mb-0">Manajemen akses pengguna aplikasi</p>
                </div>
                <button class="btn btn-modern-add" wire:click="showCreateForm">
                    <i class="fas fa-plus mr-1"></i> Add User
                </button>
            </div>
        </div>
        
        <div class="content-card">
            @if (session()->has('message'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('message') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

             <div class="table-responsive">
                 <table class="table modern-table">
                     <thead>
                         <tr>
                             <th width="50">ID</th>
                             <th>Name</th>
                             <th>Email</th>
                             <th>Role</th>
                             <th width="150" class="text-center">Actions</th>
                         </tr>
                     </thead>
                     <tbody>
                        @foreach ($users as $user)
                             <tr>
                                 <td>{{ $user->id }}</td>
                                 <td class="font-weight-bold">{{ $user->name }}</td>
                                 <td>{{ $user->email }}</td>
                                 <td>
                                     <span class="badge {{ $user->role == 'admin' ? 'badge-danger' : 'badge-primary' }} px-2 py-1">
                                        {{ ucfirst($user->role) }}
                                     </span>
                                 </td>
                                 <td class="text-center">
                                     <button class="btn btn-action-edit" wire:click="showEditForm({{ $user->id }})" title="Edit">
                                         <i class="fas fa-pencil-alt"></i>
                                     </button>
                                     <button class="btn btn-action-delete" wire:click="deleteUser({{ $user->id }})"
                                             onclick="confirm('Are you sure?') || event.stopImmediatePropagation()" title="Delete">
                                         <i class="fas fa-trash-alt"></i>
                                     </button>
                                 </td>
                             </tr>
                        @endforeach
                     </tbody>
                 </table>
             </div>
             
             <div class="mt-4">
                 {{ $users->links('livewire::bootstrap') }}
             </div>
        </div>
    </div>

    @if ($modalFormVisible)
        <div class="modal d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title font-weight-bold">{{ $isEdit ? 'Edit User' : 'Create User' }}</h5>
                        <button type="button" class="close" wire:click="$set('modalFormVisible', false)">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="saveUser">
                            <div class="form-group">
                                <label class="font-weight-bold small text-secondary">Name</label>
                                <input type="text" class="form-control" wire:model="name">
                                @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold small text-secondary">Email</label>
                                <input type="email" class="form-control" wire:model="email">
                                @error('email') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold small text-secondary">Role</label>
                                <select class="form-control custom-select-modern" wire:model="role">
                                    <option value="admin">Admin</option>
                                    <option value="user">User</option>
                                </select>
                                @error('role') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold small text-secondary">Password</label>
                                <input type="password" class="form-control" wire:model="password">
                                @error('password') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="d-flex justify-content-end mt-4">
                                <button type="button" class="btn btn-secondary mr-2" wire:click="$set('modalFormVisible', false)">Cancel</button>
                                <button type="submit" class="btn btn-modern-add">{{ $isEdit ? 'Update' : 'Save' }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
