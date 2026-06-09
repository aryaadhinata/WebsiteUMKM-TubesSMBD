<?php
require_once 'config.php';
if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit; }

// Ambil semua daftar toko dari database untuk dikelola
$stmt = $pdo->query("SELECT t.id_toko, t.nama_toko, j.nama_jenis, s.value_sertifikasi, 
                    (SELECT COUNT(*) FROM menu WHERE id_toko = t.id_toko) as total_menu
                    FROM toko t
                    JOIN jenis j ON t.id_jenis = j.id_jenis
                    JOIN sertifikasi_halal s ON t.id_sertifikasi = s.id_sertifikasi
                    ORDER BY t.id_toko DESC");
$list_toko = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard UMKM</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>

    <div class="sidebar">
        <div>
            <h2>UMKM Control Center</h2>
            <a href="dashboard.php" class="active">🏠 Manajemen Toko</a>
            <a href="index.php" target="_blank">🌐 Lihat Web Publik</a>
        </div>
        <a href="login.php" class="btn-logout">Logout System</a>
    </div>

    <div class="content">
        <div class="header-content">
            <h1>Manajemen Toko UMKM</h1>
            <a href="toko_form.php" class="btn-add">+ Tambah Toko Baru</a>
        </div>

        <table class="dash-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Toko</th>
                    <th>Jenis Kuliner</th>
                    <th>Sertifikasi</th>
                    <th>Jumlah Menu</th>
                    <th style="text-align: center;">Aksi Manajemen</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($list_toko as $row): ?>
                    <tr>
                        <td><?= $row['id_toko'] ?></td>
                        <td><strong><?= htmlspecialchars($row['nama_toko']) ?></strong></td>
                        <td><?= $row['nama_jenis'] ?></td>
                        <td><?= $row['value_sertifikasi'] ?></td>
                        <td><span style="background: #eee; padding: 2px 8px; border-radius: 10px; font-size: 12px; font-weight: bold;"><?= $row['total_menu'] ?> Menu</span></td>
                        <td style="text-align: center;">
                            <a href="menu_manage.php?id_toko=<?= $row['id_toko'] ?>" class="btn-action btn-menu">🍱 Kelola Menu</a>
                            <a href="toko_form.php?id=<?= $row['id_toko'] ?>" class="btn-action btn-edit">✏️ Edit</a>
                            <a href="toko_delete.php?id=<?= $row['id_toko'] ?>" class="btn-action btn-delete" onclick="return confirm('Apakah Anda yakin ingin menghapus toko ini beserta relasi kontak dan menunya?')">❌ Hapus</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</body>
</html>