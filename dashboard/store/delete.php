<?php
// dashboard/store/delete.php
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

// Hapus dulu relasi rule badge
mysqli_query($koneksi, "DELETE FROM store_item_badge_rules WHERE store_item_id = $id");

// Hapus barang dari tabel utama
mysqli_query($koneksi, "DELETE FROM store_items WHERE id = $id");

header("Location: index.php");
exit();
