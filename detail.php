<?php
require_once 'config.php';

$id_toko = $_GET['id'] ?? null;
if (!$id_toko) { header("Location: index.php"); exit; }

// 1. Ambil detail data Toko
$toko_stmt = $pdo->prepare("SELECT t.*, j.nama_jenis, s.value_sertifikasi, jd.buka, jd.tutup 
                            FROM toko t
                            JOIN jenis j ON t.id_jenis = j.id_jenis
                            JOIN sertifikasi_halal s ON t.id_sertifikasi = s.id_sertifikasi
                            JOIN jadwal jd ON t.id_jadwal = jd.id_jadwal
                            WHERE t.id_toko = ?");
$toko_stmt->execute([$id_toko]);
$toko = $toko_stmt->fetch();
if (!$toko) { die("Toko tidak ditemukan."); }

// 2. Ambil Kontak Toko
$kontak_stmt = $pdo->prepare("SELECT * FROM kontak WHERE id_toko = ?");
$kontak_stmt->execute([$id_toko]);
$list_kontak = $kontak_stmt->fetchAll();

// Map kontak agar mudah dirender
$kontak_map = [];
foreach ($list_kontak as $k) {
    $kontak_map[$k['tipe_kontak']] = $k['value_kontak'];
}

// 3. Ambil Metode Pembayaran Toko
$metode_stmt = $pdo->prepare("SELECT mp.nama_metode FROM rel_metode_pembayaran rmp JOIN metode_pembayaran mp ON rmp.id_metode = mp.id_metode WHERE rmp.id_toko = ?");
$metode_stmt->execute([$id_toko]);
$list_metode = $metode_stmt->fetchAll(PDO::FETCH_COLUMN);

// 4. Ambil Mitra Pengiriman Online Toko
$mitra_stmt = $pdo->prepare("SELECT m.nama_mitra FROM rel_mitra rm JOIN mitra m ON rm.id_mitra = m.id_mitra WHERE rm.id_toko = ?");
$mitra_stmt->execute([$id_toko]);
$list_mitra = $mitra_stmt->fetchAll(PDO::FETCH_COLUMN);

// 5. Ambil Buku Menu Lengkap
$menu_stmt = $pdo->prepare("SELECT m.*, k.nama_kategori FROM menu m JOIN kategori k ON m.id_kategori = k.id_kategori WHERE m.id_toko = ? ORDER BY m.id_menu DESC");
$menu_stmt->execute([$id_toko]);
$list_menu = $menu_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Toko - <?= htmlspecialchars($toko['nama_toko']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/detail.css">
</head>

<body>

    <div class="container">
        <a href="index.php" class="btn-back">&larr; Kembali ke Beranda</a>

        <div class="grid-detail">
            <div class="sidebar-info">
                <h1><?= htmlspecialchars($toko['nama_toko']) ?></h1>

                <div class="info-block">
                    <h4>Jenis Kuliner</h4>
                    <p><?= $toko['nama_jenis'] ?></p>
                </div>

                <div class="info-block">
                    <h4>Status Sertifikasi</h4>
                    <p><?= $toko['value_sertifikasi'] ?></p>
                </div>

                <div class="info-block">
                    <h4>Jam Operasional</h4>
                    <p>🕒 Buka: <?= substr($toko['buka'],0,5) ?> - Tutup: <?= substr($toko['tutup'],0,5) ?> WIB</p>
                </div>

                <div class="info-block">
                    <h4>Kontak Resmi</h4>
                    <p>📞 Telp/WA: <?= htmlspecialchars($kontak_map['telepon'] ?? 'Tidak tersedia') ?></p>
                    <p>📸 Instagram: <?= htmlspecialchars($kontak_map['instagram'] ?? 'Tidak tersedia') ?></p>
                    <p>🌐 Website: <?= htmlspecialchars($kontak_map['website'] ?? 'Tidak tersedia') ?></p>
                </div>

                <div class="info-block">
                    <h4>Metode Pembayaran</h4>
                    <p><?= !empty($list_metode) ? implode(', ', $list_metode) : 'Tunai / Cash' ?></p>
                </div>

                <div class="info-block">
                    <h4>Mitra Pengiriman</h4>
                    <p><?= !empty($list_mitra) ? implode(', ', $list_mitra) : 'Hanya Pesan di Tempat' ?></p>
                </div>
            </div>

            <div class="main-content">
                <h2>Buku Menu Lengkap Toko</h2>

                <?php if(count($list_menu) > 0): ?>
                <table class="menu-table">
                    <thead>
                        <tr>
                            <th>Nama Menu</th>
                            <th>Kategori Sifat</th>
                            <th>Harga</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($list_menu as $menu): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($menu['nama_menu']) ?></strong></td>
                            <td><span class=\"cat-badge\"><?= $menu['nama_kategori'] ?></span></td>
                            <td class="price"><?= formatRupiah($menu['harga']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p style="color: #888; font-style: italic; padding: 20px 0; text-align: center;">Toko ini belum
                    memasukkan daftar menu ke database.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

</body>

</html>