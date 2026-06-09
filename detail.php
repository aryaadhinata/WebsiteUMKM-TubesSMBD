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

// 3. Ambil Metode Pembayaran Toko
$metode_stmt = $pdo->prepare("SELECT mp.nama_metode FROM rel_metode_pembayaran rmp JOIN metode_pembayaran mp ON rmp.id_metode = mp.id_metode WHERE rmp.id_toko = ?");
$metode_stmt->execute([$id_toko]);
$list_metode = $metode_stmt->fetchAll();

// 4. Ambil Mitra Pengiriman Toko
$mitra_stmt = $pdo->prepare("SELECT m.nama_mitra FROM rel_mitra rm JOIN mitra m ON rm.id_mitra = m.id_mitra WHERE rm.id_toko = ?");
$mitra_stmt->execute([$id_toko]);
$list_mitra = $mitra_stmt->fetchAll();

// 5. Ambil Menu Lengkap Toko beserta Kategori
$menu_stmt = $pdo->prepare("SELECT m.*, k.nama_kategori FROM menu m JOIN kategori k ON m.id_kategori = k.id_kategori WHERE m.id_toko = ? ORDER BY k.nama_kategori ASC");
$menu_stmt->execute([$id_toko]);
$list_menu = $menu_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Toko - <?= htmlspecialchars($toko['nama_toko']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background-color: #F4F4F4; color: #333; padding-bottom: 50px; }
        .container { width: 85%; max-width: 1100px; margin: 0 auto; padding-top: 30px; }
        .btn-back { display: inline-block; background: #667302; color: #fff; text-decoration: none; padding: 10px 20px; border-radius: 5px; font-weight: 600; margin-bottom: 20px; }
        
        .grid-detail { display: grid; grid-template-columns: 1fr 2fr; gap: 30px; }
        .sidebar-info { background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); height: fit-content; }
        .sidebar-info h1 { color: #400101; font-size: 26px; margin-bottom: 15px; }
        .info-block { margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #eee; }
        .info-block h4 { color: #667302; margin-bottom: 5px; font-size: 14px; text-transform: uppercase; }
        
        .main-content { background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .main-content h2 { color: #400101; border-bottom: 3px solid #667302; padding-bottom: 10px; margin-bottom: 20px; }
        
        .menu-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .menu-table th, .menu-table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; }
        .menu-table th { background-color: #f9f9f9; color: #400101; font-weight: 600; }
        .menu-table tr:hover { background-color: #fdfdfd; }
        .price { font-weight: bold; color: #400101; }
        .cat-badge { background: #E0F7FA; color: #006064; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; }
    </style>
</head>
<body>

    <div class="container">
        <a href="index.php" class="btn-back">&larr; Kembali ke Katalog</a>
        
        <div class="grid-detail">
            <div class="sidebar-info">
                <h1><?= htmlspecialchars($toko['nama_toko']) ?></h1>
                
                <div class="info-block">
                    <h4>Jenis Kuliner & Sertifikasi</h4>
                    <p><strong><?= $toko['nama_jenis'] ?></strong> (<?= $toko['value_sertifikasi'] ?>)</p>
                </div>
                
                <div class="info-block">
                    <h4>Jam Operasional</h4>
                    <p>🕒 <?= substr($toko['buka'],0,5) ?> - <?= substr($toko['tutup'],0,5) ?> WIB</p>
                </div>

                <div class="info-block">
                    <h4>Kontak Resmi</h4>
                    <?php if(count($list_kontak) > 0): ?>
                        <?php foreach($list_kontak as $k): ?>
                            <p style="margin-bottom: 4px;">🔹 <strong><?= ucfirst($k['tipe_kontak']) ?>:</strong> <?= htmlspecialchars($k['value_kontak']) ?></p>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="color: #888; font-style: italic;">Tidak ada kontak terdaftar.</p>
                    <?php endif; ?>
                </div>

                <div class="info-block">
                    <h4>Metode Pembayaran</h4>
                    <p><?php 
                        $metodes = array_column($list_metode, 'nama_metode');
                        echo !empty($metodes) ? implode(', ', $metodes) : 'Hanya Tunai';
                    ?></p>
                </div>

                <div class="info-block" style="border: none; padding: 0;">
                    <h4>Mitra Pengiriman Online</h4>
                    <p><?php 
                        $mitras = array_column($list_mitra, 'nama_mitra');
                        echo !empty($mitras) ? implode(', ', $mitras) : 'Belum tersedia di platform online';
                    ?></p>
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
                                    <td><span class="cat-badge"><?= $menu['nama_kategori'] ?></span></td>
                                    <td class="price"><?= formatRupiah($menu['harga']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="color: #888; font-style: italic; padding: 20px 0; text-align: center;">Toko ini belum memasukkan daftar menu ke database.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

</body>
</html>