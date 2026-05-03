# Gawe SKP

Gawe SKP adalah aplikasi Laravel + Inertia.js + Vue 3 untuk membantu pengelolaan laporan SKP, hasil kerja, indikator kinerja, perilaku kerja, dan ekspor dokumen pendukung. Aplikasi ini juga mendukung integrasi Google Drive untuk upload dokumen laporan.

## Fitur Utama

- Manajemen laporan SKP
- Auto-generate perilaku kerja saat laporan dibuat
- Master indikator kinerja
- CRUD indikator kinerja
- Input hasil kerja dengan select indikator
- Rencana aksi dinamis
- Upload bukti foto
- Export PDF
- Integrasi Google Drive

## Teknologi

- Laravel 13
- Inertia.js v3
- Vue 3
- PostgreSQL
- Docker
- Nginx
- Supervisor
- Cloudflare Tunnel

## Struktur Docker Production

Setup production ini menggunakan:

- Container aplikasi Laravel + Nginx + PHP-FPM
- Container PostgreSQL
- Container queue worker
- Container scheduler
- Optional container `cloudflared` untuk Cloudflare Tunnel

Port publik yang digunakan:

- Aplikasi: `3526`
- PostgreSQL: `1245`

Port internal container tetap:

- Aplikasi web: `80`
- PostgreSQL: `5432`

## Persiapan Instalasi

Pastikan server sudah memiliki:

- Docker
- Docker Compose
- Akun Cloudflare jika ingin akses via tunnel
- Domain yang sudah diarahkan ke Cloudflare

## Instalasi

1. Clone repository ke server.
2. Buat file environment production:

```bash
cp .env.production.example .env.production
```

3. Isi konfigurasi penting di `.env.production`:

- `APP_NAME`
- `APP_URL`
- `APP_KEY`
- `DB_PASSWORD`
- `SESSION_DOMAIN`
- `TRUSTED_HOSTS`
- `CLOUDFLARED_TUNNEL_TOKEN`
- Google OAuth / Google Drive credentials

4. Build dan jalankan container:

```bash
docker compose --profile tunnel up -d --build
```

5. Jalankan migrasi database:

```bash
docker compose exec app php artisan migrate --force
```

6. Jika diperlukan, jalankan seeder:

```bash
docker compose exec app php artisan db:seed --force
```

7. Generate symlink storage jika belum dibuat otomatis:

```bash
docker compose exec app php artisan storage:link
```

## Akses Aplikasi

- Browser: `https://domain-anda`
- Jika akses langsung ke port host: `http://IP-SERVER:3526`
- Akses PostgreSQL dari luar container: `IP-SERVER:1245`

## Cloudflare Tunnel

Service `cloudflared` pada `docker-compose.yml` diarahkan ke service aplikasi internal.

Target origin yang digunakan:

```text
http://app:80
```

Pastikan token tunnel diisi pada environment:

```env
CLOUDFLARED_TUNNEL_TOKEN=isi_token_tunnel_anda
```

## Environment Penting

Contoh variabel yang wajib dicek:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-anda
APP_TIMEZONE=Asia/Jakarta
SESSION_SECURE_COOKIE=true
TRUSTED_PROXIES=*
TRUSTED_HOSTS=^domain-anda\.tld$
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=gawe_skp
DB_USERNAME=gawe_skp
DB_PASSWORD=change-me
SESSION_DOMAIN=.domain-anda.tld
```

## Catatan Produksi

- Gunakan `APP_URL` dengan skema `https://` agar URL yang dihasilkan konsisten.
- Jangan ubah `DB_PORT` menjadi `1245` di container aplikasi. Nilai `1245` hanya untuk akses host ke PostgreSQL dari luar Docker.
- Jika Anda mengganti domain, perbarui `SESSION_DOMAIN`, `TRUSTED_HOSTS`, dan `GOOGLE_REDIRECT_URI`.

## Lisensi

Proyek ini mengikuti lisensi yang berlaku pada repository ini.
