<?php
session_start();
// Hanya butuh koneksi, tidak perlu header/footer atau elemen tampilan lainnya
include '../../includes/inc_koneksi.php'; // Pastikan path ini benar

// --- Logika Otorisasi: Hanya Admin dan Developer yang Diizinkan ---
// Jika tidak login atau bukan admin/developer, redirect dengan pesan error
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'developer')) {
    $_SESSION['error_message'] = "Anda tidak memiliki akses untuk melakukan tindakan ini.";
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

    // --- MULAI TRANSAKSI ---
    // Mengatur autocommit ke FALSE agar kita bisa mengontrol transaksi secara manual
    $koneksi->autocommit(FALSE);

    try {
        // 1. Hapus data terkait di tabel 'assignment_submissions' terlebih dahulu
        $sql_submissions = "DELETE FROM assignment_submissions WHERE user_id = ?";
        $stmt_submissions = $koneksi->prepare($sql_submissions);

        if (!$stmt_submissions) {
            throw new Exception("Gagal menyiapkan statement untuk submissions: " . $koneksi->error);
        }

        $stmt_submissions->bind_param("i", $user_id);
        if (!$stmt_submissions->execute()) {
            throw new Exception("Gagal menghapus submissions terkait: " . $stmt_submissions->error);
        }
        $stmt_submissions->close();

        // 2. Hapus pengguna dari tabel 'users'
        $sql_users = "DELETE FROM users WHERE id = ?";
        $stmt_users = $koneksi->prepare($sql_users);

        if (!$stmt_users) {
            throw new Exception("Gagal menyiapkan statement untuk users: " . $koneksi->error);
        }

        $stmt_users->bind_param("i", $user_id);
        if (!$stmt_users->execute()) {
            throw new Exception("Gagal menghapus pengguna: " . $stmt_users->error);
        }
        $stmt_users->close();

        // Jika semua berhasil, commit transaksi
        $koneksi->commit();
        $_SESSION['success_message'] = "Pengguna dan data terkait berhasil dihapus.";

    } catch (Exception $e) {
        // Jika ada kesalahan, rollback transaksi
        $koneksi->rollback();
        $_SESSION['error_message'] = "Gagal menghapus pengguna: " . $e->getMessage();
        // Anda bisa log $e->getMessage() ke file log untuk debugging lebih lanjut
    } finally {
        // Selalu kembalikan autocommit ke TRUE setelah transaksi selesai
        $koneksi->autocommit(TRUE);
    }

} else {
    $_SESSION['error_message'] = "ID Pengguna tidak valid untuk dihapus. Operasi dibatalkan.";
}

$koneksi->close(); // Tutup koneksi database
header('Location: index.php'); // Selalu kembali ke daftar pengguna setelah operasi
exit();
?>