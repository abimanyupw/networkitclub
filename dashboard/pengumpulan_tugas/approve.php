<?php
// dashboard/submissions/approve.php
session_start();
require_once '../../includes/inc_koneksi.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'developer', 'teknisi'])) {
    $_SESSION['error_message'] = "Akses ditolak.";
    header("Location: ../../login.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "ID tidak valid.";
    header("Location: index.php");
    exit();
}

$id = (int) $_GET['id'];
$cek = mysqli_query($koneksi, "SELECT id FROM assignment_submissions WHERE id = $id");
if (mysqli_num_rows($cek) === 0) {
    $_SESSION['error_message'] = "Data tidak ditemukan.";
    header("Location: index.php");
    exit();
}

mysqli_query($koneksi, "UPDATE assignment_submissions SET status = 'approved' WHERE id = $id");
$_SESSION['success_message'] = "Tugas berhasil disetujui.";
header("Location: index.php");
exit();
