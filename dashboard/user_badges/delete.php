<?php
// dashboard/user_badges/delete.php
session_start();
require_once '../../includes/inc_koneksi.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'developer', 'teknisi'])) {
    $_SESSION['error_message'] = "Anda tidak memiliki akses.";
    header("Location: ../../login.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "ID tidak valid.";
    header("Location: index.php");
    exit();
}

$id = intval($_GET['id']);

// Cek apakah data ada
$cek = mysqli_query($koneksi, "SELECT * FROM user_badges WHERE id = $id");
if (mysqli_num_rows($cek) === 0) {
    $_SESSION['error_message'] = "Data tidak ditemukan.";
    header("Location: index.php");
    exit();
}

// Hapus data
mysqli_query($koneksi, "DELETE FROM user_badges WHERE id = $id");

// Redirect dengan pesan
$_SESSION['success_message'] = "Data badge berhasil dihapus.";
header("Location: index.php");
exit();
