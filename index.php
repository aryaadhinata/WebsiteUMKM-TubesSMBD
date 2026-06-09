<?php
require_once 'config.php';

// Ambil data jenis toko untuk dropdown filter
$jenis_stmt = $pdo->query("SELECT * FROM jenis");
$list_jenis = $jenis_stmt->fetchAll();

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
    <title>Katalog Kuliner UMKM</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: #F4F4F4;
            color: #333;
            line-height: 1.6;
        }

        .container {
            width: 85%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px 0;
        }

        header {
            background-color: #667302;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
        }

        .logo {
            color: #D9D9D9;
            font-size: 24px;
            font-weight: 700;
            text-decoration: none;
        }

        .logo span {
            color: #A9BF04;
        }

        .nav-links {
            list-style: none;
            display: flex;
            gap: 25px;
            align-items: center;
        }

        .nav-links a {
            color: #D9D9D9;
            font-weight: 500;
            text-decoration: none;
        }

        .nav-links a:hover {
            color: #A9BF04;
        }

        .btn-admin {
            background-color: #400101;
            color: #fff !important;
            padding: 8px 16px;
            border-radius: 5px;
        }

        .search-box {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            margin: 30px 0;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .search-form {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr auto;
            gap: 15px;
        }

        .search-form input,
        .search-form select {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            outline: none;
        }

        .btn-search {
            background-color: #667302;
            color: #fff;
            border: none;
            padding: 0 25px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }

        .btn-search:hover {
            background-color: #400101;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        .product-card {
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e0e0e0;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: 0.3s;
        }

        .product-card:hover {
            transform: translateY(-5px);
        }

        .product-info {
            padding: 25px;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .badge-jenis {
            background: #E8F5E9;
            color: #2E7D32;
        }

        .badge-halal {
            background: #FFF3E0;
            color: #E65100;
        }

        .product-info h3 {
            color: #400101;
            font-size: 20px;
            margin-bottom: 10px;
        }

        .product-meta {
            font-size: 13px;
            color: #666;
            margin-bottom: 15px;
        }

        .btn-detail {
            display: block;
            text-align: center;
            background-color: #667302;
            color: #fff;
            padding: 12px;
            border-radius: 0 0 12px 12px;
            font-weight: 600;
            text-decoration: none;
        }

        .btn-detail:hover {
            background-color: #400101;
        }

        footer {
            background-color: #400101;
            color: #D9D9D9;
            text-align: center;
            padding: 20px 0;
            margin-top: 50px;
            font-size: 14px;
        }
    </style>
</head>

<body>

    <header>
        <div class="container navbar">
            <a href="index.php" class="logo">UMKM<span>Kuliner</span></a>
            <ul class="nav-links">
                <li><a href="index.php">Beranda</a></li>
                <li>
                    <?php if(isset($_SESSION['admin'])): ?>
                    <a href="dashboard.php" class="btn-admin">Dashboard Admin</a>
                    <?php else: ?>
                    <a href="login.php" class="btn-admin">Login Admin</a>
                    <?php collapse_all_sections: true; $_SESSION['admin'] = null; // helper placeholder ?>
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
        <p>&copy; 2026 Katalog Kuliner UMKM. Terhubung Langsung ke Database Anda.</p>
    </footer>

</body>

</html>