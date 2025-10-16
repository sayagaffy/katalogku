# 🗺️ Peta Proyek Kaitkan: Panduan Lengkap untuk AI Assistant

Dokumen ini adalah ringkasan utama dan panduan komprehensif untuk membangun platform SaaS **Kaitkan**. Semua informasi di bawah ini disarikan dari dokumen-dokumen proyek yang telah disediakan. Gunakan ini sebagai referensi utama untuk setiap permintaan pembuatan kode.

---

## 1. Visi & Konsep Inti Proyek

* [cite_start]**Nama Produk:** Kaitkan [cite: 149]
* [cite_start]**Tagline:** "Link di Bio = Katalog Cantik" [cite: 149]
* [cite_start]**Visi:** Memberdayakan 13,5 juta penjual di media sosial Indonesia untuk mengubah pengikut menjadi pelanggan melalui katalog produk yang dioptimalkan untuk seluler dan dapat diakses melalui satu tautan. [cite: 149]
* [cite_start]**Masalah yang Diselesaikan:** Mengatasi keterbatasan satu tautan di bio media sosial, proses pemesanan manual yang rumit, dan kurangnya analisis produk bagi penjual di Indonesia. [cite: 150]
* **Target Pengguna Utama:**
    1.  [cite_start]**Penjual Umum (UMKM):** Memiliki 10-50 produk, fokus pada kemudahan penggunaan dan presentasi profesional. [cite: 190]
    2.  [cite_start]**Reseller:** Mengelola 100+ produk dari berbagai pemasok, membutuhkan organisasi katalog yang baik. [cite: 190]
* [cite_start]**Alur Pengguna Inti:** Pengguna mendaftar -> Mengunggah produk -> Mendapat satu tautan katalog -> Membagikan tautan di bio media sosial -> Pelanggan mengklik tautan -> Pelanggan memesan langsung via WhatsApp. [cite: 49, 189]

---

## 2. Spesifikasi Teknis & Arsitektur

### Backend (Laravel 11)
* [cite_start]**Database:** MySQL 8.0 [cite: 119]
* [cite_start]**Otentikasi:** Laravel Sanctum dengan verifikasi **SMS OTP** (tanpa email). [cite: 191]
* [cite_start]**Layanan Eksternal:** **Zenziva** untuk pengiriman OTP. [cite: 190]
* [cite_start]**Pemrosesan Gambar:** Library **Intervention Image**. [cite: 190]
* [cite_start]**Penyimpanan File:** `public` disk lokal. [cite: 190]

### Frontend (Vue.js 3)
* [cite_start]**Build Tool:** Vite [cite: 84, 190]
* [cite_start]**Styling:** Tailwind CSS (Mobile-First). [cite: 84, 190, 198]
* [cite_start]**State Management:** Pinia [cite: 84, 190]
* [cite_start]**Routing:** Vue Router [cite: 84, 190]
* [cite_start]**HTTP Client:** Axios [cite: 84, 190]
* [cite_start]**Arsitektur:** Menggunakan Composition API dengan `<script setup>`. [cite: 194, 195]

---

## 3. Aturan Kritis & Fungsionalitas Inti (MVP)

### Alur Otentikasi
* [cite_start]**Pendaftaran:** Hanya menggunakan **nomor WhatsApp**. [cite: 191]
* [cite_start]**Verifikasi:** Kode OTP 6 digit dikirim via Zenziva, berlaku selama **5 menit**. [cite: 191]
* [cite_start]**Rate Limit:** Maksimal **3 permintaan OTP per jam** untuk setiap nomor WhatsApp. [cite: 191]
* [cite_start]**Login:** Menggunakan **nomor WhatsApp dan password** (OTP tidak diperlukan saat login). [cite: 191]

### Pemrosesan Gambar (Wajib)
* [cite_start]**Batas Unggah:** Maksimal **10MB** (format jpg, png, webp). [cite: 191]
* **Proses Otomatis:**
    1.  [cite_start]Gambar di-resize ke ukuran maksimal **1000x1000px** (rasio aspek dipertahankan). [cite: 191]
    2.  [cite_start]Dikompresi dan disimpan sebagai **WebP** (kualitas 80%). [cite: 191]
    3.  [cite_start]Dibuat juga versi **JPG fallback** (kualitas 85%). [cite: 191]
* [cite_start]**Target Performa:** Proses unggah hingga kompresi harus selesai dalam **< 5 detik**. [cite: 191]
* [cite_start]**Penyimpanan:** Disimpan di `/storage/products/{product_id}.webp` dan `/storage/products/{product_id}.jpg`. [cite: 191]

### Manajemen Katalog & Produk
* [cite_start]**Struktur:** Satu pengguna memiliki satu katalog, dan katalog tersebut dapat memiliki banyak kategori. [cite: 192]
* [cite_start]**Batas Produk (Tier Gratis):** Maksimal **50 produk**. [cite: 158, 192]
* [cite_start]**Kategori Produk:** `elektronik`, `fashion`, `makanan`, `kecantikan`, `rumah_tangga`, `lainnya`. [cite: 135, 192]

### Halaman Katalog Publik
* [cite_start]**URL:** `kaitkan.id/{username}` [cite: 192]
* [cite_start]**Performa Kritis:** Halaman **wajib dimuat dalam < 2 detik** pada jaringan 3G. [cite: 167, 192]
* [cite_start]**Desain:** Wajib **Mobile-First**, karena 90%+ pengguna akan mengakses dari perangkat seluler. [cite: 192]
* [cite_start]**Integrasi WhatsApp:** Tombol "Order" akan langsung membuka WhatsApp dengan pesan yang sudah terisi format: `"Halo kak, mau order {Nama Produk} - Rp {Harga}"`. [cite: 192]

---

## 4. Struktur Database & API

### Skema Database
* [cite_start]**Tabel Utama:** `users`, `otp_codes`, `catalogs`, `products`, `clicks`. [cite: 118, 192]
* **Relasi Kunci:**
    * [cite_start]`User` hasOne `Catalog` [cite: 123, 193]
    * [cite_start]`Catalog` hasMany `Products` [cite: 123, 193]
    * [cite_start]`Product` hasMany `Clicks` [cite: 123, 193]
* **Indeks Penting:**
    * [cite_start]Kolom `whatsapp` dan `username` pada tabel `users` harus **UNIQUE**. [cite: 125, 193]
    * [cite_start]Kolom `username` pada tabel `catalogs` harus **UNIQUE**. [cite: 131, 193]

### Prinsip Desain API
* **Format Respons Sukses:**
    ```json
    {
      "success": true,
      "data": { ... },
      "message": "Operasi berhasil diselesaikan"
    }
    ```
* **Format Respons Error:**
    ```json
    {
      "success": false,
      "message": "Deskripsi error",
      "errors": { "field_name": ["Pesan error validasi"] }
    }
    ```
* [cite_start]**Otentikasi API:** Menggunakan **Bearer Token** dari Laravel Sanctum. [cite: 193]
* [cite_start]**Endpoint Publik:** Endpoint yang tidak memerlukan otentikasi adalah `/api/public/{username}` dan `/api/clicks`. [cite: 193]

---

## 5. Standar Kode & Panduan Implementasi

### Aturan Umum
* [cite_start]**Bahasa:** Antarmuka dan pesan error harus dalam **Bahasa Indonesia**. [cite: 208]
* [cite_start]**Performa:** Prioritaskan waktu muat yang cepat dan responsivitas. [cite: 208]
* [cite_start]**Keamanan:** Lakukan validasi pada semua input dari pengguna dan terapkan rate limiting. [cite: 208]
* [cite_start]**Fokus MVP:** Hindari mengimplementasikan fitur di luar lingkup MVP seperti integrasi pembayaran, impor dari marketplace, atau login via media sosial. [cite: 201]

### Backend (Laravel)
* [cite_start]Gunakan **Service Layer** untuk menempatkan logika bisnis (misal: `OTPService`, `ImageService`). [cite: 194]
* [cite_start]Gunakan **Form Requests** untuk validasi data yang masuk. [cite: 194]
* [cite_start]Gunakan **API Resources** untuk memformat respons JSON secara konsisten. [cite: 194]

### Frontend (Vue)
* [cite_start]Gunakan **Composables** untuk logika yang dapat digunakan kembali (misal: `useImageUpload`). [cite: 195]
* [cite_start]Gunakan **Service Layer** (`services/api.js`) untuk semua interaksi dengan API backend. [cite: 195]
* [cite_start]Gunakan **Pinia stores** untuk mengelola state global seperti data pengguna dan produk. [cite: 195]
* [cite_start]**Desain Responsif:** Gunakan utility classes dari Tailwind CSS dengan pendekatan mobile-first (misal: `sm:`, `md:`, `lg:`). [cite: 195]

---

Dokumen ini harus menjadi satu-satunya sumber kebenaran (`single source of truth`) selama proses pengembangan. Jika ada keraguan, kembali ke dokumen ini.
