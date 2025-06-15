<?php
// dashboard/categories/delete.php

session_start();

include '../../includes/inc_koneksi.php';

// Cek apakah user sudah login dan memiliki role admin/developer/teknisi
if (!isset($_SESSION['user_id']) || (!($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'developer' || $_SESSION['role'] === 'teknisi'))) {
    $_SESSION['error_message'] = "Anda tidak memiliki akses untuk menghapus kategori.";
    header('Location: index.php');
    exit();
}

$category_id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT);

if (!$category_id) {
    $_SESSION['error_message'] = "ID kategori tidak valid untuk penghapusan.";
    header('Location: index.php');
    exit();
}

// Mengambil nama kategori untuk pesan konfirmasi (opsional, bisa juga dihapus)
$category_name = "Tidak Diketahui";
$stmt_get_name = $koneksi->prepare("SELECT name FROM categories WHERE id = ?");
if ($stmt_get_name) {
    $stmt_get_name->bind_param("i", $category_id);
    $stmt_get_name->execute();
    $result_name = $stmt_get_name->get_result();
    if ($result_name->num_rows > 0) {
        $category_name = $result_name->fetch_assoc()['name'];
    }
    $stmt_get_name->close();
}


try {
    $stmt = $koneksi->prepare("DELETE FROM categories WHERE id = ?");
    if ($stmt === false) {
        throw new Exception("Gagal menyiapkan statement hapus: " . $koneksi->error);
    }
    $stmt->bind_param("i", $category_id);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Kategori '$category_name' berhasil dihapus.";
    } else {
        // Ini akan menangkap jika ada foreign key constraint (misalnya, materi yang masih terhubung)
        // Cek jika errornya karena foreign key constraint
        if ($koneksi->errno == 1451) { // MySQL error code for foreign key constraint fail
            $_SESSION['error_message'] = "Gagal menghapus kategori '$category_name'. Kategori ini masih digunakan oleh materi lain. Harap hapus atau ubah kategori materi terkait terlebih dahulu.";
        } else {
            $_SESSION['error_message'] = "Gagal menghapus kategori '$category_name': " . $stmt->error;
        }
    }
    $stmt->close();
} catch (Exception $e) {
    $_SESSION['error_message'] = "Terjadi kesalahan: " . $e->getMessage();
}

$koneksi->close();
header('Location: index.php');
exit();
?>