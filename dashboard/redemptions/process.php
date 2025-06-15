<?php
// dashboard/redemptions/process.php
session_start();
require_once '../../includes/inc_koneksi.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'developer', 'teknisi'])) {
    header("Location: ../../login.php");
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$action = $_GET['action'] ?? '';

if (!$id || !in_array($action, ['approve', 'reject'])) {
    header("Location: index.php?error=invalid");
    exit();
}

// Ambil data penukaran
$redeem = mysqli_query($koneksi, "SELECT * FROM redemptions WHERE id = $id AND status = 'pending'");
if (mysqli_num_rows($redeem) === 0) {
    header("Location: index.php?error=notfound");
    exit();
}

$data = mysqli_fetch_assoc($redeem);
$user_id = $data['user_id'];
$item_id = $data['item_id'];

if ($action === 'approve') {
    // Kurangi stok
    mysqli_query($koneksi, "UPDATE store_items SET stock = stock - 1 WHERE id = $item_id");

    // Kurangi badge sesuai syarat
    $rules = mysqli_query($koneksi, "SELECT badge_id, badge_count_required FROM store_item_badge_rules WHERE store_item_id = $item_id");
    while ($rule = mysqli_fetch_assoc($rules)) {
        $badge_id = $rule['badge_id'];
        $required = $rule['badge_count_required'];

        // Hapus sejumlah badge milik siswa
        $delete = mysqli_query($koneksi, "
            DELETE FROM user_badges 
            WHERE user_id = $user_id AND badge_id = $badge_id 
            LIMIT $required
        ");
    }

    // Set status approved
    mysqli_query($koneksi, "UPDATE redemptions SET status = 'approved' WHERE id = $id");
} elseif ($action === 'reject') {
    mysqli_query($koneksi, "UPDATE redemptions SET status = 'rejected' WHERE id = $id");
}

header("Location: index.php");
exit();
