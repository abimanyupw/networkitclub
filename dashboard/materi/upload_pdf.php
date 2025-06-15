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
    echo json_encode(['error' => 'Akses ditolak. Anda tidak memiliki izin untuk mengunggah file PDF.']);
    exit();
}

$response = array(); // Array untuk menyimpan respons JSON

if (isset($_FILES['pdf_file'])) { // 'pdf_file' adalah nama input file di form HTML
    $file = $_FILES['pdf_file'];

    // Atur direktori tujuan upload
    $upload_dir = '../../uploads/materi_pdfs/'; // Sesuaikan path ini
    $upload_dir_absolute = realpath($upload_dir) . DIRECTORY_SEPARATOR;

    // Pastikan direktori upload ada, jika tidak, buat
    if (!is_dir($upload_dir_absolute)) {
        if (!mkdir($upload_dir_absolute, 0775, true)) { // 0775 untuk Linux, izinkan web server menulis
            $response = ['error' => 'Gagal membuat direktori upload: ' . $upload_dir_absolute];
            error_log("UPLOAD_PDF_ERROR: Failed to create directory: " . $upload_dir_absolute);
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        }
    }

    // Validasi Tipe File (MIME Type)
    $allowed_mime_types = ['application/pdf'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime_type, $allowed_mime_types)) {
        $response = ['error' => 'Tipe file tidak diizinkan. Hanya PDF yang diizinkan.'];
    } else {
        // Validasi Ukuran File (contoh: maks 20MB - SESUAIKAN DENGAN KEMAMPUAN SERVER DAN PHP.INI)
        $max_file_size = 20 * 1024 * 1024; // 20 MB
        if ($file['size'] > $max_file_size) {
            $response = ['error' => 'Ukuran file terlalu besar. Maksimal ' . ($max_file_size / (1024 * 1024)) . 'MB.'];
        } else {
            // Bersihkan nama file dan generate nama unik
            $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $new_file_name = uniqid('pdf_', true) . '.' . $file_extension;
            $target_file_path = $upload_dir_absolute . $new_file_name;

            if (move_uploaded_file($file['tmp_name'], $target_file_path)) {
                // Sukses upload, kembalikan URL file PDF relatif terhadap root situs web
                // SESUAIKAN PUBLIC URL PATH INI SAMA SEPERTI DI upload_image.php!
                $public_url = '/networkitclub/uploads/materi_pdfs/' . $new_file_name; // Contoh untuk XAMPP

                $response = ['url' => $public_url];
            } else {
                $response = ['error' => 'Gagal memindahkan file PDF. Error code: ' . $file['error'] . '. Pastikan izin folder benar.'];
                error_log("UPLOAD_PDF_ERROR: Failed to move uploaded file: " . $file['tmp_name'] . " to " . $target_file_path);
            }
        }
    }
} else {
    $response = ['error' => 'Tidak ada file PDF yang diunggah.'];
}

header('Content-Type: application/json');
echo json_encode($response);

if (isset($koneksi) && is_object($koneksi) && $koneksi->ping()) {
    $koneksi->close();
}
exit();
?>