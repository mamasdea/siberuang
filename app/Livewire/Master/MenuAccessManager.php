<?php

namespace App\Livewire\Master;

use App\Models\MenuPermission;
use Livewire\Component;
use Livewire\Attributes\Title;

#[Title('Pengaturan Akses Menu')]
class MenuAccessManager extends Component
{
    public $permissions = [];
    public $roles = ['admin', 'user'];

    public function mount()
    {
        $this->loadPermissions();
    }

    public function loadPermissions()
    {
        $this->permissions = [];
        foreach ($this->roles as $role) {
            $activeMenus = MenuPermission::where('role', $role)->pluck('menu_key')->toArray();
            foreach (MenuPermission::allMenus() as $key => $label) {
                $this->permissions[$role][$key] = in_array($key, $activeMenus);
            }
        }
    }

    public function togglePermission($role, $menuKey)
    {
        // Admin tidak boleh menonaktifkan menu "master" untuk admin (anti lock-out)
        if ($role === 'admin' && $menuKey === 'master') {
            $this->js(<<<'JS'
                const Toast = Swal.mixin({ toast: true, position: "top-end", showConfirmButton: false, timer: 2000, timerProgressBar: true });
                Toast.fire({ icon: "warning", title: "Menu Master tidak bisa dinonaktifkan untuk Admin" });
            JS);
            return;
        }

        $exists = MenuPermission::where('role', $role)->where('menu_key', $menuKey)->first();

        if ($exists) {
            $exists->delete();
            $this->permissions[$role][$menuKey] = false;
        } else {
            MenuPermission::create(['role' => $role, 'menu_key' => $menuKey]);
            $this->permissions[$role][$menuKey] = true;
        }

        MenuPermission::clearCache();

        $this->js(<<<'JS'
            const Toast = Swal.mixin({ toast: true, position: "top-end", showConfirmButton: false, timer: 1000, timerProgressBar: true });
            Toast.fire({ icon: "success", title: "Akses diperbarui" });
        JS);
    }

    public function render()
    {
        return view('livewire.master.menu-access-manager', [
            'menus' => MenuPermission::allMenus(),
        ]);
    }
}
