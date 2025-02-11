<?php

namespace App\Livewire\Master;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Hash;

#[Title('Master')]
class UserManagement extends Component
{
    use WithPagination;

    public $name, $email, $password, $role, $userId;
    public $modalFormVisible = false;
    public $isEdit = false;

    public function render()
    {
        return view('livewire.master.user-management', [
            'users' => User::paginate(10),
        ]);
    }

    public function showCreateForm()
    {
        $this->resetInputFields();
        $this->isEdit = false;
        $this->modalFormVisible = true;
    }

    public function showEditForm($id)
    {
        $user = User::findOrFail($id);
        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role;
        $this->isEdit = true;
        $this->modalFormVisible = true;
    }

    public function saveUser()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $this->userId,
            'role' => 'required|in:admin,user',
            'password' => $this->isEdit ? 'nullable|min:6' : 'required|min:6',
        ]);

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
        ];

        // Jangan gunakan Hash::make() karena Laravel sudah otomatis hash
        if (!$this->isEdit || !empty($this->password)) {
            $data['password'] = $this->password; // Laravel akan otomatis hash
        }

        User::updateOrCreate(['id' => $this->userId], $data);

        $this->resetInputFields();
        $this->modalFormVisible = false;
        session()->flash('message', $this->isEdit ? 'User updated successfully' : 'User created successfully');
    }

    public function deleteUser($id)
    {
        User::findOrFail($id)->delete();
        session()->flash('message', 'User deleted successfully');
    }

    private function resetInputFields()
    {
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->role = 'user';
        $this->userId = null;
    }
}
