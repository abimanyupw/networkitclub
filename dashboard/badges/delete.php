<?php
session_start();
require_once '../../includes/inc_koneksi.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'developer', 'teknisi'])) {
    header("Location: ../../login.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = (int)$_GET['id'];

// Cek apakah badge masih digunakan oleh siswa
$check = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM user_badges WHERE badge_id = $id");
$data = mysqli_fetch_assoc($check);

if ($data['total'] > 0) {
    $_SESSION['error_message'] = "Tidak dapat menghapus badge karena masih digunakan oleh $data[total] pengguna.";
    header("Location: index.php");
    exit();
}

// Hapus badge jika tidak digunakan
mysqli_query($koneksi, "DELETE FROM badges WHERE id = $id");
$_SESSION['success_message'] = "Badge berhasil dihapus.";
header("Location: index.php");
exit();
