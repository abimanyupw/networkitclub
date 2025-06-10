<?php
session_start();

// Sertakan autoloader Composer untuk memuat kelas PhpSpreadsheet
require '../../vendor/autoload.php'; // Pastikan path ini benar!

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx; // Kita akan menggunakan Xlsx untuk format .xlsx

include '../../includes/inc_koneksi.php'; // Sesuaikan path koneksi database Anda

// Pastikan user sudah login dan memiliki role admin/developer
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'developer')) {
    $_SESSION['error_message'] = "Anda tidak memiliki akses untuk mengekspor data.";
    header('Location: ../../login.php');
    exit();
}

// Ambil parameter pencarian ('search') dan filter ('role') dari URL (jika ada).
$search_query = isset($_GET['search']) ? $_GET['search'] : '';
$filter_role = isset($_GET['role']) ? $_GET['role'] : '';

// Inisialisasi array untuk kondisi SQL, parameter binding, dan tipe binding.
$sql_conditions = [];
$bind_params = [];
$bind_types = '';

// Kondisi dasar: Jika pengguna yang login adalah 'admin',
// maka mereka tidak boleh mengekspor data pengguna dengan role 'developer'.
if ($_SESSION['role'] === 'admin') {
    $sql_conditions[] = "role != 'developer'";
}

// Tambahkan kondisi pencarian jika 'search_query' tidak kosong.
// Pencarian akan dilakukan di kolom 'username', 'email', dan 'full_name'.
if (!empty($search_query)) {
    $sql_conditions[] = "(username LIKE ? OR email LIKE ? OR full_name LIKE ?)";
    $bind_types .= 'sss'; // Menambahkan tiga 's' untuk tiga parameter string
    $bind_params[] = "%" . $search_query . "%";
    $bind_params[] = "%" . $search_query . "%";
    $bind_params[] = "%" . $search_query . "%";
}

// Tambahkan kondisi filter role jika 'filter_role' tidak kosong dan bukan 'all'.
if (!empty($filter_role) && $filter_role !== 'all') {
    $sql_conditions[] = "role = ?";
    $bind_types .= 's'; // Menambahkan satu 's' untuk parameter string role
    $bind_params[] = $filter_role;
}

// Gabungkan semua kondisi SQL menjadi klausa WHERE.
$where_clause = '';
if (!empty($sql_conditions)) {
    $where_clause = " WHERE " . implode(' AND ', $sql_conditions);
}

// --- Query SQL untuk mengambil data pengguna ---
// Kolom-kolom dipilih secara eksplisit agar lebih jelas.
// PERHATIAN: Mengekspor kolom 'password' (yang seharusnya hash) harus dilakukan dengan hati-hati.
$sql = "SELECT
            username,
            email,
            full_name,
            role,
            password
        FROM
            users
        " . $where_clause . "
        ORDER BY
            username ASC";

// Siapkan statement SQL menggunakan prepared statement untuk keamanan.
$stmt = $koneksi->prepare($sql);

// Tangani jika persiapan statement gagal.
if ($stmt === false) {
    error_log("Export Error: Gagal menyiapkan statement SQL - " . $koneksi->error);
    $_SESSION['error_message'] = "Terjadi kesalahan internal saat menyiapkan data ekspor. Silakan coba lagi.";
    header('Location: index.php'); // Arahkan kembali ke halaman manajemen pengguna
    exit();
}

// Bind parameter ke statement jika ada.
if (!empty($bind_params)) {
    $stmt->bind_param($bind_types, ...$bind_params);
}

// Eksekusi statement.
$stmt->execute();

// Dapatkan hasil dari eksekusi query.
$result = $stmt->get_result();

// Tangani jika pengambilan hasil gagal.
if (!$result) {
    error_log("Export Error: Gagal mengambil data pengguna dari database - " . $stmt->error);
    $_SESSION['error_message'] = "Terjadi kesalahan saat mengambil data untuk ekspor. Silakan coba lagi.";
    header('Location: index.php'); // Arahkan kembali ke halaman manajemen pengguna
    exit();
}

// --- PEMBUATAN FILE EXCEL DENGAN PHPSPREADSHEET ---

// 1. Buat objek Spreadsheet baru
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// 2. Tulis header kolom di baris pertama
// Kolom-kolom yang akan diekspor dan ditampilkan di Excel
$headers = [
    'Username',
    'Email',
    'Nama Lengkap',
    'Role',
    'Password Hash' // Label yang lebih akurat untuk kolom password
];
$sheet->fromArray($headers, NULL, 'A1'); // Tulis array headers mulai dari sel A1

// Opsional: Atur lebar kolom agar lebih rapi di Excel
$sheet->getColumnDimension('A')->setWidth(20); // Username
$sheet->getColumnDimension('B')->setWidth(30); // Email
$sheet->getColumnDimension('C')->setWidth(25); // Nama Lengkap
$sheet->getColumnDimension('D')->setWidth(15); // Role
$sheet->getColumnDimension('E')->setWidth(60); // Password Hash (karena hash panjang)


// 3. Tulis data baris demi baris ke Excel
$row_number = 2; // Mulai menulis data dari baris ke-2 (setelah header)
while ($row_data = $result->fetch_assoc()) {
    $data_to_write = [
        $row_data['username'],
        $row_data['email'],
        $row_data['full_name'],
        $row_data['role'],
        $row_data['password'] // Ini adalah hash password
    ];
    $sheet->fromArray($data_to_write, NULL, 'A' . $row_number);
    $row_number++;
}

// 4. Siapkan Writer untuk format XLSX
$writer = new Xlsx($spreadsheet);

// 5. Set header HTTP untuk download file Excel
$filename = "data_pengguna_export_" . date('YmdHis') . ".xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0'); // Agar browser tidak menyimpan cache

// 6. Kirim file ke browser
$writer->save('php://output');

// Tutup statement database dan koneksi
$stmt->close();
$koneksi->close();

// Hentikan eksekusi skrip setelah file dikirim.
exit();
?>