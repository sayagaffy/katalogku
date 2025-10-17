# Step-by-Step Implementation Needs (Kaitkan)

This checklist prepares the app for Phone‑first auth (OTP + 6‑digit PIN), WebSMS, Firebase (via Custom Token), Firestore preferences, and Cloudflare R2 for images.

Use this as a practical runbook. Follow steps in order; items marked Optional can be deferred.

---

## 0) Prerequisites

- Accounts
  - WebSMS: active token (API Key)
  - Cloudflare R2: Account ID, Bucket, Access Key + Secret, CDN domain (optional)
  - Firebase: Project, Service Account JSON (backend), Web App config (frontend)
- Access
  - SSH/CI access to API server; permission to set environment variables
  - DB access (MySQL)

---

## 1) Backend: Base Setup

- Ensure API boots locally
  - `cd kaitkan-api`
  - `composer install`
  - `cp .env.example .env` (if needed) → set DB creds
  - `php artisan key:generate`
  - `php artisan migrate`
  - Seed curated themes: `php artisan db:seed --class=ThemeSeeder`
- Verify routes exist (sanity): `php artisan route:list | findstr /I profile`

References:
- Migrations and models were added for themes/links/link_groups
- Theme seeder: `kaitkan-api/database/seeders/ThemeSeeder.php:1`

---

## 2) WebSMS (SMS “sandi masuk”)

- Backend env (`kaitkan-api/.env`)
  - `WEBSMS_API_TOKEN=YOUR_TOKEN_HERE`
- Message copy (avoid banned words: “OTP, Kode, Verifikasi, …”)
  - Recommended: `Sandi masuk Kaitkan Anda: 123456. Berlaku 5 menit. Jangan bagikan.`
- Rate limiting
  - Already set at route (3/min per IP) and DB (3/hour per number)
- Quick test (after server runs)
  - POST `/api/auth/send-otp` with `{ whatsapp: "08123…" }`
  - Check logs/delivery; then verify with POST `/api/auth/verify-otp`

References:
- Config token: `kaitkan-api/config/services.php:1`
- OTP service: `kaitkan-api/app/Services/OTPService.php:1`
- Routes: `kaitkan-api/routes/api.php:1`

---

## 3) Cloudflare R2 (S3) for Images

- Create R2 bucket (e.g., `kaitkan-media`) and Access Keys
- Optional: Configure CDN (e.g., `https://cdn.kaitkan.id`) to serve public files
- Backend env (`kaitkan-api/.env`)
  - `IMAGE_DISK=r2`
  - `AWS_ACCESS_KEY_ID=...`
  - `AWS_SECRET_ACCESS_KEY=...`
  - `AWS_BUCKET=kaitkan-media`
  - `AWS_DEFAULT_REGION=auto`
  - `AWS_ENDPOINT=https://<account-id>.r2.cloudflarestorage.com`
  - `R2_CDN_URL=https://cdn.kaitkan.id` (optional)
- Validate
  - Upload avatar via `/api/profile` or `/api/profile/avatar`
  - Response avatar URLs should resolve under CDN/base endpoint

References:
- Filesystem config: `kaitkan-api/config/filesystems.php:1`
- ImageService (now disk-configurable + CDN-aware): `kaitkan-api/app/Services/ImageService.php:1`

---

## 4) PIN-based Auth (Phone-first)

- DB migration already included; run: `php artisan migrate`
- Set PIN (after user is logged in or has valid session)
  - POST `/api/auth/set-pin` with `{ pin:"123456", pin_confirmation:"123456" }`
- Login with PIN
  - POST `/api/auth/login-pin` with `{ whatsapp:"08123…", pin:"123456" }`
- Keep existing OTP for registration and device recovery

References:
- Controller methods: `kaitkan-api/app/Http/Controllers/AuthController.php:1`
- Routes: `kaitkan-api/routes/api.php:1`

---

## 5) Firebase Integration (Custom Token)

Goal: FE signs in to Firebase without forcing Firebase Phone Auth UX.

- Backend
  - Install SDK (on server or local): `composer require kreait/laravel-firebase` (or `kreait/firebase-php`)
  - Set env in `kaitkan-api/.env`:
    - `FIREBASE_PROJECT_ID=...`
    - `FIREBASE_CREDENTIALS=/path/to/service-account.json` (or JSON/base64 variant)
  - Verify endpoint:
    - Authenticated POST `/api/auth/firebase-token` → returns `{ custom_token: "..." }`
- Frontend (Optional now; needed when enabling Firebase features)
  - Set env in `kaitkan-frontend/.env`:
    - `VITE_AUTH_PROVIDER=firebase` (when ready)
    - `VITE_FIREBASE_API_KEY=...`
    - `VITE_FIREBASE_AUTH_DOMAIN=...`
    - `VITE_FIREBASE_PROJECT_ID=...`
    - `VITE_FIREBASE_MESSAGING_SENDER_ID=...`
    - `VITE_FIREBASE_APP_ID=...`
  - FE flow (pseudo):
    1) Login to Laravel (PIN)
    2) Request `/api/auth/firebase-token`
    3) `signInWithCustomToken(custom_token)` on Firebase

References:
- Custom token endpoint (stubbed if SDK missing): `kaitkan-api/app/Http/Controllers/AuthController.php:1`

---

## 6) Frontend API & Env

- Set API base
  - `VITE_API_BASE_URL=http://localhost:8000` (dev) or `https://api.kaitkan.id` (prod)
  - `VITE_API_PREFIX=/api`
- Existing screens updated:
  - Settings uses `/profile` and `/themes`
  - Links (basic CRUD) uses `/links`
- Optional next: switch Login/Register screens to PIN flow

References:
- Endpoints map: `kaitkan-frontend/src/config/api.js:1`
- Services: `profile.service.js`, `theme.service.js`, `link.service.js`

---

## 7) Firestore Preferences (Phase C)

- Firestore rules (minimal)
  - Only owner (`request.auth.uid == resource.id` or subdoc under uid) can read/write their preferences
- API behavior (planned)
  - `/profile` GET merges MySQL profile with Firestore `users/{uid}/settings`
  - `/profile` PATCH updates MySQL + Firestore (shadow write)
- Rollout
  - Start with MySQL as source, add Firestore writing; after confidence, make Firestore source for preferences

---

## 8) Background Theme (Optional but Recommended)

- Backend (planned quick path)
  - Add columns to `catalogs`: `bg_image_webp`, `bg_image_jpg`, `bg_overlay_opacity` (0–1)
  - Endpoint: `POST /api/profile/background` (upload to R2), `PATCH /api/profile` supports overlay
- Frontend
  - Settings: upload background + overlay slider (preview)
  - Public page: render background (cover, lazyload), ensure compressed

---

## 9) QA & Validation Checklist

- Auth
  - Send OTP via WebSMS; verify & set PIN; login with PIN; logout/login; device trust policy
- Media
  - Upload avatar/product/link thumbnail → URLs under CDN
  - Cache headers good (inspect via browser devtools), reasonable file sizes
- Profile/Links
  - GET/PATCH `/profile` works; theme selection persists; CRUD links + reorder OK
- Performance
  - Public page LCP < 2.5s on 3G fast; payload sizes controlled
- Security
  - Auth headers respected; rate limits work; sensitive envs not exposed

---

## 10) Rollout & Ops

- Flags
  - Keep PIN login behind feature flag initially; toggle per cohort
- Monitoring
  - Log SMS delivery errors; alert on spike of OTP failures; track image 4xx/5xx from CDN
- Cost
  - Monitor R2 egress; compress images (<200KB where possible)

---

## 11) Env Summary (Copy/Paste)

Backend (`kaitkan-api/.env`):
```
APP_URL=https://api.kaitkan.id
# DB_*

WEBSMS_API_TOKEN=YOUR_WEBSMS_TOKEN

IMAGE_DISK=r2
AWS_ACCESS_KEY_ID=...
AWS_SECRET_ACCESS_KEY=...
AWS_BUCKET=kaitkan-media
AWS_DEFAULT_REGION=auto
AWS_ENDPOINT=https://<account-id>.r2.cloudflarestorage.com
R2_CDN_URL=https://cdn.kaitkan.id

FIREBASE_PROJECT_ID=...
FIREBASE_CREDENTIALS=/secure/path/firebase-admin.json

SANCTUM_STATEFUL_DOMAINS=app.kaitkan.id
SESSION_DOMAIN=.kaitkan.id
```

Frontend (`kaitkan-frontend/.env`):
```
VITE_API_BASE_URL=https://api.kaitkan.id
VITE_API_PREFIX=/api

# Switch when enabling Firebase on FE
VITE_AUTH_PROVIDER=local

# Firebase Web (fill when VITE_AUTH_PROVIDER=firebase)
VITE_FIREBASE_API_KEY=...
VITE_FIREBASE_AUTH_DOMAIN=...
VITE_FIREBASE_PROJECT_ID=...
VITE_FIREBASE_MESSAGING_SENDER_ID=...
VITE_FIREBASE_APP_ID=...
```

---

## 12) Next Actions

- Provide WebSMS token, R2 keys/bucket, Firebase creds
- Apply envs, run migrations & theme seeder
- Decide rollout order: PIN login UI vs Background Theme vs Firebase FE sign-in
- I can proceed to: (a) PIN UI on FE, (b) Background upload + public render, or (c) complete Firebase custom token integration end‑to‑end

