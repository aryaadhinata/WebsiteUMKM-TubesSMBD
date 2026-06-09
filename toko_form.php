<?php
require_once 'config.php';
if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit; }

$id = $_GET['id'] ?? null;
$toko = null;

// Jika mode Edit, ambil data toko lama
if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM toko WHERE id_toko = ?");
    $stmt->execute([$id]);
    $toko = $stmt->fetch();
}

// Ambil opsi master untuk dropdown form
$list_sertifikasi = $pdo->query("SELECT * FROM sertifikasi_halal")->fetchAll();
$list_jenis = $pdo->query("SELECT * FROM jenis")->fetchAll();
$list_jadwal = $pdo->query("SELECT * FROM jadwal")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_toko = $_POST['nama_toko'];
    $id_sertifikasi = $_POST['id_sertifikasi'];
    $id_jenis = $_POST['id_jenis'];
    $id_jadwal = $_POST['id_jadwal'];

    if ($id) {
        // SQL Edit Data
        $update_stmt = $pdo->prepare("UPDATE toko SET nama_toko = ?, id_sertifikasi = ?, id_jenis = ?, id_jadwal = ? WHERE id_toko = ?");
        $update_stmt->execute([$nama_toko, $id_sertifikasi, $id_jenis, $id_jadwal, $id]);
    } else {
        // SQL Tambah Baru
        $insert_stmt = $pdo->prepare("INSERT INTO toko (nama_toko, id_sertifikasi, id_jenis, id_jadwal) VALUES (?, ?, ?, ?)");
        $insert_stmt->execute([$nama_toko, $id_sertifikasi, $id_jenis, $id_jadwal]);
    }
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $id ? 'Edit' : 'Tambah' ?> Toko UMKM</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; }
        body { background-color: #f4f6f9; padding: 40px; }
        .form-card { background: #fff; max-width: 600px; margin: 0 auto; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .form-card h2 { color: #400101; margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; font-size: 14px; }
        .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; outline: none; font-size: 14px; }
        .btn-save { background-color: #667302; color: #fff; border: none; padding: 10px 20px; border-radius: 6px; font-weight: 600; cursor: pointer; }
        .btn-cancel { background-color: #aaa; color: #fff; text-decoration: none; padding: 10px 20px; border-radius: 6px; font-weight: 600; margin-left: 10px; font-size: 14px; display: inline-block; }
    </style>
</head>
<body>

    <div class="form-card">
        <h2><?= $id ? '✏️ Edit Data' : '✨ Tambah Baru' ?> Toko</h2>
        <form method="POST" action="">
            <div class="form-group">
                <label>Nama Toko</label>
                <input type="text" name="nama_toko" value="<?= htmlspecialchars($toko['nama_toko'] ?? '') ?>" required placeholder="Masukkan nama toko">
            </div>

            <div class="form-group">
                <label>Sertifikasi Halal</label>
                <select name="id_sertifikasi" required>
                    <?php foreach($list_sertifikasi as $s): ?>
                        <option value="<?= $s['id_sertifikasi'] ?>" <?= (($toko['id_sertifikasi'] ?? '') == $s['id_sertifikasi']) ? 'selected' : '' ?>><?= $s['value_sertifikasi'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Jenis Kuliner</label>
                <select name="id_jenis" required>
                    <?php foreach($list_jenis as $j): ?>
                        <option value="<?= $j['id_jenis'] ?>" <?= (($toko['id_jenis'] ?? '') == $j['id_jenis']) ? 'selected' : '' ?>><?= $j['nama_jenis'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Jadwal Operasional</label>
                <select name="id_jadwal" required>
                    <?php foreach($list_jadwal as $jd): ?>
                        <option value="<?= $jd['id_jadwal'] ?>" <?= (($toko['id_jadwal'] ?? '') == $jd['id_jadwal']) ? 'selected' : '' ?>>
                            🕒 Buka: <?= substr($jd['buka'],0,5) ?> - Tutup: <?= substr($jd['tutup'],0,5) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn-save">Simpan Data</button>
            <a href="dashboard.php" class="btn-cancel">Batal</a>
        </form>
    </div>

</body>
</html>