<p align="center">
    <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
</p>

<h2 align="center">Laravel 12 File Manager with jsTree</h2>

<p align="center">
    A simple file manager application built with <strong>Laravel 12</strong> and <strong>jsTree</strong> for folder browsing and management.
</p>

---

## ✨ Fitur Utama

-   ✅ Menggunakan **Laravel 12**
-   🌲 Navigasi struktur folder dengan **jsTree**
-   👤 Setiap user yang registrasi akan otomatis dibuatkan **direktori pribadi**
-   📂 Akses dan pengelolaan file terbatas hanya pada folder milik masing-masing user
-   📁 Fitur: **upload, create folder, rename, delete** file/folder
-   🔐 Autentikasi dan middleware Laravel bawaan (`auth`, `verified`)
-   🖼️ Dukungan untuk **tampilan screenshot** (akan ditambahkan di bawah)

---

## 📸 Screenshot

Berikut adalah beberapa tampilan antarmuka dari aplikasi ini:

<!-- Ganti link dengan gambar setelah diunggah ke repo atau image hosting -->

1. Dashboard File Manager  
   ![Dashboard Screenshot](screenshots/dashboard.png)

2. Struktur Folder jsTree  
   ![Tree View Screenshot](screenshots/tree-view.png)

3. Form Upload File  
   ![Upload Screenshot](screenshots/upload.png)

---

## 🚀 Cara Install (Lokal)

```bash
git clone https://github.com/dedirsam/js-tree-filemanager.git
cd js-tree-filemanager
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install && npm run build
php artisan serve
```
