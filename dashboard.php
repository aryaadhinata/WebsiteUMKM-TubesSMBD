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
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; }
        body { background-color: #f4f6f9; display: flex; min-height: 100vh; }
        
        .sidebar { width: 250px; background: #400101; color: #fff; padding: 20px; display: flex; flex-direction: column; justify-content: space-between; }
        .sidebar h2 { font-size: 20px; text-align: center; margin-bottom: 30px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 15px; }
        .sidebar a { color: #D9D9D9; text-decoration: none; padding: 12px; display: block; border-radius: 6px; font-weight: 500; margin-bottom: 10px; }
        .sidebar a:hover, .sidebar a.active { background: #667302; color: #fff; }
        .btn-logout { background: #d32f2f !important; text-align: center; margin-top: auto; }

        .content { flex: 1; padding: 40px; }
        .header-content { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .header-content h1 { color: #333; font-size: 28px; }
        
        .btn-add { background-color: #667302; color: #fff; text-decoration: none; padding: 10px 20px; border-radius: 6px; font-weight: 600; font-size: 14px; }
        .btn-add:hover { background-color: #400101; }

        .dash-table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .dash-table th, .dash-table td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        .dash-table th { background-color: #eee; color: #333; font-weight: 600; }
        
        .btn-action { text-decoration: none; padding: 5px 12px; border-radius: 4px; font-size: 12px; font-weight: 600; display: inline-block; margin-right: 5px; }
        .btn-edit { background: #FFF3E0; color: #E65100; }
        .btn-delete { background: #FFEBEE; color: #C62828; }
        .btn-menu { background: #E8F5E9; color: #2E7D32; }
    </style>
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