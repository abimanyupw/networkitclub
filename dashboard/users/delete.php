<?php
session_start();
// Hanya butuh koneksi, tidak perlu header/footer atau elemen tampilan lainnya
include '../../includes/inc_koneksi.php';

// --- Logika Otorisasi: Hanya Admin dan Developer yang Diizinkan ---
// Jika tidak login atau bukan admin/developer, redirect dengan pesan error
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'developer')) {
    $_SESSION['error_message'] = "Anda tidak memiliki akses untuk melakukan tindakan ini."; // Pesan lebih umum
    header('Location: ../../login.php'); // Sesuaikan path jika login.php tidak di root
    exit();
}

$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Bersihkan pesan sesi sebelumnya jika ada, agar tidak tercampur
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);

if ($user_id > 0) {
    // Cek apakah pengguna yang akan dihapus bukan pengguna yang sedang login
    if ($user_id == $_SESSION['user_id']) {
        $_SESSION['error_message'] = "Anda tidak bisa menghapus akun Anda sendiri!";
        header('Location: index.php');
        exit();
    }

    // Persiapkan query DELETE
    $sql = "DELETE FROM users WHERE id = ?";
    $stmt = $koneksi->prepare($sql);

    // Cek apakah prepare statement berhasil
    if ($stmt) {
        $stmt->bind_param("i", $user_id);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Pengguna berhasil dihapus.";
        } else {
            $_SESSION['error_message'] = "Gagal menghapus pengguna: " . $stmt->error;
        }
        $stmt->close(); // Tutup statement setelah digunakan
    } else {
        $_SESSION['error_message'] = "Terjadi kesalahan sistem saat menyiapkan penghapusan: " . $koneksi->error;
    }
} else {
    $_SESSION['error_message'] = "ID Pengguna tidak valid untuk dihapus. Operasi dibatalkan.";
}

$koneksi->close(); // Tutup koneksi database
header('Location: index.php'); // Selalu kembali ke daftar pengguna setelah operasi
exit();
?>