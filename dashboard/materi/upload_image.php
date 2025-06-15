<?php
// Pastikan ini dimulai di paling atas
session_start();

// Aktifkan pelaporan kesalahan PHP untuk debugging.
// Harap nonaktifkan ini di lingkungan produksi untuk keamanan.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Sertakan koneksi database jika diperlukan untuk logging atau validasi user
require_once '../../includes/inc_koneksi.php';

// Verifikasi user role atau login (PENTING UNTUK KEAMANAN)
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'developer', 'teknisi'])) {
    http_response_code(403); // Forbidden
    echo json_encode(['error' => 'Akses ditolak. Anda tidak memiliki izin untuk mengunggah gambar.']);
    exit();
}

$response = array(); // Array untuk menyimpan respons JSON

if (isset($_FILES['file'])) { // 'file' adalah nama default yang digunakan Summernote untuk upload
    $file = $_FILES['file'];

    // Atur direktori tujuan upload relatif terhadap file ini
    // Pastikan folder ini ada dan memiliki izin tulis (misalnya 775 atau 777 untuk pengujian, lalu 775 di produksi)
    $upload_dir = '../../uploads/materi_images/'; // Sesuaikan path ini sesuai struktur folder Anda

    // Pastikan direktori upload ada, jika tidak, buat
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0775, true); // Buat direktori secara rekursif
    }

    // Validasi Tipe File (MIME Type)
    $allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime_type, $allowed_mime_types)) {
        $response = ['error' => 'Tipe file tidak diizinkan. Hanya JPG, PNG, GIF, WEBP yang diizinkan.'];
    } else {
        // Validasi Ukuran File (contoh: maks 5MB)
        $max_file_size = 5 * 1024 * 1024; // 5 MB
        if ($file['size'] > $max_file_size) {
            $response = ['error' => 'Ukuran file terlalu besar. Maksimal 5MB.'];
        } else {
            // Bersihkan nama file untuk keamanan
            $file_name = basename($file['name']);
            // Generate nama file unik untuk menghindari overwrite dan masalah karakter
            $new_file_name = uniqid('img_', true) . '.' . pathinfo($file_name, PATHINFO_EXTENSION);
            $target_file = $upload_dir . $new_file_name;

            if (move_uploaded_file($file['tmp_name'], $target_file)) {
                // Sukses upload, kembalikan URL gambar relatif terhadap root situs web
                // Asumsi: '../../uploads/materi_images/' relatif terhadap 'materi/upload_image.php'
                // Maka, URL publiknya akan menjadi '/uploads/materi_images/new_file_name.ext'
                
                // Hitung path relatif dari root dokumen web
                // Ini mungkin memerlukan penyesuaian tergantung pada setup server Anda
                // Contoh jika root ada di parent dari 'dashboard'
                $web_root_path = dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR; // Ini menuju ke root 'networkitclub'
                
                // Pastikan $upload_dir_abs adalah path absolut ke folder uploads
                // Contoh: C:\xampp2\htdocs\networkitclub\uploads\materi_images\
                $upload_dir_abs = realpath($upload_dir) . DIRECTORY_SEPARATOR;
                
                // URL yang akan dikembalikan ke Summernote
                // Anda perlu URL yang bisa diakses oleh browser dari root web.
                // Jika structure: htdocs/networkitclub/dashboard/materi/upload_image.php
                // dan htdocs/networkitclub/uploads/materi_images/
                // Maka URL-nya adalah /networkitclub/uploads/materi_images/
                
                // Cara paling aman adalah mendefinisikan base URL atau menentukannya secara dinamis
                // Untuk localhost, bisa jadi: http://localhost/networkitclub/uploads/materi_images/
                $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
                // Asumsi networkitclub ada di root htdocs atau subdomain
                // Jika networkitclub adalah folder di htdocs:
                $public_url_path = '/networkitclub/uploads/materi_images/' . $new_file_name; // SESUAIKAN DENGAN NAMA FOLDER PROYEK ANDA!
                // Jika domain Anda langsung menunjuk ke networkitclub:
                // $public_url_path = '/uploads/materi_images/' . $new_file_name;


                $response = ['url' => $public_url_path];
            } else {
                $response = ['error' => 'Gagal memindahkan file. Periksa izin folder: ' . $upload_dir];
                error_log("UPLOAD_ERROR: Failed to move uploaded file: " . $file['tmp_name'] . " to " . $target_file);
            }
        }
    }
} else {
    $response = ['error' => 'Tidak ada file yang diunggah.'];
}

header('Content-Type: application/json');
echo json_encode($response);

// Tutup koneksi database jika dibuka
if (isset($koneksi) && is_object($koneksi) && $koneksi->ping()) {
    $koneksi->close();
}

exit(); // Pastikan tidak ada output lain
?>