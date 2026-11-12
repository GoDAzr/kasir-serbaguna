<?php

namespace App\Http\Controllers;

use App\Support\ModuleHooks;
use App\Support\Tenant;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Mesin CRUD berbasis config/modules.php. Satu controller untuk semua menu data.
 */
class ModuleController extends Controller
{
    private static array $cols = [];

    // ---------- Config ----------
    private function mod(string $key): array
    {
        $m = config("modules.$key");
        abort_unless($m, 404);
        abort_unless(Tenant::can($m['perm']), 403, 'Anda tidak punya izin untuk menu ini.');
        $m['key'] = $key;
        $m['fields'] = $this->norm($m['fields'] ?? []);
        if (isset($m['children'])) {
            $m['children']['fields'] = $this->norm($m['children']['fields']);
        }
        $m['filters'] = $this->norm($m['filters'] ?? []);

        return $m;
    }

    private function norm(array $fields): array
    {
        $out = [];
        foreach ($fields as $a) {
            $f = ['name' => $a[0], 'label' => $a[1], 'type' => $a[2] ?? 'text'];
            foreach ($a as $k => $v) {
                if (! is_int($k)) {
                    $f[$k] = $v;
                }
            }
            $out[] = $f;
        }

        return $out;
    }

    private function hasCol(string $table, string $col): bool
    {
        return in_array($col, self::$cols[$table] ??= Schema::getColumnListing($table), true);
    }

    // ---------- Pilihan (select / ref) ----------
    public function options(array $f): array
    {
        if ($f['type'] === 'select') {
            return $f['opts'];
        }
        $table = $f['ref'];
        $label = $f['label_col'] ?? 'name';
        $q = DB::table($table);
        if ($this->hasCol($table, 'business_id')) {
            $q->where('business_id', Tenant::id());
        }
        if ($this->hasCol($table, 'deleted_at')) {
            $q->whereNull('deleted_at');
        }
        if ($table === 'branches') {
            return Tenant::branches();
        }
        foreach ($f['where'] ?? [] as $c => $v) {
            is_array($v) ? $q->whereIn($c, $v) : $q->where($c, $v);
        }
        foreach ($f['where_not'] ?? [] as $c => $v) {
            is_array($v) ? $q->whereNotIn($c, $v) : $q->where($c, '!=', $v);
        }
        if ($this->hasCol($table, 'is_active') && empty($f['all'])) {
            $q->where('is_active', 1);
        }

        return $q->orderBy($label)->limit(1000)->pluck($label, $f['key'] ?? 'id')->all();
    }

    private function withOptions(array $fields): array
    {
        foreach ($fields as &$f) {
            if (in_array($f['type'], ['select', 'ref'], true)) {
                $f['options'] = $this->options($f);
            }
        }

        return $fields;
    }

    // ---------- Daftar ----------
    public function index(Request $request, string $key)
    {
        $m = $this->mod($key);
        $table = $m['table'];

        $q = DB::table($table);
        if ($this->hasCol($table, 'business_id')) {
            $q->where("$table.business_id", Tenant::id());
        }
        if ($this->hasCol($table, 'deleted_at')) {
            $q->whereNull("$table.deleted_at");
        }
        foreach ($m['where'] ?? [] as $c => $v) {
            is_array($v) ? $q->whereIn("$table.$c", $v) : $q->where("$table.$c", $v);
        }
        foreach ($m['where_not'] ?? [] as $c => $v) {
            is_array($v) ? $q->whereNotIn("$table.$c", $v) : $q->where("$table.$c", '!=', $v);
        }
        if (! empty($m['branch']) && ($ids = Tenant::branchIds()) !== null) {
            $q->whereIn("$table.{$m['branch']}", $ids ?: [0]);
        }
        if ($term = trim((string) $request->query('q'))) {
            $q->where(function ($w) use ($m, $table, $term) {
                foreach ($m['search'] ?? ['name'] as $c) {
                    $w->orWhere("$table.$c", 'like', "%$term%");
                }
            });
        }
        foreach ($m['filters'] as $f) {
            $v = $request->query($f['name']);
            if ($v === null || $v === '') {
                continue;
            }
            if ($f['type'] === 'date') {
                $q->whereDate("$table.{$f['name']}", ($f['op'] ?? '='), $v);
            } else {
                $q->where("$table.{$f['name']}", $v);
            }
        }
        $q->select("$table.*")->orderByDesc("$table.id");
        $rows = $q->paginate(20)->withQueryString();

        $maps = $this->lookupMaps($m['cols'], $rows->items());
        $filters = $this->withOptions($m['filters']);

        return view('modules.index', compact('m', 'rows', 'maps', 'filters'));
    }

    /** Ambil label untuk kolom yang berisi id (cabang, produk, dst). */
    private function lookupMaps(array $cols, array $rows): array
    {
        $maps = [];
        foreach ($cols as $c) {
            if (($c[2] ?? '') !== 'rel') {
                continue;
            }
            [$tbl, $lbl] = explode('.', $c[3]);
            $ids = collect($rows)->pluck($c[0])->filter()->unique()->all();
            $maps[$c[0]] = $ids ? DB::table($tbl)->whereIn('id', $ids)->pluck($lbl, 'id')->all() : [];
        }

        return $maps;
    }

    // ---------- Tambah / ubah ----------
    public function create(string $key)
    {
        $m = $this->mod($key);
        abort_if(($m['create'] ?? true) === false, 404);

        return $this->form($m, null);
    }

    public function edit(string $key, int $id)
    {
        $m = $this->mod($key);
        abort_if(($m['edit'] ?? true) === false, 404);
        $row = $this->find($m, $id);

        return $this->form($m, $row);
    }

    private function form(array $m, ?object $row)
    {
        $fields = $this->withOptions($m['fields']);
        $children = null;
        $childRows = [];
        if (isset($m['children'])) {
            $children = $m['children'];
            $children['fields'] = $this->withOptions($children['fields']);
            if ($row) {
                $childRows = DB::table($children['table'])->where($children['fk'], $row->id)->get()->map(fn ($r) => (array) $r)->all();
            }
        }
        $extra = ModuleHooks::call("form_{$m['key']}", $row) ?? [];

        return view('modules.form', compact('m', 'row', 'fields', 'children', 'childRows', 'extra'));
    }

    private function find(array $m, int $id): object
    {
        $q = DB::table($m['table'])->where('id', $id);
        if ($this->hasCol($m['table'], 'business_id')) {
            $q->where('business_id', Tenant::id());
        }
        if ($this->hasCol($m['table'], 'deleted_at')) {
            $q->whereNull('deleted_at');
        }
        if (! empty($m['branch']) && ($ids = Tenant::branchIds()) !== null) {
            $q->whereIn($m['branch'], $ids ?: [0]);
        }

        return $q->first() ?? abort(404);
    }

    public function show(string $key, int $id)
    {
        $m = $this->mod($key);
        $row = $this->find($m, $id);
        $fields = $this->withOptions($m['fields']);
        $childRows = [];
        $children = $m['children'] ?? null;
        if ($children) {
            $children['fields'] = $this->withOptions($children['fields']);
            $childRows = DB::table($children['table'])->where($children['fk'], $id)->get()->map(fn ($r) => (array) $r)->all();
        }

        return view('modules.show', compact('m', 'row', 'fields', 'children', 'childRows'));
    }

    // ---------- Simpan ----------
    private function rules(array $fields, Request $r, array $m, ?object $row, string $prefix = '', ?array $scope = null): array
    {
        $rules = [];
        $scope ??= $r->all();
        foreach ($fields as $f) {
            if (! $this->visible($f, $scope)) {
                continue;
            }
            $name = $prefix . $f['name'];
            $rule = [];
            $required = ! empty($f['r']) || $this->requiredIf($f, $scope) || (! empty($f['rc']) && ! $row);
            $rule[] = $required ? ($f['type'] === 'check' ? 'sometimes' : 'required') : 'nullable';

            switch ($f['type']) {
                case 'number':
                    $rule[] = 'numeric';
                    $rule[] = isset($f['gt']) ? 'gt:' . $f['gt'] : 'min:' . ($f['min'] ?? 0);
                    break;
                case 'money':
                    $rule[] = 'numeric';
                    $rule[] = isset($f['gt']) ? 'gt:' . $f['gt'] : 'min:0';
                    $rule[] = 'max:999999999999';
                    break;
                case 'date':
                    $rule[] = 'date';
                    break;
                case 'email':
                    $rule[] = 'email';
                    $rule[] = 'max:255';
                    break;
                case 'file':
                    $rule = [$required && ! ($row && ! empty($row->{$f['name']})) ? 'required' : 'nullable', 'file', 'max:4096', 'mimes:jpg,jpeg,png,webp,pdf'];
                    break;
                case 'select':
                    $rule[] = Rule::in(array_keys($f['opts']));
                    break;
                case 'ref':
                    if (empty($f['key'])) {
                        $rule[] = 'integer';
                    }
                    $rule[] = Rule::in(array_keys($this->options($f)));
                    break;
                case 'password':
                    $rule[] = 'string';
                    $rule[] = 'min:' . ($f['minlen'] ?? 8);
                    break;
                case 'check':
                    $rule[] = 'boolean';
                    break;
                case 'perms':
                    $rule = [$required ? 'required' : 'nullable', 'array', 'min:1'];
                    break;
                case 'multi':
                    $rule = [$required ? 'required' : 'nullable', 'array'];
                    break;
                case 'datetime':
                    $rule[] = 'date';
                    break;
                default:
                    $rule[] = 'string';
                    $rule[] = 'max:' . ($f['type'] === 'textarea' ? 2000 : 255);
            }
            if (! empty($f['u'])) {
                $u = Rule::unique($m['table'], $f['name']);
                if (! empty($f['global']) === false && $this->hasCol($m['table'], 'business_id')) {
                    $u = $u->where('business_id', Tenant::id());
                }
                foreach ($f['u_where'] ?? [] as $c => $v) {
                    $u = $u->where($c, $v);
                }
                if ($row) {
                    $u = $u->ignore($row->id);
                }
                $rule[] = $u;
            }
            foreach ($f['x'] ?? [] as $x) {
                $rule[] = $x;
            }
            $rules[$name] = $rule;
        }

        return $rules;
    }

    private function visible(array $f, array $scope): bool
    {
        foreach ($f['show'] ?? [] as $k => $v) {
            $cur = $scope[$k] ?? null;
            if (! in_array((string) $cur, array_map('strval', (array) $v), true)) {
                return false;
            }
        }

        return true;
    }

    private function requiredIf(array $f, array $scope): bool
    {
        if (empty($f['req_if'])) {
            return false;
        }
        foreach ($f['req_if'] as $k => $v) {
            if (! in_array((string) ($scope[$k] ?? ''), array_map('strval', (array) $v), true)) {
                return false;
            }
        }

        return true;
    }

    private function collect(array $fields, array $validated, Request $r, ?object $row, string $prefix = ''): array
    {
        $d = [];
        foreach ($fields as $f) {
            $n = $f['name'];
            $scope = $r->all();
            if (! $this->visible($f, $scope)) {
                continue;
            }
            switch ($f['type']) {
                case 'file':
                    if ($r->hasFile($prefix . $n)) {
                        $d[$n] = $r->file($prefix . $n)->store('uploads/' . Tenant::id(), 'public');
                        if ($row && ! empty($row->$n)) {
                            Storage::disk('public')->delete($row->$n);
                        }
                    }
                    break;
                case 'check':
                    $d[$n] = $r->boolean($prefix . $n) ? 1 : 0;
                    break;
                case 'perms':
                case 'multi':
                    $d[$n] = json_encode(array_values($r->input($prefix . $n, [])));
                    break;
                case 'password':
                    if (($v = $r->input($prefix . $n)) !== null && $v !== '') {
                        $d[$n] = $v;
                    }
                    break;
                default:
                    $v = $validated[$prefix . $n] ?? null;
                    $d[$n] = ($v === '' ? null : $v);
            }
        }

        return $d;
    }

    public function store(Request $request, string $key)
    {
        $m = $this->mod($key);
        abort_if(($m['create'] ?? true) === false, 404);

        return $this->save($request, $m, null);
    }

    public function update(Request $request, string $key, int $id)
    {
        $m = $this->mod($key);
        abort_if(($m['edit'] ?? true) === false, 404);

        return $this->save($request, $m, $this->find($m, $id));
    }

    private function save(Request $request, array $m, ?object $row)
    {
        $rules = $this->rules($m['fields'], $request, $m, $row);
        $messages = ['required' => ':attribute wajib diisi.', 'unique' => ':attribute sudah dipakai.', 'gt' => ':attribute harus lebih dari :value.',
            'min' => ':attribute tidak valid (minimal :min).', 'numeric' => ':attribute harus angka.', 'in' => ':attribute tidak valid.',
            'date' => ':attribute bukan tanggal yang benar.', 'email' => 'Format :attribute tidak valid.', 'confirmed' => 'Konfirmasi :attribute tidak sama.'];
        $attrs = [];
        foreach ($m['fields'] as $f) {
            $attrs[$f['name']] = $f['label'];
        }

        // Rincian (repeater)
        $childData = [];
        if (isset($m['children'])) {
            $c = $m['children'];
            $rowsIn = array_values(array_filter((array) $request->input($c['name'], []), fn ($r) => is_array($r) && array_filter($r, fn ($v) => $v !== null && $v !== '')));
            if (! empty($c['min']) && count($rowsIn) < $c['min']) {
                throw ValidationException::withMessages([$c['name'] => ($c['label'] ?? 'Rincian') . ' wajib diisi minimal ' . $c['min'] . ' baris.']);
            }
            foreach ($rowsIn as $i => $r) {
                $rr = $this->rules($c['fields'], $request, $m, null, '', $r);
                $v = validator($r, $rr, $messages, array_column($c['fields'], 'label', 'name'));
                if ($v->fails()) {
                    throw ValidationException::withMessages([$c['name'] => 'Baris ' . ($i + 1) . ': ' . $v->errors()->first()]);
                }
                $childData[] = $v->validated();
            }
        }

        $validated = $request->validate($rules, $messages, $attrs);
        $data = $this->collect($m['fields'], $validated, $request, $row);

        $data = ModuleHooks::call("prepare_{$m['key']}", $data, $row, $request, $childData) ?? $data;

        foreach ($m['fixed'] ?? [] as $c => $v) {
            $data[$c] = $v;
        }
        // Password / PIN selalu di-hash
        foreach ($m['fields'] as $f) {
            if ($f['type'] === 'password' && ! empty($data[$f['name']])) {
                $data[$f['name']] = \Illuminate\Support\Facades\Hash::make($data[$f['name']]);
            }
            if ($f['type'] === 'password' && empty($data[$f['name']])) {
                unset($data[$f['name']]);
            }
        }
        $data = array_diff_key($data, array_flip($m['virtual'] ?? []));
        // Kolom kosong: saat tambah dibiarkan memakai nilai bawaan database; angka kosong saat ubah tidak ditimpa
        $numeric = array_column(array_filter($m['fields'], fn ($f) => in_array($f['type'], ['money', 'number'], true)), 'name');
        foreach ($data as $c => $v) {
            if ($v === null && (! $row || in_array($c, $numeric, true))) {
                unset($data[$c]);
            }
        }

        $table = $m['table'];
        $id = DB::transaction(function () use ($m, $row, $data, $childData, $request, $table) {
            $payload = $data;
            if ($this->hasCol($table, 'business_id') && ! $row) {
                $payload['business_id'] = Tenant::id();
            }
            if ($this->hasCol($table, 'user_id') && ! $row && ! isset($payload['user_id'])) {
                $payload['user_id'] = Tenant::user()->id;
            }
            if ($this->hasCol($table, 'updated_at')) {
                $payload['updated_at'] = now();
            }
            if (ModuleHooks::has("store_{$m['key']}")) {
                return ModuleHooks::call("store_{$m['key']}", $data, $childData, $request);
            }
            if ($row) {
                DB::table($table)->where('id', $row->id)->update($payload);
                $id = $row->id;
            } else {
                if ($this->hasCol($table, 'created_at')) {
                    $payload['created_at'] = now();
                }
                $id = DB::table($table)->insertGetId($payload);
            }

            if (isset($m['children'])) {
                $c = $m['children'];
                DB::table($c['table'])->where($c['fk'], $id)->delete();
                foreach ($childData as $r) {
                    $r[$c['fk']] = $id;
                    foreach (['business_id' => Tenant::id(), 'created_at' => now(), 'updated_at' => now()] as $col => $val) {
                        if ($this->hasCol($c['table'], $col)) {
                            $r[$col] = $val;
                        }
                    }
                    $r = ModuleHooks::call("child_{$m['key']}", $r) ?? $r;
                    DB::table($c['table'])->insert($r);
                }
            }
            ModuleHooks::call("after_{$m['key']}", $id, $data, $childData, $request, $row);

            return $id;
        });

        Tenant::audit($row ? 'ubah' : 'tambah', $m['title'], ['id' => $id] + array_diff_key($data, array_flip(['password', 'pin'])));

        $go = $m['after'] ?? null;

        return redirect($go ? url($go) : route('modules.index', $m['key']))
            ->with('ok', $m['title'] . ($row ? ' berhasil diperbarui.' : ' berhasil disimpan.'));
    }

    // ---------- Hapus ----------
    public function destroy(string $key, int $id)
    {
        $m = $this->mod($key);
        abort_if(($m['delete'] ?? true) === false, 404);
        $row = $this->find($m, $id);

        try {
            DB::transaction(function () use ($m, $row) {
                ModuleHooks::call("delete_{$m['key']}", $row);
                if ($this->hasCol($m['table'], 'deleted_at')) {
                    DB::table($m['table'])->where('id', $row->id)->update(['deleted_at' => now()]);
                } else {
                    DB::table($m['table'])->where('id', $row->id)->delete();
                }
            });
        } catch (QueryException $e) {
            return back()->with('err', 'Data ini sudah dipakai di tempat lain sehingga tidak bisa dihapus. Ubah statusnya jadi nonaktif saja.');
        }

        Tenant::audit('hapus', $m['title'], ['id' => $id]);

        return back()->with('ok', 'Data dihapus.');
    }
}
