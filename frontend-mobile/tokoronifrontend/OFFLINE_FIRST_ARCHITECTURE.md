# Offline-First + Auto Sync Architecture

Dokumen ini menjelaskan implementasi offline-first pada Flutter app tanpa mengubah API Laravel yang sudah stabil.

## Tujuan

- Aplikasi tetap bisa dipakai saat offline.
- Data perubahan lokal masuk ke sync queue.
- Saat koneksi kembali, data otomatis tersinkron ke Laravel.
- Flow auth, role, dan UI existing tetap dipertahankan.

## Komponen Utama

- `Local SQLite`:
  - `offline_products`
  - `offline_categories`
  - `offline_transaction_drafts`
  - `offline_cart`
  - `offline_cart_items`
  - `sync_queue`
  - `offline_app_session`
  - `offline_sync_meta`
- `Sync Manager`: listener konektivitas + worker sinkronisasi background.
- `Sync Queue Repository`: merge operasi `create/update/delete`, retry + backoff.
- `Sync API Client`: kirim queue ke endpoint Laravel existing (tanpa ubah kontrak API).

## Data yang Disimpan Lokal

- Master:
  - Produk
  - Kategori
  - Stok (melekat pada field `stock` produk)
- Operasional:
  - Cart aktif kasir
  - Draft transaksi / transaksi pending sync
- Session:
  - token, user id, role basic access (mirror dari SharedPreferences)

## Metadata Sinkronisasi

Setiap tabel domain memakai metadata:

- `sync_status`: `synced | pending_create | pending_update | pending_delete | failed`
- `updated_at`
- `deleted_at`
- `local_revision`
- `last_error`

## Strategi Baca/Tulis

- Baca data:
  - `remote-first`
  - sukses => update cache lokal
  - gagal koneksi => fallback ke SQLite
- Tulis data:
  - online => kirim ke server, update cache lokal
  - offline => simpan lokal + enqueue operasi sync

## Konflik Data

Strategi conflict resolution:

- `updated_at` strategy
- Record lokal yang masih `pending_*` tidak ditimpa pull server jika perubahan lokal lebih baru.
- Setelah sync sukses, server menjadi source of truth dan record ditandai `synced`.

## Retry Mechanism

- Queue item gagal akan:
  - menyimpan `last_error`
  - menaikkan `retry_count`
  - menjadwalkan `next_retry_at` dengan exponential backoff

## Lifecycle

- `main.dart` memanggil bootstrap offline saat app start:
  - init DB
  - mirror session
  - start connectivity listener
  - auto trigger sync

