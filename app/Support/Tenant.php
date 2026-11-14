<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Pembatas data per usaha (business_id), cabang, role, dan izin.
 */
class Tenant
{
    private static array $roleCache = [];

    public static function user(): ?User
    {
        return Auth::user();
    }

    public static function id(): ?int
    {
        return Auth::user()?->business_id;
    }

    public static function isAdmin(?User $u = null): bool
    {
        $u ??= self::user();

        return $u && in_array($u->role, ['owner', 'admin'], true);
    }

    /** True kalau pengguna ini memakai tampilan kasir (role-nya ditandai "tampilan kasir" oleh owner). */
    public static function cashierUi(?User $u = null): bool
    {
        $u ??= self::user();
        if (! $u || self::isAdmin($u)) {
            return false;
        }
        $role = self::role($u);

        return (bool) ($role->cashier_ui ?? false);
    }

    public static function role(?User $u = null): ?object
    {
        $u ??= self::user();
        if (! $u) {
            return null;
        }
        $key = $u->business_id . ':' . $u->role;

        return self::$roleCache[$key] ??= DB::table('roles')
            ->where('business_id', $u->business_id)->where('slug', $u->role)->first();
    }

    public static function can(string $perm, ?User $u = null): bool
    {
        $u ??= self::user();
        if (! $u) {
            return false;
        }
        if (self::isAdmin($u)) {
            return true;
        }
        $role = self::role($u);
        if (! $role || ! $role->is_active) {
            return false;
        }
        $perms = json_decode($role->permissions ?? '[]', true) ?: [];

        return in_array('*', $perms, true) || in_array($perm, $perms, true);
    }

    /** Daftar id cabang yang boleh diakses user; null = semua cabang. */
    public static function branchIds(?User $u = null): ?array
    {
        $u ??= self::user();
        if (! $u || self::isAdmin($u)) {
            return null;
        }
        $role = self::role($u);
        if ($role && $role->all_branches) {
            return null;
        }

        return DB::table('branch_user')->where('user_id', $u->id)->pluck('branch_id')->all();
    }

    /** Cabang yang boleh dipilih user (id => nama). */
    public static function branches(): array
    {
        $q = DB::table('branches')->where('business_id', self::id())->where('is_active', 1)->orderBy('id');
        if (($ids = self::branchIds()) !== null) {
            $q->whereIn('id', $ids ?: [0]);
        }

        return $q->pluck('name', 'id')->all();
    }

    public static function audit(string $action, string $menu, $changes = null): void
    {
        $u = self::user();
        DB::table('audit_logs')->insert([
            'business_id' => $u?->business_id,
            'user_id' => $u?->id,
            'action' => $action,
            'menu' => $menu,
            'changes' => is_string($changes) ? $changes : json_encode($changes, JSON_UNESCAPED_UNICODE),
            'ip' => request()?->ip(),
            'created_at' => now(),
        ]);
    }

    public static function rupiah($n): string
    {
        return 'Rp ' . number_format((float) $n, 0, ',', '.');
    }
}
