# Firebase + Cloudflare R2 Migration Plan (Kaitkan)

## 1. Why & Goals

- Phone‑first auth for UMKM Indonesia: daftar dengan SMS “sandi masuk” dan set PIN 6 digit, login harian pakai PIN (tanpa email wajib).
- Gunakan WebSMS (hemat biaya) sebagai SMS provider utama; fallback WhatsApp Business bila perlu.
- Integrasi Firebase untuk Firestore (preferences ringan), Analytics, dan FCM — tanpa memaksa Phone Auth Firebase di FE (hindari reCAPTCHA web), lewat Custom Token dari backend.
- Semua media (avatar, produk, link thumbnail, background) ke Cloudflare R2 (S3-compatible) dengan CDN.

Non-goals (tahap ini):
- Migrasi penuh domain data (links/products/clicks) ke Firestore.
- Menghapus Sanctum sepenuhnya — kita jalankan dual-mode sampai cutover siap.

## 2. Target Architecture (Ringkas)

- Identity/Auth: Laravel tetap source‑of‑truth untuk OTP & PIN; setelah login, backend menerbitkan Firebase Custom Token → FE sign-in ke Firebase untuk akses Firestore/Analytics/FCM.
- Data:
  - MySQL (Laravel): profiles/catalogs publik, links, products, clicks, themes.
  - Firestore: user preferences (language, notification flags, theme_id, onboarding flags).
  - R2 (S3): semua gambar.
- SMS: WebSMS `smsgateway` (5 kredit) sebagai default; fallback WhatsApp Business endpoint setelah 2–3 retry.

## 3. WebSMS Integration Notes

- Endpoint default: `https://websms.co.id/api/smsgateway?token={token}&to={to}&msg={msg}`.
- Format nomor: terima `08…`, `62…`, `+62…` → kirim ke WebSMS sebagai `08…` (sudah diimplementasi di OTPService formatPhoneNumberForWebSMS).
- Hindari kata terlarang: “Token, OTP, Kode, Verifikasi, …” → Gunakan: “Sandi masuk Kaitkan Anda: 123456. Berlaku 5 menit. Jangan bagikan.”
- Rate limiting: pertahankan limit per-IP route (3/min) + per nomor (3/jam) di DB. Tambahkan logging hasil provider.

## 4. Cloudflare R2 Setup

Env keys (contoh):
- `IMAGE_DISK=r2`
- `AWS_ACCESS_KEY_ID=...`
- `AWS_SECRET_ACCESS_KEY=...`
- `AWS_BUCKET=kaitkan-media`
- `AWS_DEFAULT_REGION=auto`
- `AWS_ENDPOINT=https://<accountid>.r2.cloudflarestorage.com`
- `R2_CDN_URL=https://cdn.kaitkan.id` (opsional untuk URL publik)

Laravel `config/filesystems.php` (ringkas):
```php
'disks' => [
  'r2' => [
    'driver' => 's3',
    'key' => env('AWS_ACCESS_KEY_ID'),
    'secret' => env('AWS_SECRET_ACCESS_KEY'),
    'region' => env('AWS_DEFAULT_REGION', 'auto'),
    'bucket' => env('AWS_BUCKET'),
    'endpoint' => env('AWS_ENDPOINT'),
    'use_path_style_endpoint' => true,
    'throw' => true,
  ],
],
```

ImageService refactor (intention): gunakan `config('filesystems.image_disk', 'public')` untuk `Storage::disk($disk)` dan URL builder yang mengutamakan `R2_CDN_URL` jika di-set.

## 5. Firebase Integration (Tanpa Email Wajib)

Pendekatan: Custom Token dari backend.
- Backend memasang Firebase Admin (Kreait Laravel Firebase). Endpoint baru: `POST /auth/firebase-token` (require login Sanctum) → kembalikan custom token untuk user.
- FE: setelah login Laravel (PIN), panggil endpoint ini dan `signInWithCustomToken` ke Firebase. FE kini bisa gunakan Firestore/Analytics/FCM tanpa captcha.

User mapping:
- Tambah kolom `firebase_uid` di `users`. Saat pertama kali minta custom token, buat user di Firebase jika belum ada, simpan `firebase_uid`.
- Middleware opsional `FirebaseAuthenticate` untuk menerima Bearer Firebase ID token (fase cutover nanti) sebagai alternatif Sanctum.

## 6. PIN Auth Flow

- Pendaftaran: WhatsApp → kirim “sandi masuk” via SMS → verifikasi → set PIN 6 digit → buat profil draft.
- Login: WhatsApp + PIN (6 digit) → token Sanctum → minta Firebase custom token → FE sign-in Firebase.
- Lupa PIN: kirim “sandi masuk” via SMS → verifikasi → set PIN baru.

Impl teknis (laravel):
- Migration: tambah `pin_hash` (varchar 255) dan `firebase_uid` di tabel `users`.
- Endpoint: `POST /auth/set-pin` (butuh sesi valid/OTP), `POST /auth/login-pin`.
- Validasi PIN: 6 digit numeric; disimpan hashed (bcrypt/argon2id).

## 7. Firestore Preferences

- Dokumen: `users/{uid}/settings`
  - `language`, `notifications` (weekly, clicks, updates), `theme_id`, `onboarding_done`.
- API `/profile`:
  - GET: gabungkan atribut profil (MySQL) dengan preferences (Firestore) bila tersedia.
  - PATCH: update MySQL + Firestore (shadow write) untuk field preferences.

Keuntungan: UI settings tetap cepat di-ship; sumber kebenaran preferensi dipindah bertahap.

## 8. Background Theme (Linktree Feel)

- DB perubahan cepat (profil): kolom `bg_image_webp`, `bg_image_jpg`, `bg_overlay_opacity` (0–1).
- Endpoint: `POST /profile/background` (upload → R2), `PATCH /profile` menerima overlay.
- FE Settings: unggah background + slider overlay; Public page: render BG (lazyload, cover, compressed).

Alternatif: background per theme kurasi (kolom di `themes`) — cocok untuk opsi preset.

## 9. Phases & Timeline (Praktis)

- A: R2 wiring (1–2 hari)
  - Disk R2, refactor ImageService (disk configurable + CDN URL), uji unggah.
- B: PIN Auth (2–3 hari)
  - Migration `pin_hash`, endpoint set-pin, login-pin, lupa-pin; UI kecil di FE (login pakai PIN).
- C: Firebase Custom Token + Firestore Prefs (2–3 hari)
  - Endpoint custom token; FE sign-in Firebase; sinkron preferences.
- D: Background Theme (1–2 hari)
  - Upload BG + overlay; render public page.
- E (opsional): Links → Firestore + realtime preview (3–4 hari) dengan shadow write/rollout.

## 10. Risks & Mitigations

- Dual auth (Sanctum + Firebase) menjadi kompleks → minimalisir dengan urutan: Sanctum dahulu, Firebase hanya untuk FE services.
- Latensi & biaya R2 → kompresi agresif, cache CDN, ukur egress.
- Konsistensi Firestore vs MySQL → batasi dulu hanya preferences; gunakan shadow write.
- SMS deliverability → logging status, fallback WA Business, retry terbatas.

## 11. QA Checklist

- Login PIN multi-device, token refresh, revoke.
- Upload image ke R2 (avatar/produk/link/bg) — ukuran, format, cache headers.
- Preferences sync round-trip (PATCH → Firestore → GET).
- Public page perf (3G fast), BG image dimuat efisien.

## 12. Env Matrix (Ringkas)

Backend:
- `IMAGE_DISK`, `AWS_*`, `R2_CDN_URL`
- `FIREBASE_CREDENTIALS` (path/JSON), `FIREBASE_PROJECT_ID`
- `WEBSMS_TOKEN`

Frontend:
- `VITE_AUTH_PROVIDER=local|firebase`
- `VITE_FIREBASE_API_KEY`, `VITE_FIREBASE_AUTH_DOMAIN`, `VITE_FIREBASE_PROJECT_ID`, `VITE_FIREBASE_MESSAGING_SENDER_ID`, `VITE_FIREBASE_APP_ID`

## 13. Cutover Strategy

- Mulai dengan Sanctum-only + Custom Token untuk FE; stabilkan.
- Pindahkan preferences ke Firestore; ukur.
- Evaluasi memindahkan links ke Firestore bila realtime preview dibutuhkan.

