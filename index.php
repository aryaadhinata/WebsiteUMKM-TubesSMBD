<?php
require_once 'config.php';

// Ambil data jenis toko untuk dropdown filter
$jenis_stmt = $pdo->query("SELECT * FROM jenis");
$list_jenis = $jenis_stmt->fetchAll();

// Ambil data jadwal operasional untuk dropdown filter baru
$jadwal_stmt = $pdo->query("SELECT * FROM jadwal");
$list_jadwal = $jadwal_stmt->fetchAll();

// Bangun query filter pencarian toko
$query = "SELECT t.*, j.nama_jenis, s.value_sertifikasi, jd.buka, jd.tutup 
            FROM toko t
            JOIN jenis j ON t.id_jenis = j.id_jenis
            JOIN sertifikasi_halal s ON t.id_sertifikasi = s.id_sertifikasi
            JOIN jadwal jd ON t.id_jadwal = jd.id_jadwal
            WHERE 1=1";

$params = [];

// Filter berdasarkan nama/keyword
if (!empty($_GET['keyword'])) {
    $query .= " AND t.nama_toko LIKE :keyword";
    $params['keyword'] = '%' . $_GET['keyword'] . '%';
}

// Filter berdasarkan Jenis Toko
if (!empty($_GET['id_jenis'])) {
    $query .= " AND t.id_jenis = :id_jenis";
    $params['id_jenis'] = $_GET['id_jenis'];
}

// Filter berdasarkan Jam Spesifik (Input Time)
if (!empty($_GET['jam_spesifik'])) {
    // Tambahkan detik agar formatnya sesuai dengan tipe TIME di database (HH:MM:00)
    $jam_pencarian = $_GET['jam_spesifik'] . ':00'; 
    
    // Logika ini menangani toko yang buka normal (pagi ke malam) 
    // maupun toko yang buka melewati tengah malam (malam ke pagi)
    $query .= " AND (
        (jd.buka <= jd.tutup AND :jam_pencarian BETWEEN jd.buka AND jd.tutup)
        OR 
        (jd.buka > jd.tutup AND (:jam_pencarian >= jd.buka OR :jam_pencarian <= jd.tutup))
    )";
    $params['jam_pencarian'] = $jam_pencarian;
}

// Filter berdasarkan rentang harga menu kelipatan 20.000
if (!empty($_GET['range_harga'])) {
    if ($_GET['range_harga'] == '0_20') {
        $query .= " AND EXISTS (SELECT 1 FROM menu WHERE id_toko = t.id_toko AND harga <= 20000)";
    } elseif ($_GET['range_harga'] == '20_40') {
        $query .= " AND EXISTS (SELECT 1 FROM menu WHERE id_toko = t.id_toko AND harga BETWEEN 20001 AND 40000)";
    } elseif ($_GET['range_harga'] == '40_60') {
        $query .= " AND EXISTS (SELECT 1 FROM menu WHERE id_toko = t.id_toko AND harga BETWEEN 40001 AND 60000)";
    } elseif ($_GET['range_harga'] == '60_80') {
        $query .= " AND EXISTS (SELECT 1 FROM menu WHERE id_toko = t.id_toko AND harga BETWEEN 60001 AND 80000)";
    } elseif ($_GET['range_harga'] == '80_100') {
        $query .= " AND EXISTS (SELECT 1 FROM menu WHERE id_toko = t.id_toko AND harga BETWEEN 80001 AND 100000)";
    } elseif ($_GET['range_harga'] == '100_above') {
        $query .= " AND EXISTS (SELECT 1 FROM menu WHERE id_toko = t.id_toko AND harga > 100000)";
    }
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$list_toko = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="css/logo.png" type="image/png">
    <title>Katalog Kuliner UMKM</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/index.css">
</head>

<body>

    <header>
        <div class="container navbar">
            <a href="index.php" class="logo">UMKM<span>TUBES</span></a>
            <ul class="nav-links">
                <li><a href="index.php">Beranda</a></li>
                <li>
                    <?php if(isset($_SESSION['admin'])): ?>
                    <a href="dashboard.php" class="btn-admin">Dashboard Admin</a>
                    <?php else: ?>
                    <a href="login.php" class="btn-admin">Login Admin</a>
                    <?php endif; ?>
                </li>
            </ul>
        </div>
    </header>

    <main class="container">
        <div class="search-box">
            <form method="GET" action="index.php" class="search-form">
                <input type="text" name="keyword" placeholder="Cari nama toko..."
                    value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>">

                <select name="id_jenis">
                    <option value="">Semua Jenis Kuliner</option>
                    <?php foreach($list_jenis as $j): ?>
                    <option value="<?= $j['id_jenis'] ?>"
                        <?= (($_GET['id_jenis'] ?? '') == $j['id_jenis']) ? 'selected' : '' ?>><?= $j['nama_jenis'] ?>
                    </option>
                    <?php endforeach; ?>
                </select>

                <input type="time" name="jam_spesifik" value="<?= htmlspecialchars($_GET['jam_spesifik'] ?? '') ?>"
                    title="Cari toko yang buka di jam ini">

                <select name="range_harga">
                    <option value="">Semua Range Harga</option>
                    <option value="0_20" <?= (($_GET['range_harga'] ?? '') == '0_20') ? 'selected' : '' ?>>Di bawah Rp
                        20.000</option>
                    <option value="20_40" <?= (($_GET['range_harga'] ?? '') == '20_40') ? 'selected' : '' ?>>Rp 20.000 -
                        Rp 40.000</option>
                    <option value="40_60" <?= (($_GET['range_harga'] ?? '') == '40_60') ? 'selected' : '' ?>>Rp 40.000 -
                        Rp 60.000</option>
                    <option value="60_80" <?= (($_GET['range_harga'] ?? '') == '60_80') ? 'selected' : '' ?>>Rp 60.000 -
                        Rp 80.000</option>
                    <option value="80_100" <?= (($_GET['range_harga'] ?? '') == '80_100') ? 'selected' : '' ?>>Rp 80.000
                        - Rp 100.000</option>
                    <option value="100_above" <?= (($_GET['range_harga'] ?? '') == '100_above') ? 'selected' : '' ?>>Di
                        atas Rp 100.000</option>
                </select>

                <button type="submit" class="btn-search">Filter</button>
            </form>
        </div>

        <h2 style="margin-bottom: 20px; color: #400101;">Daftar Toko Tersedia (<?= count($list_toko) ?>)</h2>
        <div class="product-grid">
            <?php if(count($list_toko) > 0): ?>
            <?php foreach($list_toko as $toko): ?>
            <div class="product-card">
                <div class="product-info">
                    <span class="badge badge-jenis"><?= $toko['nama_jenis'] ?></span>
                    <span class="badge badge-halal"><?= $toko['value_sertifikasi'] ?></span>
                    <h3><?= htmlspecialchars($toko['nama_toko']) ?></h3>
                    <div class="product-meta">
                        🕒 Jam Operasional:<br>
                        <strong><?= substr($toko['buka'], 0, 5) ?> - <?= substr($toko['tutup'], 0, 5) ?> WIB</strong>
                    </div>
                </div>
                <a href="detail.php?id=<?= $toko['id_toko'] ?>" class="btn-detail">Lihat Menu Lengkap & Detail</a>
            </div>
            <?php endforeach; ?>
            <?php else: ?>
            <p style="grid-column: 1/-1; text-align: center; color: #888; padding: 40px 0;">Tidak ada toko yang sesuai
                dengan filter pencarian.</p>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <p>&copy; 2026 Katalog Kuliner UMKM. Untuk TUBES SMBD Kelompok 2</p>
    </footer>

</body>

</html>