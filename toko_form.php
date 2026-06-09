<?php
require_once 'config.php';
if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit; }

$id = $_GET['id'] ?? null;
$toko = null;
$existing_kontak = [];
$existing_metode = [];
$existing_mitra = [];

// Ambil opsi master untuk dropdown/checkbox form
$list_sertifikasi = $pdo->query("SELECT * FROM sertifikasi_halal")->fetchAll();
$list_jenis = $pdo->query("SELECT * FROM jenis")->fetchAll();
$list_jadwal = $pdo->query("SELECT * FROM jadwal")->fetchAll();
$all_metode = $pdo->query("SELECT * FROM metode_pembayaran")->fetchAll();
$all_mitra = $pdo->query("SELECT * FROM mitra")->fetchAll();

// Jika mode Edit, ambil semua data lama (termasuk relasinya)
if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM toko WHERE id_toko = ?");
    $stmt->execute([$id]);
    $toko = $stmt->fetch();

    if ($toko) {
        // Ambil Kontak Lama
        $stmt_k = $pdo->prepare("SELECT * FROM kontak WHERE id_toko = ?");
        $stmt_k->execute([$id]);
        foreach($stmt_k->fetchAll() as $k) {
            $existing_kontak[$k['tipe_kontak']] = $k['value_kontak'];
        }

        // Ambil Metode Pembayaran Terpilih
        $stmt_m = $pdo->prepare("SELECT id_metode FROM rel_metode_pembayaran WHERE id_toko = ?");
        $stmt_m->execute([$id]);
        $existing_metode = $stmt_m->fetchAll(PDO::FETCH_COLUMN);

        // Ambil Mitra Pengiriman Terpilih
        $stmt_mt = $pdo->prepare("SELECT id_mitra FROM rel_mitra WHERE id_toko = ?");
        $stmt_mt->execute([$id]);
        $existing_mitra = $stmt_mt->fetchAll(PDO::FETCH_COLUMN);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_toko = $_POST['nama_toko'];
    $id_sertifikasi = $_POST['id_sertifikasi'];
    $id_jenis = $_POST['id_jenis'];
    $id_jadwal = $_POST['id_jadwal'];
    
    $kontak_post = $_POST['kontak'] ?? []; // Array asosiatif ['whatsapp' => '...', 'instagram' => '...']
    $metode_post = $_POST['metode'] ?? []; // Array ID metode terpilih
    $mitra_post = $_POST['mitra'] ?? [];   // Array ID mitra terpilih

    // Jalankan Database Transaction agar eksekusi data relasional aman terjamin
    $pdo->beginTransaction();
    try {
        if ($id) {
            // 1. Update data dasar Toko
            $update_stmt = $pdo->prepare("UPDATE toko SET nama_toko = ?, id_sertifikasi = ?, id_jenis = ?, id_jadwal = ? WHERE id_toko = ?");
            $update_stmt->execute([$nama_toko, $id_sertifikasi, $id_jenis, $id_jadwal, $id]);
            $id_toko = $id;

            // Hapus relasi lama agar tidak duplikat saat ditimpa data baru
            $pdo->prepare("DELETE FROM kontak WHERE id_toko = ?")->execute([$id_toko]);
            $pdo->prepare("DELETE FROM rel_metode_pembayaran WHERE id_toko = ?")->execute([$id_toko]);
            $pdo->prepare("DELETE FROM rel_mitra WHERE id_toko = ?")->execute([$id_toko]);
        } else {
            // 1. Tambah Toko Baru
            $insert_stmt = $pdo->prepare("INSERT INTO toko (nama_toko, id_sertifikasi, id_jenis, id_jadwal) VALUES (?, ?, ?, ?)");
            $insert_stmt->execute([$nama_toko, $id_sertifikasi, $id_jenis, $id_jadwal]);
            $id_toko = $pdo->lastInsertId();
        }

        // 2. Simpan Kontak Baru/Update jika diisi
        $ins_kontak = $pdo->prepare("INSERT INTO kontak (id_toko, tipe_kontak, value_kontak) VALUES (?, ?, ?)");
        foreach ($kontak_post as $tipe => $value) {
            if (!empty(trim($value))) {
                $ins_kontak->execute([$id_toko, $tipe, trim($value)]);
            }
        }

        // 3. Simpan Relasi Metode Pembayaran
        if (!empty($metode_post)) {
            $ins_metode = $pdo->prepare("INSERT INTO rel_metode_pembayaran (id_toko, id_metode) VALUES (?, ?)");
            foreach ($metode_post as $id_metode) {
                $ins_metode->execute([$id_toko, $id_metode]);
            }
        }

        // 4. Simpan Relasi Mitra Pengiriman
        if (!empty($mitra_post)) {
            $ins_mitra = $pdo->prepare("INSERT INTO rel_mitra (id_toko, id_mitra) VALUES (?, ?)");
            foreach ($mitra_post as $id_mitra) {
                $ins_mitra->execute([$id_toko, $id_mitra]);
            }
        }

        $pdo->commit();
        header("Location: dashboard.php");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Terjadi kegagalan sistem simpan: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $id ? 'Edit' : 'Tambah' ?> Toko UMKM</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/toko_form.css">
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

            <hr style="margin: 25px 0; border: 0; border-top: 1px dashed #ccc;">

            <div class="form-group">
                <label>Kontak Resmi (WhatsApp)</label>
                <input type="text" name="kontak[whatsapp]" value="<?= htmlspecialchars($existing_kontak['whatsapp'] ?? '') ?>" placeholder="Contoh: 08123456789">
            </div>
            <div class="form-group">
                <label>Kontak Resmi (Instagram)</label>
                <input type="text" name="kontak[instagram]" value="<?= htmlspecialchars($existing_kontak['instagram'] ?? '') ?>" placeholder="Contoh: @tokokuliner">
            </div>

            <div class="form-group">
                <label>Metode Pembayaran Tersedia</label>
                <div class="checkbox-group">
                    <?php foreach($all_metode as $m): ?>
                        <label class="checkbox-label">
                            <input type="checkbox" name="metode[]" value="<?= $m['id_metode'] ?>" <?= in_array($m['id_metode'], $existing_metode) ? 'checked' : '' ?>> 
                            <?= $m['nama_metode'] ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-group">
                <label>Mitra Pengiriman Online</label>
                <div class="checkbox-group">
                    <?php foreach($all_mitra as $mt): ?>
                        <label class="checkbox-label">
                            <input type="checkbox" name="mitra[]" value="<?= $mt['id_mitra'] ?>" <?= in_array($mt['id_mitra'], $existing_mitra) ? 'checked' : '' ?>> 
                            <?= $mt['nama_mitra'] ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <button type="submit" class="btn-save">Simpan Data</button>
            <a href="dashboard.php" class="btn-cancel">Batal</a>
        </form>
    </div>

</body>
</html>