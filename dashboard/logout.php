<?php
// logout.php
session_start();

// Pastikan koneksi database tersedia
require_once '../includes/inc_koneksi.php'; // Sesuaikan path jika perlu

// Ambil user_id dari sesi sebelum dihancurkan
$user_id_to_logout = $_SESSION['user_id'] ?? null;

// --- Bagian Pembersihan Sesi di Database (Hanya untuk logout manual) ---
if ($user_id_to_logout) {
    if ($koneksi) {
        $stmt = $koneksi->prepare("UPDATE users SET session_id = NULL WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $user_id_to_logout);
            $stmt->execute();
            $stmt->close();
        } else {
            error_log("Failed to prepare UPDATE statement for session_id on logout: " . $koneksi->error);
        }
        $koneksi->close();
    } else {
        error_log("Database connection not available in logout.php for session_id update.");
    }
}

// --- Bagian Penghancuran Session PHP ---
$_SESSION = array();
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

// Redirect ke halaman login tanpa parameter msg (karena ini logout manual)
header("Location: ../login.php"); // <-- Tanpa parameter msg
exit();
?>