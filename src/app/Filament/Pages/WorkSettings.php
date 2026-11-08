<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class WorkSettings extends Page
{
    protected string $view = 'filament.pages.work-settings';
    protected static ?string $navigationLabel = 'Setting Pekerjaan';
    protected static ?string $title = 'Setting Pekerjaan';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-briefcase';
    protected static \UnitEnum|string|null $navigationGroup = 'Pengaturan';
    protected static ?int $navigationSort = 2;

    public string $activeTab = 'work';
    public bool $selfAssignMode = false;
    public int  $selfAssignMaxTugas = 3;
    public bool $autoSyncStok = true;
    public bool $modulPenerimaan = false;

    /** @var array<string, bool> */
    public array $assignableRoles = [];

    /** @var array<string, array<string, bool>> */
    public array $adminAccess = [];

    /** @var array<string, string> */
    public array $loginAccess = [];

    /** @var array<string, array<string, bool>> */
    public array $userModuleAccess = [];

    /** @var array<string, bool> — toggle aktif/nonaktif per modul user */
    public array $userModuleEnabled = [];

    protected array $lockedRoles = ['super_admin', 'admin'];
    protected array $receiverRoles = ['magang', 'siswa', 'education', 'guest', 'member'];

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin']) ?? false;
    }

    public static function getUserModules(): array
    {
        return [
            'cetak_barcode' => 'Cetak Barcode',
            'scan_barcode'  => 'Scan Barcode',
            'distribusi'    => 'Distribusi',
        ];
    }

    public static function getAdminMenus(): array
    {
        return [
            'dashboard'           => 'Dashboard',
            // Absensi
            'attendances'         => 'Absensi',
            'attendance_recap'    => 'Rekap Absensi',
            'attendance_settings' => 'Setting Absensi',
            // Inventory
            'products'            => 'Produk',
            'warehouses'          => 'Gudang',
            'cetak_barcode'       => 'Cetak Barcode',
            'scan_barcode'        => 'Scan Barcode',
            // Master Data
            'garment_categories'  => 'Kategori Pakaian',
            'garment_types'       => 'Jenis Pakaian',
            'product_sizes'       => 'Ukuran Produk',
            'size_guides'         => 'Panduan Ukuran',
            'work_types'          => 'Jenis Pekerjaan',
            // Manajemen Kerja
            'work_assignments'    => 'Tugas Pekerjaan',
            // Pengaturan
            'users'               => 'Pengguna',
            'work_settings'       => 'Setting Pekerjaan',
        ];
    }

    public function getConfigurableRoles(): array
    {
        return Role::whereNotIn('name', array_merge($this->lockedRoles, $this->receiverRoles))
            ->orderBy('name')
            ->pluck('name')
            ->toArray();
    }

    public function getAllUserRoles(): array
    {
        return Role::whereNotIn('name', $this->lockedRoles)
            ->orderBy('name')
            ->pluck('name')
            ->toArray();
    }

    public function getRoleLabels(): array
    {
        return [
            'designer'    => 'Designer',
            'keuangan'    => 'Keuangan',
            'marketing'   => 'Marketing',
            'team_leader' => 'Team Leader',
            'tailor'      => 'Tailor',
            'support'     => 'Support',
        ];
    }

    public function mount(): void
    {
        // Tab 1: Work assign
        $permission = Permission::findByName('work.assign');
        $currentRoles = $permission ? $permission->roles()->pluck('name')->toArray() : [];
        foreach ($this->getConfigurableRoles() as $role) {
            $this->assignableRoles[$role] = in_array($role, $currentRoles);
        }

        // Tab 2: Admin menu access
        $menus = array_keys(self::getAdminMenus());
        foreach ($this->getConfigurableRoles() as $role) {
            foreach ($menus as $menu) {
                $this->adminAccess[$role][$menu] = (bool) Setting::get("role_admin_menu.{$role}.{$menu}", false);
            }
        }

        // Tab 3: Login access
        foreach (Role::whereNotIn('name', $this->lockedRoles)->pluck('name') as $role) {
            $this->loginAccess[$role] = Setting::get("role_login_access.{$role}", 'user');
        }

        // Tab 4: User module access
        $modules = array_keys(self::getUserModules());
        foreach ($modules as $module) {
            $this->userModuleEnabled[$module] = Setting::get("user_module_enabled.{$module}", '0') === '1';
        }
        foreach ($this->getAllUserRoles() as $role) {
            foreach ($modules as $module) {
                $this->userModuleAccess[$role][$module] = (bool) Setting::get("role_user_module.{$role}.{$module}", false);
            }
        }

        // Mode Mandiri
        $this->selfAssignMode      = Setting::get('self_assign_mode', '0') === '1';
        $this->selfAssignMaxTugas  = (int) Setting::get('self_assign_max_tugas', '3');
        // Auto sync stok
        $this->autoSyncStok = Setting::get('auto_sync_stok', '1') === '1';
        // Modul Penerimaan Distribusi
        $this->modulPenerimaan = Setting::get('modul_penerimaan', '0') === '1';
    }

    public function saveWork(): void
    {
        $permission = Permission::findByName('work.assign');
        if (!$permission) {
            Notification::make()->title('Permission work.assign tidak ditemukan')->danger()->send();
            return;
        }

        $alwaysAssigned = ['super_admin', 'admin'];
        $toAssign = $alwaysAssigned;
        foreach ($this->assignableRoles as $roleName => $checked) {
            if ($checked) $toAssign[] = $roleName;
        }

        foreach ($this->getConfigurableRoles() as $roleName) {
            $role = Role::findByName($roleName);
            if (in_array($roleName, $toAssign)) {
                $role->givePermissionTo($permission);
            } else {
                $role->revokePermissionTo($permission);
            }
        }

        Role::findByName('super_admin')->givePermissionTo($permission);
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Notification::make()->title('Setting pekerjaan berhasil disimpan')->success()->send();
    }

    public function saveAdminAccess(): void
    {
        foreach ($this->adminAccess as $role => $menus) {
            foreach ($menus as $menu => $value) {
                Setting::set("role_admin_menu.{$role}.{$menu}", $value ? '1' : '0');
            }
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        Notification::make()->title('Setting akses admin berhasil disimpan')->success()->send();
    }

    public function saveLoginAccess(): void
    {
        $allowed = Role::whereNotIn('name', $this->lockedRoles)->pluck('name')->flip()->toArray();
        foreach ($this->loginAccess as $role => $value) {
            if (!array_key_exists($role, $allowed)) continue;
            Setting::set("role_login_access.{$role}", $value);
        }

        Notification::make()->title('Setting akses login berhasil disimpan')->success()->send();
    }

    public function saveSelfAssignMode(): void
    {
        Setting::set('self_assign_mode', $this->selfAssignMode ? '1' : '0');
        Setting::set('self_assign_max_tugas', (string) max(1, (int) $this->selfAssignMaxTugas));
        $status = $this->selfAssignMode ? 'diaktifkan' : 'dinonaktifkan';
        Notification::make()->title("Mode Mandiri berhasil {$status}")->success()->send();
    }

    public static function getSelfAssignMaxTugas(): int
    {
        return (int) Setting::get('self_assign_max_tugas', '3');
    }

    public function saveAutoSyncStok(): void
    {
        Setting::set('auto_sync_stok', $this->autoSyncStok ? '1' : '0');
        $status = $this->autoSyncStok ? 'diaktifkan' : 'dinonaktifkan';
        Notification::make()->title("Auto Sync Stok berhasil {$status}")->success()->send();
    }

    public function saveUserModuleAccess(): void
    {
        foreach ($this->userModuleEnabled as $module => $enabled) {
            Setting::set("user_module_enabled.{$module}", $enabled ? '1' : '0');
        }
        foreach ($this->userModuleAccess as $role => $modules) {
            foreach ($modules as $module => $value) {
                Setting::set("role_user_module.{$role}.{$module}", $value ? '1' : '0');
            }
        }

        Notification::make()->title('Setting akses modul user berhasil disimpan')->success()->send();
    }

    public function saveModulPenerimaan(): void
    {
        Setting::set('modul_penerimaan', $this->modulPenerimaan ? '1' : '0');
        $status = $this->modulPenerimaan ? 'diaktifkan' : 'dinonaktifkan';
        Notification::make()->title("Modul Penerimaan Distribusi berhasil {$status}")->success()->send();
    }

    public static function isAutoSyncStokEnabled(): bool
    {
        return Setting::get('auto_sync_stok', '1') === '1';
    }

    public static function isModulPenerimaanEnabled(): bool
    {
        return Setting::get('modul_penerimaan', '0') === '1';
    }

    public function getEditableRoles(): array
    {
        return array_keys($this->assignableRoles);
    }

    public static function isUserModuleEnabled(string $module): bool
    {
        return Setting::get("user_module_enabled.{$module}", '0') === '1';
    }

    public static function canAccessUserModule(string $role, string $module): bool
    {
        if (in_array($role, ['super_admin', 'admin'])) return true;
        return Setting::get("role_user_module.{$role}.{$module}", '0') === '1';
    }

    public static function canAccessMenu(string $role, string $menu): bool
    {
        if (in_array($role, ['super_admin', 'admin'])) return true;
        return (bool) Setting::get("role_admin_menu.{$role}.{$menu}", false);
    }

    public static function canLoginAs(string $role, string $type): bool
    {
        if (in_array($role, ['super_admin', 'admin'])) return true;
        $access = Setting::get("role_login_access.{$role}", 'user');
        return $access === 'both' || $access === $type;
    }
}
