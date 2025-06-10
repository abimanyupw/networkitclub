<?php
// check_session.php
session_start();

// Waktu idle untuk auto-logout (harus sama dengan di login.php atau diatur di file konfigurasi terpisah)
$idle_timeout = 5 * 60; // 5 menit dalam detik

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $idle_timeout)) {
    // Sesi telah idle melewati batas waktu
    // Hancurkan sesi
    session_unset();
    session_destroy();

    // Hapus session_id dari database jika memungkinkan (perlu koneksi database)
    // Ini membutuhkan inc_koneksi.php dan logika UPDATE users SET session_id = NULL
    // Untuk menjaga check_session.php minimal, kita bisa biarkan logout.php yang menghandle ini.
    // ATAU: Sertakan koneksi database di sini jika Anda ingin ini terjadi saat idle
    /*
    require_once '../includes/inc_koneksi.php'; // Sesuaikan path jika perlu
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        if ($koneksi) {
            $stmt = $koneksi->prepare("UPDATE users SET session_id = NULL WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $stmt->close();
            }
            $koneksi->close();
        }
    }
    */

    // LANGSUNG REDIRECT ke halaman login dengan pesan
    header("Location: login.php?msg=logged_out");
    exit(); // Hentikan eksekusi skrip setelah redirect
} else {
    // Sesi masih aktif, update waktu aktivitas terakhir
    $_SESSION['last_activity'] = time();
    // Tidak perlu echo json_encode() jika tidak ada JS yang mendengarkan
    // Jika masih ada JS yang mendengarkan 'active' status, biarkan baris ini
    // Tapi jika Anda ingin check_session.php hanya redirect saat idle, hapus ini
    echo json_encode(['status' => 'active', 'message' => 'Sesi aktif.']);
}
exit();
?>