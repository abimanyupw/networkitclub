<?php
session_start();
include '../../includes/inc_koneksi.php'; // Sesuaikan path jika berbeda

// 1. Verifikasi hak akses
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'developer', 'teknisi'])) {
    $_SESSION['error_message'] = "Anda tidak memiliki akses untuk menghapus materi.";
    header('Location: ../../login.php');
    exit();
}

// 2. Menerima dan memvalidasi ID materi
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']); // Amankan ID dengan mengonversi ke integer

    // 3. Persiapkan dan jalankan query DELETE
    $sql = "DELETE FROM materials WHERE id = ?";
    $stmt = $koneksi->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("i", $id); // 'i' untuk integer
        if ($stmt->execute()) {
            // Periksa apakah ada baris yang terpengaruh (materi berhasil dihapus)
            if ($stmt->affected_rows > 0) {
                $_SESSION['success_message'] = "Materi berhasil dihapus.";
            } else {
                $_SESSION['error_message'] = "Materi dengan ID " . htmlspecialchars($id) . " tidak ditemukan atau sudah dihapus.";
            }
        } else {
            $_SESSION['error_message'] = "Gagal menghapus materi: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['error_message'] = "Error preparing statement: " . $koneksi->error;
    }
} else {
    $_SESSION['error_message'] = "ID materi tidak valid untuk dihapus.";
}

// Tutup koneksi database
$koneksi->close();

// 4. Redirect kembali ke halaman daftar materi
header('Location: index.php');
exit();
?>