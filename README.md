# 🍽️ Katalog Kuliner UMKM

Proyek ini adalah sebuah platform website **Katalog Kuliner UMKM** yang dibangun menggunakan PHP Native (PDO) dan MySQL. Website ini dibuat untuk memenuhi **Tugas Besar Sistem Manajemen Basis Data (Tubes SMBD) Kelompok 2**.

Platform ini memungkinkan pengguna umum untuk mencari dan melihat detail toko kuliner UMKM lokal lengkap beserta buku menu, sertifikasi halal, jam operasional, kontak, dan metode pembayaran. Selain itu, terdapat panel admin untuk mengelola seluruh data toko dan menu.

---

## ✨ Fitur Utama

### 👥 Halaman Publik (Pengguna Umum)
* **Katalog Beranda:** Menampilkan seluruh daftar toko kuliner UMKM yang terdaftar.
* **Filter Pencarian Canggih:** * Pencarian berdasarkan nama/keyword toko.
  * Filter berdasarkan **Jenis Kuliner**.
  * Filter berdasarkan **Jam Operasional**.
  * Filter berdasarkan **Rentang Harga Menu** (misal: Di bawah Rp 20.000, Rp 20.000 - Rp 40.000, dll).
* **Detail Toko:** Menampilkan informasi lengkap meliputi jenis toko, status halal, buku menu beserta harga, kontak resmi (WhatsApp/Instagram), ketersediaan pembayaran cashless/tunai, serta ketersediaan di platform ojek online.

### 🔐 Halaman Admin (Manajemen Data)
* **Login Terpusat:** Akses aman menggunakan kredensial admin.
* **Dashboard Manajemen:** Ringkasan dan kontrol penuh atas seluruh entitas toko.
* **Manajemen Toko (CRUD):** Tambah, Edit, dan Hapus data toko. Tersedia pengisian dinamis untuk:
  * Kontak Resmi.
  * *Checkbox* Metode Pembayaran (Qris, Cash, Transfer, dll).
  * *Checkbox* Mitra Pengiriman (GoFood, GrabFood, ShopeeFood, dll).
  * *Sistem penyimpanan data relasional telah menggunakan **Database Transaction** sehingga aman dari anomali data saat dihapus/diupdate.*
* **Manajemen Menu:** Tambah dan hapus buku menu per masing-masing toko secara spesifik.

---

## 🛠️ Teknologi yang Digunakan

* **Backend:** PHP 8+ (PDO - PHP Data Objects)
* **Database:** MySQL
* **Frontend:** HTML5, CSS3 (Native, Custom Styling)
* **Font:** Google Fonts (Poppins)

---

## 📂 Struktur Folder

```text
📁 umkm-katalog/
│
├── 📁 css/                 # Folder berisi stylesheet untuk UI website
│   ├── dashboard.css
│   ├── detail.css
│   ├── index.css
│   └── toko_form.css
│
├── config.php            # File koneksi PDO ke MySQL Database
├── dashboard.php         # Halaman utama Admin (List Toko)
├── detail.php            # Halaman detail publik toko & menu
├── index.php             # Halaman utama publik & pencarian
├── login.php             # Halaman autentikasi admin
├── menu_manage.php       # Halaman pengelolaan menu spesifik toko
├── toko_delete.php       # Aksi penghapusan toko (Transaction System)
├── toko_form.php         # Halaman Tambah/Edit Toko dinamis
└── README.md             # Dokumentasi repositori
