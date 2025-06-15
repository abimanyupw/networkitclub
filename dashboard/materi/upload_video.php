<?php
// Pastikan ini dimulai di paling atas
session_start();

// Aktifkan pelaporan kesalahan PHP untuk debugging.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../includes/inc_koneksi.php';

// Verifikasi user role atau login (PENTING UNTUK KEAMANAN)
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'developer', 'teknisi'])) {
    http_response_code(403); // Forbidden
    echo json_encode(['error' => 'Akses ditolak. Anda tidak memiliki izin untuk mengunggah video.']);
    exit();
}

$response = array(); // Array untuk menyimpan respons JSON

if (isset($_FILES['file'])) { // 'file' adalah nama default yang digunakan Summernote
    $file = $_FILES['file'];

    // Atur direktori tujuan upload relatif terhadap file ini
    $upload_dir = '../../uploads/materi_videos/'; // Sesuaikan path ini
    
    // Pastikan direktori upload ada, jika tidak, buat
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0775, true); // Buat direktori secara rekursif
    }

    // Validasi Tipe File (MIME Type) - Tambahkan tipe video yang Anda dukung
    $allowed_mime_types = [
        'video/mp4',
        'video/webm',
        'video/ogg',
        // Tambahkan tipe lain jika diperlukan
    ];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime_type, $allowed_mime_types)) {
        $response = ['error' => 'Tipe file video tidak diizinkan. Hanya MP4, WebM, Ogg yang diizinkan.'];
    } else {
        // Validasi Ukuran File (contoh: maks 50MB - SESUAIKAN DENGAN KEMAMPUAN SERVER ANDA)
        $max_file_size = 50 * 1024 * 1024; // 50 MB
        if ($file['size'] > $max_file_size) {
            $response = ['error' => 'Ukuran file terlalu besar. Maksimal 50MB.'];
        } else {
            // Bersihkan nama file dan generate nama unik
            $file_name = basename($file['name']);
            $new_file_name = uniqid('vid_', true) . '.' . pathinfo($file_name, PATHINFO_EXTENSION);
            $target_file = $upload_dir . $new_file_name;

            if (move_uploaded_file($file['tmp_name'], $target_file)) {
                // Sukses upload, kembalikan URL video relatif terhadap root situs web
                // SESUAIKAN PUBLIC URL PATH INI SAMA SEPERTI DI upload_image.php!
                $public_url_path = '/networkitclub/uploads/materi_videos/' . $new_file_name; // Contoh untuk XAMPP
                // Atau jika domain langsung ke root proyek:
                // $public_url_path = '/uploads/materi_videos/' . $new_file_name;

                $response = ['url' => $public_url_path];
            } else {
                $response = ['error' => 'Gagal memindahkan file video. Periksa izin folder: ' . $upload_dir];
                error_log("UPLOAD_VIDEO_ERROR: Failed to move uploaded file: " . $file['tmp_name'] . " to " . $target_file);
            }
        }
    }
} else {
    $response = ['error' => 'Tidak ada file video yang diunggah.'];
}

header('Content-Type: application/json');
echo json_encode($response);

if (isset($koneksi) && is_object($koneksi) && $koneksi->ping()) {
    $koneksi->close();
}
exit();
?>