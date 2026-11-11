# Dokumentasi API Kasir Serbaguna

Base URL: `http://<host>:8000/api`
Header wajib: `Accept: application/json`. Endpoint terproteksi: `Authorization: Bearer <token>`.

## Auth

| Method | Endpoint  | Akses  | Keterangan |
|--------|-----------|--------|------------|
| GET    | /ping     | publik | Cek server hidup |
| POST   | /login    | publik | Body: `email`, `password`, `device_name` (opsional). Balik: `token`, `user` |
| GET    | /me       | login  | Data user yang sedang login |
| POST   | /logout   | login  | Hapus token saat ini |

## Produk & kategori

| Method | Endpoint                      | Akses | Keterangan |
|--------|-------------------------------|-------|------------|
| GET    | /categories                   | login | Daftar kategori |
| POST   | /categories                   | admin | Body: `name` |
| PUT    | /categories/{id}              | admin | |
| DELETE | /categories/{id}              | admin | |
| GET    | /products                     | login | Query: `search`, `category_id`, `low_stock=1`, `active=1`, `per_page` |
| GET    | /products/{id}                | login | |
| GET    | /products/barcode/{code}      | login | Cari dari scan barcode/SKU |
| POST   | /products                     | admin | `sku`, `name`, `price` wajib; `barcode`, `cost_price`, `stock`, `min_stock`, `unit`, `category_id` |
| PUT    | /products/{id}                | admin | Stok tidak diubah di sini |
| DELETE | /products/{id}                | admin | Soft delete |
| GET    | /products/{id}/stock          | admin | Riwayat stok |
| POST   | /products/{id}/stock          | admin | `type` (in/out/adjust), `qty`, `note` |

## Pelanggan

`GET/POST /customers`, `GET/PUT/DELETE /customers/{id}` (login). Query list: `search`.

## Transaksi

| Method | Endpoint                    | Akses | Keterangan |
|--------|-----------------------------|-------|------------|
| GET    | /transactions               | login | Kasir: milik sendiri; admin: semua. Query: `from`, `to`, `status` |
| GET    | /transactions/{id}          | login | Detail + item |
| POST   | /transactions               | login | Checkout (lihat contoh) |
| POST   | /transactions/{id}/void     | admin | Batalkan, stok dikembalikan |

Contoh checkout:

```json
POST /api/transactions
{
  "client_uuid": "6f1c7c0e-1b3a-4a7e-9d51-2d9a5a3c7b10",
  "customer_id": null,
  "items": [
    { "product_id": 1, "qty": 2 },
    { "product_id": 4, "qty": 1 }
  ],
  "discount": 0,
  "tax_percent": 0,
  "paid": 20000,
  "payment_method": "cash",
  "note": null
}
```

- Harga dihitung server dari database (bukan dari aplikasi).
- `client_uuid`: buat satu UUID per keranjang di Android. Jika request diulang (sinyal putus), server mengembalikan transaksi yang sama, tidak membuat ganda.
- Error validasi (stok kurang, uang kurang) berstatus 422.

## Laporan (admin)

`GET /reports/summary?from=2026-11-01&to=2026-11-09`
Balik: total transaksi, omzet, diskon, estimasi laba, penjualan harian, 10 produk terlaris, rekap per metode bayar, jumlah stok menipis.

## Kode status umum

200/201 sukses, 401 belum login, 403 tidak punya akses, 404 tidak ditemukan, 422 validasi gagal.
