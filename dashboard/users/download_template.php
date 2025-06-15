<?php
session_start();

// Sertakan autoloader Composer untuk memuat kelas PhpSpreadsheet
// Pastikan path ini benar sesuai struktur direktori proyek Anda.
// Contoh: Jika file ini ada di 'admin/users/download_template.php'
// dan folder 'vendor' ada di root proyek, maka path-nya '../../vendor/autoload.php'.
require '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx; // Gunakan Xlsx untuk format .xlsx

// Cek apakah user sudah login. Jika tidak, arahkan kembali ke halaman login.
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "Anda perlu login untuk mengunduh template.";
    header('Location: ../../login.php'); // Sesuaikan path ke halaman login Anda
    exit();
}

// Opsional: Batasi role yang boleh mengunduh template.
// Aktifkan blok ini jika hanya role 'admin' atau 'developer' yang diizinkan.
/*
if (!($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'developer')) {
    $_SESSION['error_message'] = "Anda tidak memiliki izin untuk mengunduh template.";
    header('Location: ../../dashboard/index.php'); // Arahkan ke dashboard atau halaman lain
    exit();
}
*/

// 1. Buat Objek Spreadsheet baru
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet(); // Dapatkan lembar kerja aktif (biasanya sheet pertama)

// 2. Tulis header kolom di baris pertama
// Pastikan urutan kolom sesuai dengan yang Anda harapkan untuk proses impor nanti.
$headers = ['username', 'email', 'full_name', 'role', 'password'];
$sheet->fromArray($headers, NULL, 'A1'); // Tulis array headers mulai dari sel A1

// Opsional: Tambahkan beberapa gaya atau instruksi di template untuk kemudahan pengguna
$sheet->getColumnDimension('A')->setWidth(20); // Lebar kolom untuk Username
$sheet->getColumnDimension('B')->setWidth(30); // Lebar kolom untuk Email
$sheet->getColumnDimension('C')->setWidth(25); // Lebar kolom untuk Nama Lengkap (full_name)
$sheet->getColumnDimension('D')->setWidth(15); // Lebar kolom untuk Role
$sheet->getColumnDimension('E')->setWidth(20); // Lebar kolom untuk Password

// Beri instruksi dan contoh data di baris berikutnya
$sheet->setCellValue('A3', 'Isi data pengguna mulai dari baris ini.');
$sheet->setCellValue('A4', 'Contoh: user1');
$sheet->setCellValue('B4', 'contoh@domain.com');
$sheet->setCellValue('C4', 'Nama Lengkap Pengguna'); // Contoh untuk full_name
$sheet->setCellValue('D4', 'user'); // Contoh role: 'siswa', 'admin', 'developer', dll.
$sheet->setCellValue('E4', 'password123'); // Contoh password (akan di-hash saat diimpor)

// 3. Siapkan Writer untuk format XLSX (format modern Excel)
$writer = new Xlsx($spreadsheet);

// 4. Set header HTTP untuk memaksa browser mengunduh file Excel
$filename = "template_import_pengguna.xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0'); // Agar browser tidak menyimpan cache dan selalu mengunduh versi baru

// 5. Kirim file ke browser
// 'php://output' mengirimkan output langsung ke browser.
$writer->save('php://output');
exit(); // Hentikan eksekusi skrip setelah file dikirim
?>