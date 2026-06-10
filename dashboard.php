<?php
require_once 'config.php';
if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit; }

// --- QUERY UTAMA: DAFTAR TOKO (Eksisting) ---
$stmt = $pdo->query("SELECT t.id_toko, t.nama_toko, j.nama_jenis, s.value_sertifikasi, 
                    (SELECT COUNT(*) FROM menu WHERE id_toko = t.id_toko) as total_menu
                    FROM toko t
                    JOIN jenis j ON t.id_jenis = j.id_jenis
                    JOIN sertifikasi_halal s ON t.id_sertifikasi = s.id_sertifikasi
                    ORDER BY t.id_toko DESC");
$list_toko = $stmt->fetchAll();


// ==========================================
// --- STATISTIK CONTROL CENTER (BARU) ---
// ==========================================

// 1. STATISTIK JAM TERTENTU (Dinamis berdasarkan input waktu admin)
$jam_cek = $_GET['jam_cek'] ?? date('H:i'); // default jam sekarang jika belum diinput
$jam_pencarian = $jam_cek . ':00';

$query_jam = "SELECT t.nama_toko, jd.buka, jd.tutup,
                    (SELECT GROUP_CONCAT(m.nama_mitra SEPARATOR ', ') FROM rel_mitra rm JOIN mitra m ON rm.id_mitra = m.id_mitra WHERE rm.id_toko = t.id_toko) as mitra_links
            FROM toko t
            JOIN jadwal jd ON t.id_jadwal = jd.id_jadwal
            WHERE (
                (jd.buka <= jd.tutup AND :jam_cek1 BETWEEN jd.buka AND jd.tutup)
                OR 
                (jd.buka > jd.tutup AND (:jam_cek2 >= jd.buka OR :jam_cek3 <= jd.tutup))
            )";
$stmt_jam = $pdo->prepare($query_jam);
$stmt_jam->execute(['jam_cek1' => $jam_pencarian, 'jam_cek2' => $jam_pencarian, 'jam_cek3' => $jam_pencarian]);
$umkm_buka = $stmt_jam->fetchAll();


// 2. STATISTIK RANGE HARGA MENU (Menghitung jumlah UMKM unik per range harga)
$ranges = [
    '0_20'      => ['label' => 'Di bawah Rp 20.000', 'cond' => 'harga <= 20000'],
    '20_40'     => ['label' => 'Rp 20.000 - Rp 40.000', 'cond' => 'harga BETWEEN 20001 AND 40000'],
    '40_60'     => ['label' => 'Rp 40.000 - Rp 60.000', 'cond' => 'harga BETWEEN 40001 AND 60000'],
    '60_80'     => ['label' => 'Rp 60.000 - Rp 80.000', 'cond' => 'harga BETWEEN 60001 AND 80000'],
    '80_100'    => ['label' => 'Rp 80.000 - Rp 100.000', 'cond' => 'harga BETWEEN 80001 AND 100000'],
    '100_above' => ['label' => 'Di atas Rp 100.000', 'cond' => 'harga > 100000'],
];
$stat_harga = [];
foreach ($ranges as $key => $r) {
    $q = "SELECT COUNT(DISTINCT id_toko) FROM menu WHERE " . $r['cond'];
    $stat_harga[$r['label']] = $pdo->query($q)->fetchColumn();
}


// 3. STATISTIK MITRA PENGIRIMAN (Mencari mitra terbanyak)
$mitra_counts = $pdo->query("SELECT m.nama_mitra, COUNT(rm.id_toko) as jumlah 
                            FROM mitra m 
                            LEFT JOIN rel_mitra rm ON m.id_mitra = rm.id_mitra 
                            GROUP BY m.id_mitra 
                            ORDER BY jumlah DESC")->fetchAll();
$mitra_terbanyak = (!empty($mitra_counts) && $mitra_counts[0]['jumlah'] > 0) ? $mitra_counts[0]['nama_mitra'] . " (" . $mitra_counts[0]['jumlah'] . " UMKM)" : "Belum ada mitra terikat";


// 4. STATISTIK METODE PEMBAYARAN (Mencari metode selain cash)
$payment_counts = $pdo->query("SELECT mp.nama_metode, COUNT(rmp.id_toko) as jumlah 
                            FROM metode_pembayaran mp 
                            LEFT JOIN rel_metode_pembayaran rmp ON mp.id_metode = rmp.id_metode 
                            GROUP BY mp.id_metode")->fetchAll();
$non_cash_list = [];
foreach ($payment_counts as $p) {
    if (strtolower($p['nama_metode']) !== 'cash' && $p['jumlah'] > 0) {
        $non_cash_list[] = $p['nama_metode'] . " (" . $p['jumlah'] . ")";
    }
}
$non_cash_info = !empty($non_cash_list) ? implode(', ', $non_cash_list) : "Tidak ada/belum diatur";


// 5. STATISTIK SERTIFIKASI HALAL
$halal_counts = $pdo->query("SELECT s.value_sertifikasi, COUNT(t.id_toko) as jumlah 
                            FROM sertifikasi_halal s 
                            LEFT JOIN toko t ON s.id_sertifikasi = t.id_sertifikasi 
                            GROUP BY s.id_sertifikasi")->fetchAll();


// 6. STATISTIK KATEGORI RASA (Diambil dari master kategori menu)
$rasa_counts = $pdo->query("SELECT k.nama_kategori, COUNT(DISTINCT m.id_toko) as jumlah_umkm 
                            FROM kategori k 
                            LEFT JOIN menu m ON k.id_kategori = m.id_kategori 
                            GROUP BY k.id_kategori")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard & Control Center</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css">
    <style>    </style>
</head>

<body>

    <div class="sidebar">
        <div>
            <h2>UMKM ADMIN</h2>
            <a href="dashboard.php" class="active">📊 Control Center</a>
            <a href="index.php">🌐 Lihat Web Publik</a>
        </div>
        <a href="login.php" class="btn-logout" onclick="return confirm('Yakin ingin keluar?')">Keluar Log Out</a>
    </div>

    <div class="content">

        <div class="header-content">
            <h1>Dashboard & Control Center</h1>
            <a href="toko_form.php" class="btn-add">➕ Tambah Toko UMKM Baru</a>
        </div>

        <div class="control-center">
            <h2>🎯 UMKM Control Center (Statistik Real-time)</h2>

            <div class="stat-grid">

                <div class="stat-card" style="grid-column: 1 / -1;">
                    <h4>1. Status UMKM Buka Pada Jam Tertentu</h4>
                        <form method="GET" action=""
                            style="display:flex; gap:10px; align-items:center; margin-bottom:15px;">
                            <span style="font-size:14px;">Masukkan Jam Uji:</span>
                            <input type="time" name="jam_cek" value="<?= htmlspecialchars($jam_cek) ?>"
                                style="padding:4px 8px; border-radius:4px; border:1px solid #ccc;">
                            <button type="submit" class="btn-check">Cek Jumlah</button>
                        </form>
                        <p style="margin-bottom: 8px;">Total UMKM Buka pada pukul
                            <strong><?= htmlspecialchars($jam_cek) ?> WIB</strong>: <span class="badge-count"
                                style="font-size:14px; padding:4px 10px;"><?= count($umkm_buka) ?> Toko</span></p>

                        <?php if(!empty($umkm_buka)): ?>
                        <details style="margin-top: 15px; cursor: pointer;">
                            <summary style="font-size: 13px; font-weight: 600; color: #400101; margin-bottom: 8px;">
                                ▶ Klik untuk melihat/menyembunyikan daftar nama toko
                            </summary>
                            <table
                                style="width:100%; font-size:13px; border-collapse:collapse; margin-top:10px; background:#fff; border:1px solid #ddd; cursor: default;">
                                <tr style="background:#eee; text-align:left;">
                                    <th style="padding:8px;">Nama Toko</th>
                                    <th style="padding:8px;">Range Operasional</th>
                                    <th style="padding:8px;">Mitra Aktif</th>
                                </tr>
                                <?php foreach($umkm_buka as $ub): ?>
                                <tr style="border-bottom:1px solid #eee;">
                                    <td style="padding:8px;"><strong><?= htmlspecialchars($ub['nama_toko']) ?></strong>
                                    </td>
                                    <td style="padding:8px; color:#c62828;">🕒 <?= substr($ub['buka'],0,5) ?> -
                                        <?= substr($ub['tutup'],0,5) ?> WIB</td>
                                    <td style="padding:8px; color: #2e7d32; font-style:italic;">
                                        <?= $ub['mitra_links'] ?: 'Hanya Offline (Tidak ada)' ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </table>
                        </details>
                        <?php endif; ?>
                </div>

                <div class="stat-card">
                    <h4>2. Sebaran UMKM Berdasarkan Range Harga Menu</h4>
                    <ul>
                        <?php foreach($stat_harga as $label => $count): ?>
                        <li style="margin-bottom:4px;"><?= $label ?>: <strong><?= $count ?> UMKM</strong></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="stat-card">
                    <h4>3. Integrasi Mitra Eksternal</h4>
                    <p style="margin-bottom:10px;">🏆 Mitra Paling Banyak Digunakan:<br> <strong
                            style="color:#2E7D32; font-size:15px;"><?= $mitra_terbanyak ?></strong></p>
                    <span style="font-size:12px; font-weight:600; color:#666;">Detail per Mitra:</span>
                    <ul style="margin-top:5px;">
                        <?php foreach($mitra_counts as $mc): ?>
                        <li><?= htmlspecialchars($mc['nama_mitra']) ?>: <?= $mc['jumlah'] ?> UMKM</li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="stat-card">
                    <h4>4. Metode Pembayaran Terintegrasi</h4>
                    <span style="font-size:12px; font-weight:600; color:#666;">Metode Selain Cash:</span>
                    <p
                        style="background: #e8f5e9; padding: 8px; border-radius: 4px; margin: 5px 0; font-weight: bold; color: #1b5e20; font-size:13px;">
                        <?= $non_cash_info ?>
                    </p>
                    <span style="font-size:11px; color:#777;">*Menghitung berapa banyak merchant UMKM yang mengaktifkan
                        opsi tersebut.</span>
                </div>

                <div class="stat-card">
                    <h4>5. Status Legalitas Kepatuhan Halal</h4>
                    <ul>
                        <?php foreach($halal_counts as $hc): ?>
                        <li style="margin-bottom:6px;"><?= htmlspecialchars($hc['value_sertifikasi']) ?>: <span
                                class="badge-count"><?= $hc['jumlah'] ?> Toko</span></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="stat-card">
                    <h4>6. Sebaran UMKM Berdasarkan Karakter Rasa Menu</h4>
                    <ul>
                        <?php foreach($rasa_counts as $rc): ?>
                        <li style="margin-bottom:4px;">Menu Bernuansa
                            <strong><?= htmlspecialchars($rc['nama_kategori']) ?></strong>: Tersedia di
                            <?= $rc['jumlah_umkm'] ?> UMKM</li>
                        <?php endforeach; ?>
                    </ul>
                </div>

            </div>
        </div>


        <h2 style="margin-bottom: 15px; color:#400101;">📋 Seluruh Entitas Toko Terdaftar (<?= count($list_toko) ?>)
        </h2>
        <table class="dash-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Toko UMKM</th>
                    <th>Jenis Kuliner</th>
                    <th>Sertifikasi</th>
                    <th>Jumlah Menu</th>
                    <th style="text-align: center;">Aksi Manajemen</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($list_toko) > 0): ?>
                <?php foreach($list_toko as $row): ?>
                <tr>
                    <td><?= $row['id_toko'] ?></td>
                    <td><strong><?= htmlspecialchars($row['nama_toko']) ?></strong></td>
                    <td><?= $row['nama_jenis'] ?></td>
                    <td><?= $row['value_sertifikasi'] ?></td>
                    <td><span
                            style="background: #eee; padding: 2px 8px; border-radius: 10px; font-size: 12px; font-weight: bold;"><?= $row['total_menu'] ?>
                            Item</span></td>
                    <td style="text-align: center;">
                        <a href="menu_manage.php?id_toko=<?= $row['id_toko'] ?>" class="btn-action btn-menu">🍱 Kelola
                            Menu</a>
                        <a href="toko_form.php?id=<?= $row['id_toko'] ?>" class="btn-action btn-edit">✏️ Edit</a>
                        <a href="toko_delete.php?id=<?= $row['id_toko'] ?>" class="btn-action btn-delete"
                            onclick="return confirm('Apakah Anda yakin ingin menghapus toko ini beserta relasi kontak, mitra, pembayaran, dan menunya?')">❌
                            Hapus</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align:center; color:#888; padding:30px;">Belum ada entitas toko kuliner
                        yang dimasukkan ke database.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>

</html>