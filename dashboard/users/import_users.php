<?php
// Aktifkan pelaporan kesalahan PHP untuk debugging.
// Harap nonaktifkan ini di lingkungan produksi untuk keamanan.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Sertakan autoloader Composer untuk memuat kelas PhpSpreadsheet.
// Sesuaikan path ini jika folder 'vendor' berada di lokasi yang berbeda.
require '../../vendor/autoload.php';

// Impor kelas yang diperlukan dari PhpSpreadsheet.
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception as ReaderException;

// Sertakan file header dashboard dan koneksi database.
include '../dashboard_header.php';
include '../../includes/inc_koneksi.php';

// Cek apakah user sudah login dan memiliki role yang diizinkan (admin atau developer).
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'developer')) {
    $_SESSION['error_message'] = "Anda tidak memiliki akses untuk mengunggah data pengguna.";
    header('Location: ../../login.php'); // Arahkan ke halaman login jika tidak memiliki akses
    exit();
}

$page_title = "Unggah Data Pengguna"; // Judul halaman
$upload_message = ''; // Pesan sukses setelah upload
$upload_error = ''; // Pesan error setelah upload

// Tangani proses upload file jika formulir disubmit
if (isset($_POST['upload_csv']) && isset($_FILES['csv_file'])) {
    $file_mimes = [
        'text/x-comma-separated-values',
        'text/comma-separated-values',
        'application/octet-stream',
        'application/vnd.ms-excel', // Untuk .xls
        'application/x-csv',
        'text/x-csv',
        'text/csv',
        'application/csv',
        'application/excel',
        'application/vnd.msexcel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' // Untuk .xlsx
    ];

    $file_type = $_FILES['csv_file']['type'];
    $file_extension = pathinfo($_FILES['csv_file']['name'], PATHINFO_EXTENSION);

    // Validasi tipe file yang diunggah
    if (in_array($file_type, $file_mimes) || in_array($file_extension, ['xlsx', 'xls', 'csv'])) {
        $inputFileName = $_FILES['csv_file']['tmp_name'];

        try {
            // Deteksi tipe file secara otomatis (CSV, XLSX, XLS) dan muat spreadsheet.
            $spreadsheet = IOFactory::load($inputFileName);
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestRow();    // Baris tertinggi yang berisi data
            $highestColumn = $sheet->getHighestColumn(); // Kolom tertinggi yang berisi data (misal: 'E' untuk password)

            $imported_count = 0; // Jumlah pengguna yang berhasil diimpor
            $skipped_count = 0;  // Jumlah baris yang dilewati
            $password_default = password_hash("password123", PASSWORD_DEFAULT); // Password default yang di-hash untuk pengguna baru

            // Siapkan statement INSERT sekali di luar loop untuk efisiensi.
            // Perhatikan penambahan 'full_name' di query INSERT.
            $stmt = $koneksi->prepare("INSERT INTO users (username, email, full_name, role, password) VALUES (?, ?, ?, ?, ?)");
            if ($stmt === false) {
                $upload_error = "Gagal menyiapkan statement INSERT: " . $koneksi->error;
                error_log("IMPOR ERROR: Gagal menyiapkan statement INSERT - " . $koneksi->error);
            } else {
                // Iterasi setiap baris data, mulai dari baris ke-2 (mengabaikan header di baris pertama).
                for ($row = 2; $row <= $highestRow; ++$row) {
                    // Ambil data dari baris saat ini.
                    // 'A' . $row . ':' . $highestColumn . $row' akan mengambil semua sel dari kolom A hingga kolom terakhir.
                    $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
                        NULL, // set $nullValue to null (default)
                        TRUE, // return values as an array
                        TRUE  // format cell values (misal: tanggal diformat sebagai string)
                    );

                    // Pastikan $rowData tidak kosong dan memiliki elemen pertama (baris data).
                    if (empty($rowData) || !isset($rowData[0])) {
                        $skipped_count++;
                        error_log("IMPOR ERROR: Baris kosong atau tidak valid ditemukan. Dilewati. Baris Excel: " . $row);
                        continue; // Lanjutkan ke baris berikutnya
                    }

                    $actualRowData = $rowData[0]; // Ini adalah array yang berisi nilai sel untuk baris ini (0-indexed).

                    // Ambil data dari array $actualRowData menggunakan indeks numerik.
                    // Urutan kolom sesuai template: username (0), email (1), full_name (2), role (3), password (4)
                    $username = isset($actualRowData[0]) ? trim($actualRowData[0]) : '';
                    $email = isset($actualRowData[1]) ? trim($actualRowData[1]) : '';
                    $full_name = isset($actualRowData[2]) ? trim($actualRowData[2]) : ''; // Kolom C (full_name)
                    $role = isset($actualRowData[3]) ? strtolower(trim($actualRowData[3])) : ''; // Kolom D (role)
                    // Password dari file (kolom E) tidak digunakan untuk password default, tapi bisa diakses jika dibutuhkan.
                    // $password_from_file = isset($actualRowData[4]) ? trim($actualRowData[4]) : '';

                    $is_skipped_due_to_role = false; // Flag untuk menandai apakah baris dilewati karena batasan peran

                    // --- LOGIKA VALIDASI PERAN ---
                    // Definisikan peran yang diizinkan untuk diimpor oleh setiap role yang login.
                    $allowed_roles_for_admin = ['user', 'teknisi', 'siswa'];
                    $allowed_roles_for_developer = ['user', 'admin', 'developer', 'teknisi', 'siswa'];

                    if ($_SESSION['role'] === 'admin') {
                        // Admin hanya bisa mengunggah peran yang ada di $allowed_roles_for_admin.
                        if (!in_array($role, $allowed_roles_for_admin)) {
                            $skipped_count++;
                            error_log("IMPOR ERROR: Admin mencoba mengunggah peran '" . $role . "' yang tidak diizinkan. Baris dilewati: Username: " . $username . " (Baris Excel: " . $row . ")");
                            $is_skipped_due_to_role = true;
                        }
                    }
                    // Developer dapat mengunggah peran apa pun, jadi tidak perlu validasi tambahan di sini.
                    // Anda bisa menambahkan validasi untuk developer jika ada batasan khusus.
                    // --- AKHIR LOGIKA VALIDASI PERAN ---

                    // Validasi dasar data dan cek duplikasi hanya jika baris tidak dilewati karena batasan peran.
                    if (!$is_skipped_due_to_role) {
                        // Pastikan username, email, dan role tidak kosong, dan email valid.
                        if (!empty($username) && !empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($role)) {
                            // Cek apakah username atau email sudah ada di database (duplikasi).
                            $check_stmt = $koneksi->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
                            $check_stmt->bind_param("ss", $username, $email);
                            $check_stmt->execute();
                            $check_result = $check_stmt->get_result();

                            if ($check_result->num_rows == 0) {
                                // Jika tidak ada duplikasi, lanjutkan proses INSERT.
                                // Bind parameter sesuai urutan di query INSERT: username, email, full_name, role, password.
                                $stmt->bind_param("sssss", $username, $email, $full_name, $role, $password_default);
                                if ($stmt->execute()) {
                                    $imported_count++;
                                } else {
                                    // Tangani error saat menyisipkan data ke database.
                                    error_log("IMPOR ERROR: Gagal menyisipkan data database untuk Username: " . $username . ", Email: " . $email . ". MySQL Error: " . $stmt->error . " (Baris Excel: " . $row . ")");
                                    $skipped_count++;
                                }
                            } else {
                                // Baris dilewati karena duplikasi username atau email.
                                $skipped_count++;
                                error_log("IMPOR ERROR: Duplikasi Username atau Email ditemukan. Baris dilewati: Username: " . $username . ", Email: " . $email . " (Baris Excel: " . $row . ")");
                            }
                            $check_stmt->close(); // Tutup check_stmt setelah digunakan di setiap iterasi.
                        } else {
                            // Baris dilewati karena data tidak valid atau tidak lengkap.
                            $skipped_count++;
                            error_log("IMPOR ERROR: Data tidak valid/lengkap. Baris dilewati: Username: '" . $username . "', Email: '" . $email . "'. (Email valid: " . (filter_var($email, FILTER_VALIDATE_EMAIL) ? 'true' : 'false') . ", Username empty: " . (empty($username) ? 'true' : 'false') . ", Email empty: " . (empty($email) ? 'true' : 'false') . ", Role empty: " . (empty($role) ? 'true' : 'false') . ") (Baris Excel: " . $row . ")");
                        }
                    } // End of if (!$is_skipped_due_to_role)
                } // End of for loop
                $stmt->close(); // Tutup statement INSERT setelah semua iterasi selesai.
            }

            // Atur pesan hasil impor
            if ($imported_count > 0) {
                $upload_message = "Berhasil mengimpor " . $imported_count . " pengguna. " . $skipped_count . " baris dilewati.";
            } else if ($skipped_count > 0) {
                $upload_error = "Tidak ada pengguna yang diimpor. " . $skipped_count . " baris dilewati (mungkin duplikat, tidak valid, atau batasan peran).";
            } else {
                $upload_error = "Tidak ada data yang ditemukan di file Excel/CSV yang dapat diimpor.";
            }

        } catch (ReaderException $e) {
            // Tangani kesalahan saat membaca file Excel/CSV.
            $upload_error = "Gagal membaca file Excel/CSV: " . $e->getMessage();
            error_log("IMPOR EXCEL ERROR (ReaderException): " . $e->getMessage());
        } catch (Exception $e) {
            // Tangani kesalahan tak terduga lainnya.
            $upload_error = "Terjadi kesalahan tak terduga: " . $e->getMessage();
            error_log("IMPOR UMUM ERROR (Exception): " . $e->getMessage());
        }
    } else {
        // Pesan error jika tipe file tidak didukung.
        $upload_error = "Tipe file tidak didukung. Harap unggah file CSV, XLS, atau XLSX.";
    }
}
?>

<main class="content-wrapper">
    <section class="content-header">
        <h1><?php echo $page_title; ?></h1>
        <ol class="breadcrumb gap-2">
            <li><a href="../../index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="index.php">Manajemen Pengguna</a></li> <li class="active">Unggah Data</li>
        </ol>
    </section>

    <section class="content">
        <?php if (!empty($upload_message)): ?>
            <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                <?php echo htmlspecialchars($upload_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if (!empty($upload_error)): ?>
            <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                <?php echo htmlspecialchars($upload_error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Unggah File Data Pengguna (CSV/XLS/XLSX)</h3>
            </div>
            <div class="box-body">
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="form-group mb-3">
                        <label for="csv_file">Pilih File CSV/Excel:</label>
                        <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv, .xls, .xlsx" required>
                    </div>
                    <button type="submit" name="upload_csv" class="btn btn-primary"><i class="fa fa-upload me-1"></i> Unggah File</button>
                    <a href="index.php" class="btn btn-secondary ms-2">Batal</a> </form>
            </div>
        </div>
    </section>
</main>

<?php
// Pastikan koneksi database ditutup
$koneksi->close();
include '../dashboard_footer.php';
?>