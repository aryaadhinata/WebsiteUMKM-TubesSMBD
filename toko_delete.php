<?php
require_once 'config.php';
if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit; }

$id = $_GET['id'] ?? null;

if ($id) {
    try {
        $pdo->beginTransaction();

        // 1. Bersihkan Kontak Toko terkait
        $p1 = $pdo->prepare("DELETE FROM kontak WHERE id_toko = ?");
        $p1->execute([$id]);

        // 2. Bersihkan Relasi Metode Pembayaran
        $p2 = $pdo->prepare("DELETE FROM rel_metode_pembayaran WHERE id_toko = ?");
        $p2->execute([$id]);

        // 3. Bersihkan Relasi Mitra Online
        $p3 = $pdo->prepare("DELETE FROM rel_mitra WHERE id_toko = ?");
        $p3->execute([$id]);

        // 4. Bersihkan Buku Menu Toko
        $p4 = $pdo->prepare("DELETE FROM menu WHERE id_toko = ?");
        $p4->execute([$id]);

        // 5. Jalankan Stored Procedure hapus_toko sesuai berkas SQL Anda
        $p5 = $pdo->prepare("CALL hapus_toko(?)");
        $p5->execute([$id]);

        $pdo->commit();
        header("Location: dashboard.php");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Gagal menghapus data toko secara aman: " . $e->getMessage());
    }
} else {
    header("Location: dashboard.php");
    exit;
}
?>