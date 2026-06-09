<?php
require_once 'config.php';
if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit; }

$id_toko = $_GET['id_toko'] ?? null;
if (!$id_toko) { header("Location: dashboard.php"); exit; }

// Ambil info nama toko
$toko_stmt = $pdo->prepare("SELECT nama_toko FROM toko WHERE id_toko = ?");
$toko_stmt->execute([$id_toko]);
$toko = $toko_stmt->fetch();

// Ambil opsi master kategori untuk tambah menu
$list_kategori = $pdo->query("SELECT * FROM kategori")->fetchAll();

// Penanganan tambah menu baru jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_menu'])) {
    $nama_menu = $_POST['nama_menu'];
    $harga = $_POST['harga'];
    $id_kategori = $_POST['id_kategori'];

    $ins = $pdo->prepare("INSERT INTO menu (id_toko, nama_menu, harga, id_kategori) VALUES (?, ?, ?, ?)");
    $ins->execute([$id_toko, $nama_menu, $harga, $id_kategori]);
    header("Location: menu_manage.php?id_toko=".$id_toko);
    exit;
}

// Penanganan aksi hapus menu tertentu
if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $id_menu = $_GET['id_menu'];
    $del = $pdo->prepare("DELETE FROM menu WHERE id_menu = ? AND id_toko = ?");
    $del->execute([$id_menu, $id_toko]);
    header("Location: menu_manage.php?id_toko=".$id_toko);
    exit;
}

// Ambil daftar seluruh menu toko ini saat ini
$menu_stmt = $pdo->prepare("SELECT m.*, k.nama_kategori FROM menu m JOIN kategori k ON m.id_kategori = k.id_kategori WHERE m.id_toko = ? ORDER BY m.id_menu DESC");
$menu_stmt->execute([$id_toko]);
$menus = $menu_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="css/logo.png" type="image/png">
    <title>Kelola Menu - <?= htmlspecialchars($toko['nama_toko']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; }
        body { background-color: #f4f6f9; padding: 40px; }
        .grid-manage { display: grid; grid-template-columns: 1fr 2fr; gap: 30px; max-width: 1100px; margin: 0 auto; }
        .card { background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); height: fit-content; }
        .card h3 { color: #400101; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; font-size: 14px; }
        .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; outline: none; }
        .btn-submit { background-color: #667302; color: #fff; border: none; padding: 10px 15px; border-radius: 6px; font-weight: 600; cursor: pointer; width: 100%; }
        
        .menu-table { width: 100%; border-collapse: collapse; }
        .menu-table th, .menu-table td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        .menu-table th { background-color: #f4f4f4; }
        .btn-del { color: #c62828; text-decoration: none; font-size: 13px; font-weight: bold; }
    </style>
</head>
<body>

    <div style="max-width: 1100px; margin: 0 auto 20px;">
        <a href="dashboard.php" style="color: #667302; text-decoration: none; font-weight: bold;">&larr; Kembali ke Dashboard Utama</a>
        <h1 style="color: #400101; margin-top: 10px;">Manajemen Daftar Menu: <?= htmlspecialchars($toko['nama_toko']) ?></h1>
    </div>

    <div class="grid-manage">
        <div class="card">
            <h3>✨ Tambah Item Menu</h3>
            <form method="POST" action="">
                <input type="hidden" name="add_menu" value="1">
                <div class="form-group">
                    <label>Nama Menu</label>
                    <input type="text" name="nama_menu" required placeholder="Contoh: Es Teh Manis Gula Aren">
                </div>
                <div class="form-group">
                    <label>Harga Jual (Rupiah)</label>
                    <input type="number" name="harga" required placeholder="Contoh: 15000">
                </div>
                <div class="form-group">
                    <label>Sifat Kategori</label>
                    <select name="id_kategori" required>
                        <?php foreach($list_kategori as $k): ?>
                            <option value="<?= $k['id_kategori'] ?>"><?= $k['nama_kategori'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn-submit">Masukkan ke Database</button>
            </form>
        </div>

        <div class="card">
            <h3>🍱 Menu Terdaftar saat ini (<?= count($menus) ?>)</h3>
            <?php if(count($menus) > 0): ?>
                <table class="menu-table">
                    <thead>
                        <tr>
                            <th>Nama Item</th>
                            <th>Kategori</th>
                            <th>Harga Satuan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($menus as $m): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($m['nama_menu']) ?></strong></td>
                                <td><span style="background: #E0F7FA; color: #006064; padding: 2px 6px; border-radius: 4px; font-size: 12px;"><?= $m['nama_kategori'] ?></span></td>
                                <td style="font-weight: bold; color: #400101;"><?= formatRupiah($m['harga']) ?></td>
                                <td>
                                    <a href="menu_manage.php?id_toko=<?= $id_toko ?>&action=delete&id_menu=<?= $m['id_menu'] ?>" class="btn-del" onclick="return confirm('Hapus menu ini?')">Hapus</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="color: #888; font-style: italic; text-align: center; padding: 30px 0;">Buku menu kosong. Silakan tambahkan melalui form di samping.</p>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>