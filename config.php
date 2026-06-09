<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_umkm"; // Pastikan namanya sama dengan yang ada di phpMyAdmin Anda

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Koneksi database ke phpMyAdmin gagal: " . $e->getMessage());
}

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Fungsi bantu untuk format Rupiah
function formatRupiah($angka) {
    return "Rp " . number_format($angka, 0, ',', '.');
}
?>