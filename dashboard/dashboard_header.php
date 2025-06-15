<?php
// dashboard/dashboard_header.php

// Pastikan session sudah dimulai. session_start() harus dipanggil di awal setiap skrip yang menggunakan session.
// Jika file yang memanggil dashboard_header.php sudah memiliki session_start(), ini bisa diabaikan.
// Namun, untuk keamanan, pastikan tidak ada output sebelum session_start().
// session_start(); // Biasanya ini ada di file utama yang memanggil header ini.

// Pastikan koneksi database tersedia di sini untuk validasi sesi
// Lokasi relatif dari dashboard_header.php ke inc_koneksi.php
require_once '../../includes/inc_koneksi.php'; 

// --- LOGIKA VALIDASI SESI AKTIF DI DATABASE ---
// Logika ini harus dijalankan di setiap halaman yang dilindungi
if (isset($_SESSION['user_id'])) {
    $current_user_id = $_SESSION['user_id'];
    $current_browser_session_id = session_id(); // ID sesi dari cookie browser saat ini

    // Ambil session_id yang tersimpan di database untuk user ini
    $stmt_check_db_session = $koneksi->prepare("SELECT session_id FROM users WHERE id = ?");
    if ($stmt_check_db_session) {
        $stmt_check_db_session->bind_param("i", $current_user_id);
        $stmt_check_db_session->execute();
        $result_db_session = $stmt_check_db_session->get_result();

        if ($result_db_session->num_rows > 0) {
            $db_session_data = $result_db_session->fetch_assoc();
            $db_stored_session_id = $db_session_data['session_id']; // ID sesi yang tersimpan di database

            // Bandingkan ID sesi dari browser dengan yang di database
            // Jika tidak cocok (atau di DB NULL), berarti sesi di browser ini tidak lagi valid
            if (empty($db_stored_session_id) || $db_stored_session_id !== $current_browser_session_id) {
                // Sesi di browser ini telah di-logout dari tempat lain atau kadaluarsa
                session_unset();    // Hapus semua variabel sesi
                session_destroy();  // Hancurkan data sesi di server
                // Redirect ke halaman login dengan pesan 'logged_out'
                header("Location: ../../login.php?msg=logged_out"); 
                exit(); // Hentikan eksekusi skrip
            }
        } else {
            // User ID dari sesi tidak ditemukan di database (mungkin akun dihapus)
            session_unset();
            session_destroy();
            header("Location: ../../login.php?msg=user_not_found_session");
            exit();
        }
        $stmt_check_db_session->close();
    } else {
        // Terjadi kesalahan saat menyiapkan statement, log error dan asumsikan sesi tidak valid
        error_log("DB_ERROR: Failed to prepare session validation statement in dashboard_header.php: " . $koneksi->error);
        session_unset();
        session_destroy();
        header("Location: ../../login.php?msg=db_error_session_check");
        exit();
    }
}
// --- AKHIR LOGIKA VALIDASI SESI AKTIF ---


// Variabel untuk informasi profil yang ditampilkan di header
$page_title = $page_title ?? 'Dashboard NIC'; // Gunakan nilai $page_title yang sudah diset di halaman pemanggil

$logged_in_username = $_SESSION['full_name'] ?? 'Guest';
$logged_in_role = $_SESSION['role'] ?? 'Guest';
$profile_photo = $_SESSION['photo'] ?? null;

// Mendapatkan path URI untuk menentukan menu aktif
$current_full_uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$dashboard_segment_name = 'dashboard';
$pos = strpos($current_full_uri_path, '/' . $dashboard_segment_name . '/');

$path_to_compare = '';
if ($pos !== false) {
    $path_to_compare = substr($current_full_uri_path, $pos + strlen('/' . $dashboard_segment_name . '/'));
} else {
    $path_to_compare = ltrim($current_full_uri_path, '/');
    $parts = explode('/', $path_to_compare);
    $dashboard_index = array_search($dashboard_segment_name, $parts);
    if ($dashboard_index !== false) {
        $path_to_compare = implode('/', array_slice($parts, $dashboard_index + 1));
    }
}

$path_to_compare = trim($path_to_compare, '/');
if (empty($path_to_compare) && (strpos($current_full_uri_path, '/index.php') !== false || substr($current_full_uri_path, -1) == '/')) {
    $path_to_compare = 'index.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="icon" href="../../img/logo.png" type="image/x-icon" />

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
    
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet" />
    
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
    <style>
        .note-editable {
    background-color: white !important; /* Paksa latar belakang putih */
    color: #333 !important; /* Pastikan teks berwarna gelap agar terbaca */
}

/* Jika seluruh editor Summernote memiliki latar belakang gelap */
.note-editor.note-frame {
    background-color: white !important;
}
    </style>
    
    <link rel="stylesheet" href="../../css/dashboard.css" />
    <link rel="stylesheet" href="../css/class.css" />
    <link rel="stylesheet" href="../css/material.css" />
    <link rel="stylesheet" href="../css/class_detail.css" />
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-top-dashboard">
        <div class="container-fluid">
            <button class="btn btn-outline-light d-lg-none me-2" type="button" id="sidebarToggle">
                <i class="fa-solid fa-bars"></i>
            </button>
            <a class="navbar-brand" href="../../index.php">Network IT Club</a>
        </div>
    </nav>

    <div class="dashboard-container">
        <div class="sidebar" id="sidebar">
             <div class="sidebar-profile">
            <?php
            $default_photo = 'default.png';
            $photo_path = '../../uploads/profile_photos/' . ($profile_photo ?: $default_photo);

            if (file_exists($photo_path)) {
                $photo_url = $photo_path . '?v=' . time();
                echo '<img src="' . htmlspecialchars($photo_url) . '" alt="Foto Profil" class="profile-avatar rounded-circle" width="50" style="object-fit: cover;" />';
            } else {
                echo '<div class="profile-avatar"><i class="fa-solid fa-user"></i></div>';
            }
            ?>

  <div class="profile-name" style="text-align: center;"><?php echo htmlspecialchars($logged_in_username); ?></div>
  <div class="profile-role text-uppercase"><?php echo htmlspecialchars($logged_in_role); ?></div>
</div>

            <h6 class="sidebar-heading">MAIN NAVIGATION</h6>
            <div class="list-group list-group-flush">
                <?php
                $current_role = $_SESSION['role'] ?? 'guest';
                $menu_items = [];

                if ($current_role == 'developer') {
                    $menu_items = [
                        ['Dashboard', '../developer/index.php', 'fa-solid fa-table-cells-large'],
                        ['Kelola Data', '#', 'fa-solid fa-folder', [
                            ['Pengguna', '../users/index.php', 'fa-solid fa-users'],
                            ['Kategori Materi', '../kategori/index.php', 'fa-solid fa-tags'],
                            ['Materi', '../materi/index.php', 'fa-solid fa-file-lines'],
                            ['Informasi', '../informasi/index.php', 'fa-solid fa-info-circle'],
                            ['Badge','../badges/index.php','fa-solid fa-certificate'],
                            ['Badge Siswa','../user_badges/index.php','fa-solid fa-id-badge'],
                            ['Tugas','../tugas/index.php','fas fa-tasks'],
                            ['Pengumpulan Tugas','../pengumpulan_tugas/index.php','fas fa-file-upload'],
                            ['Store','../store/index.php','fa-solid fa-store'],
                            ['Redeem','../redemptions/index.php','fa-solid fa-credit-card'],


                        ]],
                        ['Kelola Halaman', '#', 'fa-solid fa-pager', [
                            ['Home', '../home/index.php', 'fa-solid fa-house'],
                            ['About', '../about/index.php', 'fa-solid fa-circle-info'],
                            ['Class', '../class/index.php', 'fa-solid fa-chalkboard-user'],
                            ['Contact', '../contact/index.php', 'fa-solid fa-envelope']
                        ]],
                        ['Class Preview', '../class_preview/index.php', 'fa-solid fa-chalkboard-user']
                    ];
                } elseif ($current_role == 'admin') {
                    $menu_items = [
                        ['Dashboard', '../admin/index.php', 'fa-solid fa-table-cells-large'],
                        ['Kelola Data', '#', 'fa-solid fa-folder', [
                            ['Pengguna', '../users/index.php', 'fa-solid fa-users'],
                            ['Kategori Materi', '../kategori/index.php', 'fa-solid fa-tags'],
                            ['Class', '../class/index.php', 'fa-solid fa-chalkboard-user'],
                            ['Materi', '../materi/index.php', 'fa-solid fa-file-lines'],
                            ['Informasi', '../informasi/index.php', 'fa-solid fa-info-circle'],
                            ['Badge','../badges/index.php','fa-solid fa-certificate'],
                            ['Badge Siswa','../user_badges/index.php','fa-solid fa-id-badge'],
                            ['Tugas','../tugas/index.php','fas fa-tasks'],
                            ['Pengumpulan Tugas','../pengumpulan_tugas/index.php','fas fa-file-upload']

                        ]],
                        ['Class Preview', '../class_preview/index.php', 'fa-solid fa-chalkboard-user']
                    ];
                } elseif ($current_role == 'teknisi') {
                    $menu_items = [
                        ['Dashboard', '../teknisi/index.php', 'fa-solid fa-table-cells-large'],
                        ['Kategori Materi', '../kategori/index.php', 'fa-solid fa-tags'],
                        ['Materi', '../materi/index.php', 'fa-solid fa-file-lines'],
                        ['Kelas', '../class_preview/index.php', 'fa-solid fa-chalkboard-user'],
                        ['Badge','../badges/index.php','fa-solid fa-certificate'],
                        ['Badge Siswa','../user_badges/index.php','fa-solid fa-id-badge'],
                        ['Tugas','../tugas/index.php','fas fa-tasks'],
                        ['Pengumpulan Tugas','../pengumpulan_tugas/index.php','fas fa-file-upload']
                    ];
                } elseif ($current_role == 'siswa') {
                    $menu_items = [
                        ['Dashboard', '../siswa/index.php', 'fa-solid fa-table-cells-large'],
                        ['Kelas', '../class_preview/index.php', 'fa-solid fa-chalkboard-user'],
                        ['Tugas','../tugas_siswa/index.php','fas fa-tasks'],
                        ['Badge','../badge_siswa/index.php','fa-solid fa-certificate'],
                        ['Store','../store_siswa','fa-solid fa-store']
                    ];
                }

                foreach ($menu_items as $item) {
                    list($text, $link, $icon) = $item;
                    $has_submenu = isset($item[3]);

                    $is_active = ($path_to_compare == $link);

                    if ($has_submenu) {
                        $submenu_items = $item[3];
                        $is_parent_active = false;
                        foreach ($submenu_items as $sub_item) {
                            if ($path_to_compare == $sub_item[1]) {
                                $is_parent_active = true;
                                break;
                            }
                        }
                        echo '<a href="#submenu-' . str_replace(' ', '-', strtolower($text)) . '" data-bs-toggle="collapse" class="list-group-item list-group-item-action ' . ($is_parent_active ? 'active' : '') . '" aria-expanded="' . ($is_parent_active ? 'true' : 'false') . '">';
                        echo '<i class="' . htmlspecialchars($icon) . '"></i> ' . htmlspecialchars($text);
                        echo '<i class="fa-solid fa-chevron-down ms-auto"></i>';
                        echo '</a>';
                        echo '<div class="collapse ' . ($is_parent_active ? 'show' : '') . '" id="submenu-' . str_replace(' ', '-', strtolower($text)) . '">';
                        echo '<div class="list-group ps-4">';
                        foreach ($submenu_items as $sub_item) {
                            $is_sub_active = ($path_to_compare == $sub_item[1]);
                            echo '<a href="' . htmlspecialchars($sub_item[1]) . '" class="list-group-item list-group-item-action ' . ($is_sub_active ? 'active' : '') . '">';
                            echo '<i class="' . htmlspecialchars($sub_item[2]) . '"></i> ' . htmlspecialchars($sub_item[0]);
                            echo '</a>';
                        }
                        echo '</div></div>';
                    } else {
                        echo '<a href="' . htmlspecialchars($link) . '" class="list-group-item list-group-item-action ' . ($is_active ? 'active' : '') . '">';
                        echo '<i class="' . htmlspecialchars($icon) . '"></i> ' . htmlspecialchars($text);
                        echo '</a>';
                    }
                }
                ?>
                <h6 class="sidebar-heading mt-4">SETTINGS</h6>
                <a href="../settings/index.php" class="list-group-item list-group-item-action <?php echo ($path_to_compare == 'settings.php') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-gear"></i> Pengaturan
                </a>
                <a href="../logout.php" class="list-group-item list-group-item-action">
                    <i class="fa-solid fa-right-from-bracket"></i> Logout
                </a>
            </div>
        </div>

        <div class="main-content" style="width: 100%;">
            <div class="page-content-area" style="padding: 0.5rem;">