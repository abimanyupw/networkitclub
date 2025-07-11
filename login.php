<?php
// login.php
session_start(); // Mulai session untuk menyimpan data pengguna setelah login

// Sertakan file koneksi database. Pastikan path-nya benar.
include 'includes/inc_koneksi.php';

$error_message = '';   // Variabel untuk menyimpan pesan error yang akan ditampilkan di HTML
$success_message = ''; // Variabel untuk pesan sukses atau informasi

// --- PENANGANAN PESAN DARI URL (Misal: setelah logout otomatis) ---
// Pesan dari GET hanya diproses jika tidak ada pengiriman form POST
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['msg'])) {
    if ($_GET['msg'] == 'logged_out') {
        $error_message = "Sesi Anda telah berakhir. Silakan login kembali.";
    }
    // Tambahkan penanganan untuk pesan 'msg' lainnya jika diperlukan
}

// Konfigurasi Batasan Percobaan Login
$max_attempts = 5; // Jumlah maksimal percobaan login yang gagal
$lockout_time = 15 * 60; // Waktu lockout dalam detik (15 menit)

// Waktu idle untuk auto-logout (5 menit), ini hanya untuk informasi di sini
$idle_timeout = 5 * 60; 

// Fungsi untuk mendapatkan IP address pengguna
function get_client_ip() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

$client_ip = get_client_ip();

// --- Cek Jika User Sudah Login (Berdasarkan Session PHP) ---
if (isset($_SESSION['user_id'])) {
    // Update waktu aktivitas terakhir untuk menjaga sesi tetap hidup
    $_SESSION['last_activity'] = time();

    // Redirect langsung sesuai peran pengguna
    switch ($_SESSION['role']) {
        case 'admin':
            header('Location: dashboard/admin/index.php');
            exit(); // Hentikan eksekusi setelah redirect
        case 'developer':
            header('Location: dashboard/developer/index.php');
            exit();
        case 'teknisi':
            header('Location: dashboard/teknisi/index.php');
            exit();
        case 'siswa':
            header('Location: dashboard/siswa/index.php');
            exit();
        default:
            // Jika peran tidak dikenali, hancurkan sesi untuk keamanan dan tampilkan error
            session_unset();
            session_destroy();
            $error_message = "Peran pengguna tidak dikenal atau tidak valid. Silakan login kembali."; 
            // Tidak ada redirect di sini; biarkan script lanjut ke HTML untuk menampilkan pesan error.
            break; 
    }
}

// --- Proses Pengiriman Form Login (Metode POST) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil dan bersihkan input dari form
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validasi input form dasar
    if (empty($username) || empty($password)) {
        $error_message = "Username dan password tidak boleh kosong.";
    } else {
        // --- Langkah 1: Cek Batasan Percobaan Login Gagal ---
        if ($stmt_delete_old = $koneksi->prepare("DELETE FROM login_attempts WHERE attempt_time < DATE_SUB(NOW(), INTERVAL ? SECOND)")) {
            $stmt_delete_old->bind_param("i", $lockout_time);
            $stmt_delete_old->execute();
            $stmt_delete_old->close();
        } else { error_log("DB_ERROR: Failed to prepare DELETE old attempts: " . $koneksi->error); $error_message = "Terjadi kesalahan sistem. Silakan coba lagi nanti."; }

        $ip_attempts = 0;
        if (empty($error_message) && ($stmt_check_ip = $koneksi->prepare("SELECT COUNT(*) FROM login_attempts WHERE ip_address = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL ? SECOND)"))) {
            $stmt_check_ip->bind_param("si", $client_ip, $lockout_time);
            if ($stmt_check_ip->execute()) { $stmt_check_ip->bind_result($ip_attempts); $stmt_check_ip->fetch(); }
            else { error_log("DB_ERROR: Failed to execute SELECT IP attempts: " . $stmt_check_ip->error); $error_message = "Terjadi kesalahan sistem. Silakan coba lagi nanti."; }
            $stmt_check_ip->close();
        } elseif (!empty($error_message)) { /* Skip */ } else { error_log("DB_ERROR: Failed to prepare SELECT IP attempts: " . $koneksi->error); $error_message = "Terjadi kesalahan sistem. Silakan coba lagi nanti."; }

        $user_attempts = 0;
        if (empty($error_message) && ($stmt_check_user = $koneksi->prepare("SELECT COUNT(*) FROM login_attempts WHERE username_attempted = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL ? SECOND)"))) {
            $stmt_check_user->bind_param("si", $username, $lockout_time);
            if ($stmt_check_user->execute()) { $stmt_check_user->bind_result($user_attempts); $stmt_check_user->fetch(); }
            else { error_log("DB_ERROR: Failed to execute SELECT user attempts: " . $stmt_check_user->error); $error_message = "Terjadi kesalahan sistem. Silakan coba lagi nanti."; }
            $stmt_check_user->close();
        } elseif (!empty($error_message)) { /* Skip */ } else { error_log("DB_ERROR: Failed to prepare SELECT user attempts: " . $koneksi->error); $error_message = "Terjadi kesalahan sistem. Silakan coba lagi nanti."; }

        if (empty($error_message)) {
            if ($ip_attempts >= $max_attempts || $user_attempts >= $max_attempts) {
                $error_message = "Terlalu banyak percobaan login gagal. Harap coba lagi dalam " . ($lockout_time / 60) . " menit.";
            } else {
                // --- Verifikasi Kredensial Pengguna ---
                if ($stmt_get_user = $koneksi->prepare("SELECT id, username, password, role, full_name, photo, session_id FROM users WHERE username = ?")) {
                    $stmt_get_user->bind_param("s", $username);
                    if ($stmt_get_user->execute()) {
                        $result_user_data = $stmt_get_user->get_result();

                        if ($result_user_data->num_rows == 1) {
                            $user_data_from_db = $result_user_data->fetch_assoc();
                            
                            // Verifikasi password
                            if (password_verify($password, $user_data_from_db['password'])) {
                                // Login berhasil
                                error_log("DEBUG_LOGIN: Password verified for user: " . $username);

                                // --- Force Logout Sesi Lama di Database ---
                                // Logika ini akan memastikan session_id di DB di-NULL-kan jika ada
                                // Ini adalah bagian inti dari "last login wins"
                                if ($user_data_from_db['session_id'] !== NULL) { 
                                    error_log("DEBUG_LOGIN: Detected existing session_id in DB. Attempting to clear it for user ID: " . $user_data_from_db['id']);
                                    $clear_old_session_stmt = $koneksi->prepare("UPDATE users SET session_id = NULL WHERE id = ?");
                                    if ($clear_old_session_stmt) {
                                        $clear_old_session_stmt->bind_param("i", $user_data_from_db['id']);
                                        if ($clear_old_session_stmt->execute()) {
                                            error_log("DEBUG_LOGIN: Old session_id successfully cleared in DB. Affected rows: " . $clear_old_session_stmt->affected_rows);
                                        } else {
                                            error_log("DB_ERROR: Failed to execute clear old session_id: " . $clear_old_session_stmt->error);
                                        }
                                        $clear_old_session_stmt->close();
                                    } else {
                                        error_log("DB_ERROR: Failed to prepare clear old session_id statement: " . $koneksi->error);
                                    }
                                } else {
                                    error_log("DEBUG_LOGIN: No old session_id found in DB (already NULL).");
                                }
                                // --- AKHIR FORCE LOGOUT ---

                                // Hapus percobaan login gagal sebelumnya untuk user ini dan IP ini
                                if ($stmt_delete_success = $koneksi->prepare("DELETE FROM login_attempts WHERE username_attempted = ? OR ip_address = ?")) {
                                    $stmt_delete_success->bind_param("ss", $username, $client_ip);
                                    $stmt_delete_success->execute();
                                    $stmt_delete_success->close();
                                } else {
                                     error_log("DB_ERROR: Failed to prepare DELETE attempts after success: " . $koneksi->error);
                                }

                                session_regenerate_id(true); // PENTING: Regenerasi ID sesi baru
                                $current_session_id = session_id(); 
                                error_log("DEBUG_LOGIN: New session_id regenerated: " . $current_session_id);

                                // Simpan data user ke dalam session
                                $_SESSION['user_id'] = $user_data_from_db['id'];
                                $_SESSION['username'] = $user_data_from_db['username'];
                                $_SESSION['role'] = $user_data_from_db['role'];
                                $_SESSION['full_name'] = $user_data_from_db['full_name'];
                                $_SESSION['photo'] = $user_data_from_db['photo']; 
                                $_SESSION['last_activity'] = time(); 

                                // Simpan ID sesi baru ke database untuk melacak sesi aktif
                                if ($stmt_update_session = $koneksi->prepare("UPDATE users SET session_id = ? WHERE id = ?")) {
                                    $stmt_update_session->bind_param("si", $current_session_id, $user_data_from_db['id']);
                                    if ($stmt_update_session->execute()) {
                                        error_log("DEBUG_LOGIN: New session_id " . $current_session_id . " successfully saved to DB for user " . $user_data_from_db['id']);
                                    } else {
                                        error_log("DB_ERROR: Failed to execute update new session ID: " . $stmt_update_session->error);
                                    }
                                    $stmt_update_session->close();
                                } else {
                                    error_log("DB_ERROR: Failed to prepare update new session ID statement: " . $koneksi->error);
                                }

                                // Redirect ke dashboard sesuai peran pengguna
                                switch ($user_data_from_db['role']) {
                                    case 'admin': header('Location: dashboard/admin/index.php'); exit();
                                    case 'developer': header('Location: dashboard/developer/index.php'); exit();
                                    case 'teknisi': header('Location: dashboard/teknisi/index.php'); exit();
                                    case 'siswa': header('Location: dashboard/siswa/index.php'); exit();
                                    default:
                                        session_unset();
                                        session_destroy();
                                        $error_message = "Peran pengguna tidak dikenal atau tidak valid."; 
                                        break; 
                                }
                                
                            } else {
                                // Password salah
                                $error_message = "Username atau password salah.";
                                if ($stmt_insert_attempt = $koneksi->prepare("INSERT INTO login_attempts (username_attempted, ip_address) VALUES (?, ?)")) {
                                    $stmt_insert_attempt->bind_param("ss", $username, $client_ip);
                                    $stmt_insert_attempt->execute();
                                    $stmt_insert_attempt->close();
                                } else {
                                     error_log("DB_ERROR: Failed to prepare INSERT attempt (password wrong): " . $koneksi->error);
                                }
                            }
                        } else {
                            // Username tidak ditemukan di database
                            $error_message = "Username atau password salah.";
                            if ($stmt_insert_attempt = $koneksi->prepare("INSERT INTO login_attempts (username_attempted, ip_address) VALUES (?, ?)")) {
                                $stmt_insert_attempt->bind_param("ss", $username, $client_ip);
                                $stmt_insert_attempt->execute();
                                $stmt_insert_attempt->close();
                            } else {
                                 error_log("DB_ERROR: Failed to prepare INSERT attempt (username not found): " . $koneksi->error);
                            }
                        }
                    } else {
                         error_log("DB_ERROR: Failed to execute SELECT statement for user login: " . $stmt_get_user->error);
                         $error_message = "Terjadi kesalahan sistem saat verifikasi pengguna. Silakan coba lagi nanti.";
                    }
                    $stmt_get_user->close();
                } else {
                    error_log("DB_ERROR: Failed to prepare SELECT statement for user login: " . $koneksi->error);
                    $error_message = "Terjadi kesalahan sistem. Silakan coba lagi nanti.";
                }
            }
        }
    }
}

// Tutup koneksi database setelah semua operasi selesai
$koneksi->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            overflow: hidden;
            background: linear-gradient(135deg, #1a202c 0%, #2d3748 100%);
            color: #e2e8f0;
            height: 100vh;
            position: relative;
        }

        /* Container utama animasi */
        .scene-container {
            width: 100%;
            height: 100%;
            position: absolute;
            top: 0; left: 0;
            overflow: hidden;
            z-index: 1;
        }

        /* Animasi dan karakter kamu - tidak diubah */
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        @keyframes pulse {
            0% { transform: scale(1); opacity: 0.8; }
            50% { transform: scale(1.1); opacity: 1; }
            100% { transform: scale(1); opacity: 0.8; }
        }
        @keyframes line-flow {
            0% { stroke-dashoffset: 0; }
            100% { stroke-dashoffset: 100; }
        }
        .character {
            position: absolute;
            width: 120px;
            height: 120px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            animation: float 3s ease-in-out infinite;
            z-index: 10;
        }
        .character svg {
            width: 80px;
            height: 80px;
            margin-bottom: 5px;
        }
        .character .label {
            font-size: 0.8rem;
            font-weight: 600;
            color: #a0aec0;
            background-color: rgba(0, 0, 0, 0.4);
            padding: 3px 8px;
            border-radius: 5px;
            white-space: nowrap;
        }
        .ui-ux { top: 15%; left: 10%; }
        .web-dev { top: 60%; left: 20%; }
        .network-eng { top: 30%; right: 15%; }
        .data-analyst { bottom: 10%; left: 40%; }
        .mobile-dev { top: 20%; left: 45%; }
        .data-scientist { bottom: 20%; right: 25%; }
        .cyber-security { top: 50%; left: 5%; }
        .cloud-eng { bottom: 5%; right: 5%; }
        .network-bg {
            position: absolute;
            width: 100%;
            height: 100%;
            z-index: 5;
        }
        .network-bg svg {
            width: 100%;
            height: 100%;
        }
        .network-bg .node {
            fill: #4299e1;
            animation: pulse 2s ease-in-out infinite;
            transform-origin: center center;
        }
        .network-bg .line {
            stroke: #63b3ed;
            stroke-width: 1.5;
            stroke-dasharray: 10 5;
            animation: line-flow 10s linear infinite;
            opacity: 0.5;
        }

        /* Media queries (optional, sesuaikan) */
        @media (max-width: 768px) {
            .character { width: 90px; height: 90px; }
            .character svg { width: 60px; height: 60px; }
            .character .label { font-size: 0.7rem; }
            .ui-ux { top: 10%; left: 5%; }
            .web-dev { top: 40%; left: 10%; }
            .network-eng { top: 10%; right: 5%; }
            .data-analyst { bottom: 10%; left: 20%; }
            .mobile-dev { top: 30%; right: 10%; }
            .data-scientist { bottom: 20%; right: 5%; }
            .cyber-security { top: 70%; left: 5%; }
            .cloud-eng { bottom: 5%; right: 5%; }
        }
        @media (max-width: 480px) {
            .character { width: 70px; height: 70px; }
            .character svg { width: 50px; height: 50px; }
            .character .label { font-size: 0.6rem; padding: 2px 5px; }
            .ui-ux { top: 5%; left: 2%; }
            .web-dev { top: 25%; left: 5%; }
            .network-eng { top: 5%; right: 2%; }
            .data-analyst { bottom: 5%; left: 10%; }
            .mobile-dev { top: 20%; right: 5%; }
            .data-scientist { bottom: 10%; right: 2%; }
            .cyber-security { top: 45%; left: 2%; }
            .cloud-eng { bottom: 2%; right: 2%; }
        }

        /* Styling form login */
        .login-form-wrapper {
            position: relative;
            z-index: 20;
            background: rgba(0, 0, 0, 0.7);
            padding: 2rem;
            border-radius: 12px;
            width: 320px;
            box-shadow: 0 0 15px rgba(0,0,0,0.5);
            color: #e2e8f0;
        }
    </style>
</head>
<body>
     <div class="scene-container">
      <div class="network-bg">
        <svg viewBox="0 0 1000 600" preserveAspectRatio="xMidYMid slice">
          <line class="line" x1="100" y1="100" x2="300" y2="200" />
          <line class="line" x1="300" y1="200" x2="500" y2="150" />
          <line class="line" x1="500" y1="150" x2="700" y2="250" />
          <line class="line" x1="700" y1="250" x2="900" y2="100" />
          <line class="line" x1="150" y1="300" x2="400" y2="400" />
          <line class="line" x1="400" y1="400" x2="650" y2="350" />
          <line class="line" x1="650" y1="350" x2="850" y2="450" />
          <line class="line" x1="200" y1="500" x2="500" y2="550" />
          <line class="line" x1="500" y1="550" x2="800" y2="500" />
          <line class="line" x1="100" y1="100" x2="150" y2="300" />
          <line class="line" x1="300" y1="200" x2="400" y2="400" />
          <line class="line" x1="500" y1="150" x2="650" y2="350" />
          <line class="line" x1="700" y1="250" x2="850" y2="450" />
          <line class="line" x1="900" y1="100" x2="800" y2="500" />
          <line class="line" x1="200" y1="500" x2="150" y2="300" />
          <line class="line" x1="400" y1="400" x2="500" y2="550" />
          <line class="line" x1="650" y1="350" x2="800" y2="500" />

          <circle class="node" cx="100" cy="100" r="8" />
          <circle class="node" cx="300" cy="200" r="8" />
          <circle class="node" cx="500" cy="150" r="8" />
          <circle class="node" cx="700" cy="250" r="8" />
          <circle class="node" cx="900" cy="100" r="8" />
          <circle class="node" cx="150" cy="300" r="8" />
          <circle class="node" cx="400" cy="400" r="8" />
          <circle class="node" cx="650" cy="350" r="8" />
          <circle class="node" cx="850" cy="450" r="8" />
          <circle class="node" cx="200" cy="500" r="8" />
          <circle class="node" cx="500" cy="550" r="8" />
          <circle class="node" cx="800" cy="500" r="8" />
        </svg>
      </div>

      <div class="character ui-ux">
        <svg
          xmlns="http://www.w3.org/2000/svg"
          viewBox="0 0 24 24"
          fill="none"
          stroke="#63b3ed"
          stroke-width="1.5"
          stroke-linecap="round"
          stroke-linejoin="round"
        >
          <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
          <line x1="8" y1="21" x2="16" y2="21"></line>
          <line x1="12" y1="17" x2="12" y2="21"></line>
          <path d="M7 7h.01"></path>
          <path d="M10 7h.01"></path>
          <path d="M13 7h.01"></path>
          <path d="M7 10h.01"></path>
          <path d="M10 10h.01"></path>
          <path d="M13 10h.01"></path>
        </svg>
        <span class="label">UI/UX Designer</span>
      </div>

      <div class="character web-dev">
        <svg
          xmlns="http://www.w3.org/2000/svg"
          viewBox="0 0 24 24"
          fill="none"
          stroke="#a78bfa"
          stroke-width="1.5"
          stroke-linecap="round"
          stroke-linejoin="round"
        >
          <path d="M16 18l6-6-6-6M8 6l-6 6 6 6M15 21l-6-18"></path>
        </svg>
        <span class="label">Web Developer</span>
      </div>

      <div class="character network-eng">
            <svg width="64" height="64" viewBox="0 0 64 64" fill="none" stroke="#DB1514" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" xmlns="http://www.w3.org/2000/svg">
            <path d="M10 54 L 54 54" />
            <path d="M32 46 L 32 62" />
            <rect x="24" y="10" width="16" height="16" rx="2" />
            <rect x="24" y="30" width="16" height="16" rx="2" />
            <path d="M32 26 L 32 10" />
            <path d="M32 46 L 32 30" />
            <path d="M18 16 L 8 6" />
            <path d="M46 16 L 56 6" />
            <path d="M18 40 L 8 34" />
            <path d="M46 40 L 56 34" />
            </svg>
        <span class="label">Network Engineer</span>
      </div>

      <div class="character data-analyst">
        <svg
          xmlns="http://www.w3.org/2000/svg"
          viewBox="0 0 24 24"
          fill="none"
          stroke="#fc8181"
          stroke-width="1.5"
          stroke-linecap="round"
          stroke-linejoin="round"
        >
          <path d="M12 20V10"></path>
          <path d="M18 20V4"></path>
          <path d="M6 20v-4"></path>
          <line x1="12" y1="20" x2="12" y2="10"></line>
          <line x1="18" y1="20" x2="18" y2="4"></line>
          <line x1="6" y1="20" x2="6" y2="16"></line>
        </svg>
        <span class="label">Data Analyst</span>
      </div>

      <div class="character mobile-dev">
        <svg
          xmlns="http://www.w3.org/2000/svg"
          viewBox="0 0 24 24"
          fill="none"
          stroke="#48bb78"
          stroke-width="1.5"
          stroke-linecap="round"
          stroke-linejoin="round"
        >
          <rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect>
          <line x1="12" y1="18" x2="12" y2="18"></line>
        </svg>
        <span class="label">Mobile Developer</span>
      </div>

      <div class="character data-scientist">
        <svg
          xmlns="http://www.w3.org/2000/svg"
          viewBox="0 0 24 24"
          fill="none"
          stroke="#ecc94b"
          stroke-width="1.5"
          stroke-linecap="round"
          stroke-linejoin="round"
        >
          <circle cx="12" cy="12" r="3"></circle>
          <path
            d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0-.33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82-.33V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09A1.65 1.65 0 0 0 15 4.6a1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0 .33 1.82H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"
          ></path>
        </svg>
        <span class="label">Data Scientist</span>
      </div>

      <div class="character cyber-security">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#DB1514" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 1L3 5v6c0 5 4 9 9 9s9-4 9-9V5l-9-4z"></path>
                <path d="M12 11v4"></path>
                <circle cx="12" cy="16" r="1"></circle>
            </svg>
            <div class="label">Cyber Security</div>
        </div>

      <div class="character cloud-eng">
        <svg
          xmlns="http://www.w3.org/2000/svg"
          viewBox="0 0 24 24"
          fill="none"
          stroke="#4299e1"
          stroke-width="1.5"
          stroke-linecap="round"
          stroke-linejoin="round"
        >
          <path d="M18 10h-1.26A8 8 0 1 0 9 20h9a5 5 0 0 0 0-10z"></path>
        </svg>
        <span class="label">Cloud Engineer</span>
      </div>
    </div>

    <div class="d-flex justify-content-center align-items-center" style="height: 100vh; position: relative; z-index: 20; width: 100%;">
        
        <form class="login-form-wrapper" action="#" method="POST">
            <img src="img/logo.png" alt="logo" class="img-fluid mb-3" style="width: 85px; height: 85px; display: block; margin: 0 auto;" />
            <h4 class="mb-4 text-center">Network IT Club</h4>
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan username" required />
            </div>
            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password" required />
            </div>
            <?php if ($error_message): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>