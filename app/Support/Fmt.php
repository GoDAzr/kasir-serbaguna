<?php

namespace App\Support;

use Carbon\Carbon;

/** Format tampilan sel tabel. */
class Fmt
{
    public const BADGE = [
        'lunas' => 'ok', 'aktif' => 'ok', 'selesai' => 'ok', 'berhasil' => 'ok', 'paid' => 'ok', 'tambah' => 'ok', 'in' => 'ok', 'earn' => 'ok',
        'sebagian' => 'warn', 'belum_lunas' => 'bad', 'nonaktif' => 'bad', 'gagal' => 'bad', 'void' => 'bad', 'hapus' => 'bad', 'batal' => 'bad',
        'peringatan' => 'warn', 'ubah' => 'warn', 'retur' => 'warn', 'keluar' => 'gray',
    ];

    public static function label(?string $v): string
    {
        $map = ['cash_in' => 'Kas masuk', 'cash_out' => 'Kas keluar', 'capital' => 'Modal', 'income' => 'Pemasukan', 'expense' => 'Pengeluaran',
            'sale' => 'Penjualan', 'refund' => 'Refund', 'purchase' => 'Pembelian', 'debt_payment' => 'Bayar hutang',
            'receivable_payment' => 'Terima piutang', 'in' => 'Masuk', 'out' => 'Keluar', 'adjust' => 'Opname', 'transfer_in' => 'Transfer masuk',
            'transfer_out' => 'Transfer keluar', 'return' => 'Retur', 'void' => 'Batal', 'out_' => '', 'langsung' => 'Langsung', 'pesanan' => 'Pesanan',
            'online' => 'Online', 'cash' => 'Tunai', 'transfer' => 'Transfer', 'qris' => 'QRIS', 'ewallet' => 'E-Wallet'];

        return $map[$v] ?? ucfirst(str_replace('_', ' ', (string) $v));
    }

    public static function money($n): string
    {
        $n = (float) $n;

        return ($n < 0 ? '-' : '') . 'Rp ' . number_format(abs($n), 0, ',', '.');
    }

    public static function cell(array $col, object $row, array $maps, string $plain = ''): string
    {
        [$name, , $fmt] = array_pad($col, 3, 'text');
        $v = $row->$name ?? null;
        if ($v === null || $v === '') {
            return '<span class="muted">-</span>';
        }
        switch ($fmt) {
            case 'money': return e(self::money($v));
            case 'pct': return e(rtrim(rtrim(number_format((float) $v, 2, ',', '.'), '0'), ',') . '%');
            case 'date': return e(Carbon::parse($v)->format('d/m/Y'));
            case 'datetime': return e(Carbon::parse($v)->format('d/m/Y H:i'));
            case 'status': return $v ? '<span class="badge ok">Aktif</span>' : '<span class="badge bad">Nonaktif</span>';
            case 'yesno': return $v ? 'Ya' : 'Tidak';
            case 'badge': return '<span class="badge ' . (self::BADGE[$v] ?? 'gray') . '">' . e(self::label((string) $v)) . '</span>';
            case 'rel': return e($maps[$name][$v] ?? '-');
            case 'img': return '<img class="thumb" src="' . e(asset('storage/' . $v)) . '" alt="">';
            case 'bytes': return e(number_format($v / 1024, 1, ',', '.') . ' KB');
            case 'long': return '<span class="clip" title="' . e($v) . '">' . e(\Illuminate\Support\Str::limit((string) $v, 70)) . '</span>';
            case 'stock':
                $low = isset($row->min_stock) && $v <= $row->min_stock;

                return '<b class="' . ($low ? 'low' : '') . '">' . e($v) . '</b>' . ($low ? ' <span class="badge warn">Menipis</span>' : '');
            default: return e($v);
        }
    }
}
