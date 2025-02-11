<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">User Management</h3>
            <button class="btn btn-primary float-right" wire:click="showCreateForm">Add User</button>
        </div>
        <div class="card-body">
            @if (session()->has('message'))
                <div class="alert alert-success">{{ session('message') }}</div>
            @endif

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td><span
                                    class="badge {{ $user->role == 'admin' ? 'badge-danger' : 'badge-primary' }}">{{ ucfirst($user->role) }}</span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-warning"
                                    wire:click="showEditForm({{ $user->id }})">Edit</button>
                                <button class="btn btn-sm btn-danger" wire:click="deleteUser({{ $user->id }})"
                                    onclick="confirm('Are you sure?') || event.stopImmediatePropagation()">Delete</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{ $users->links() }}
        </div>
    </div>

    @if ($modalFormVisible)
        <div wire:ignore.self class="modal d-block" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $isEdit ? 'Edit User' : 'Create User' }}</h5>
                        <button type="button" class="close"
                            wire:click="$set('modalFormVisible', false)">&times;</button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="saveUser">
                            <div class="form-group">
                                <label>Name</label>
                                <input type="text" class="form-control" wire:model="name">
                                @error('name')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" class="form-control" wire:model="email">
                                @error('email')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label>Role</label>
                                <select class="form-control" wire:model="role">
                                    <option value="admin">Admin</option>
                                    <option value="user">User</option>
                                </select>
                                @error('role')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label>Password</label>
                                <input type="password" class="form-control" wire:model="password">
                                @error('password')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Update' : 'Save' }}</button>
                            <button type="button" class="btn btn-secondary"
                                wire:click="$set('modalFormVisible', false)">Cancel</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
