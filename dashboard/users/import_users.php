<?php
// dashboard/users/import_users.php
// Aktifkan pelaporan kesalahan PHP untuk debugging.
// Harap nonaktifkan ini di lingkungan produksi untuk keamanan.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Sertakan autoloader Composer untuk memuat kelas PhpSpreadsheet.
require '../../vendor/autoload.php';

// Impor kelas yang diperlukan dari PhpSpreadsheet.
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception as ReaderException;

// Sertakan file header dashboard dan koneksi database.
include '../dashboard_header.php'; // Pastikan path ini benar
include '../../includes/inc_koneksi.php'; // Pastikan path ini benar

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
            $highestColumn = $sheet->getHighestColumn(); // Kolom tertinggi yang berisi data

            $imported_count = 0; // Jumlah pengguna yang berhasil diimpor
            $skipped_count = 0;  // Jumlah baris yang dilewati
            
            // Siapkan statement INSERT sekali di luar loop untuk efisiensi.
            // Perhatikan penambahan 'jurusan' dan 'kelas' di query INSERT
            $stmt = $koneksi->prepare("INSERT INTO users (username, email, full_name, role, password, jurusan, kelas) VALUES (?, ?, ?, ?, ?, ?, ?)");
            if ($stmt === false) {
                $upload_error = "Gagal menyiapkan statement INSERT: " . $koneksi->error;
                error_log("IMPOR ERROR: Gagal menyiapkan statement INSERT - " . $koneksi->error);
            } else {
                // Iterasi setiap baris data, mulai dari baris ke-2 (mengabaikan header di baris pertama).
                for ($row = 2; $row <= $highestRow; ++$row) {
                    // Ambil data dari baris saat ini.
                    $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
                        NULL, // set $nullValue to null (default)
                        TRUE, // return values as an array
                        TRUE  // format cell values
                    );

                    // Pastikan $rowData tidak kosong dan memiliki elemen pertama (baris data).
                    if (empty($rowData) || !isset($rowData[0])) {
                        $skipped_count++;
                        error_log("IMPOR ERROR: Baris kosong atau tidak valid ditemukan. Dilewati. Baris Excel: " . $row);
                        continue; // Lanjutkan ke baris berikutnya
                    }

                    $actualRowData = $rowData[0]; // Ini adalah array yang berisi nilai sel untuk baris ini (0-indexed).

                    // Ambil data dari array $actualRowData menggunakan indeks numerik.
                    // Asumsi urutan kolom di Excel:
                    // A: username (0), B: email (1), C: full_name (2), D: role (3), E: password (4), F: jurusan (5), G: kelas (6)
                    $username = isset($actualRowData[0]) ? trim($actualRowData[0]) : '';
                    $email = isset($actualRowData[1]) ? trim($actualRowData[1]) : '';
                    $full_name = isset($actualRowData[2]) ? trim($actualRowData[2]) : ''; 
                    $role = isset($actualRowData[3]) ? strtolower(trim($actualRowData[3])) : ''; 
                    $password_from_file = isset($actualRowData[4]) ? trim($actualRowData[4]) : ''; // <<< Password dari Kolom E
                    $jurusan = isset($actualRowData[5]) ? trim($actualRowData[5]) : ''; // <<< Jurusan dari Kolom F
                    $kelas = isset($actualRowData[6]) ? trim($actualRowData[6]) : '';     // <<< Kelas dari Kolom G

                    $is_skipped_due_to_role = false; // Flag untuk menandai apakah baris dilewati karena batasan peran

                    // --- LOGIKA VALIDASI PERAN ---
                    $allowed_roles_for_admin = ['user', 'teknisi', 'siswa']; // Admin bisa mengimpor ini
                    // Developer dapat mengunggah peran apa pun kecuali admin jika diinginkan (tergantung kebijakan)
                    // Jika login role developer, maka semua role boleh diimpor, jadi tidak ada batasan khusus.
                    if ($_SESSION['role'] === 'admin') {
                        if (!in_array($role, $allowed_roles_for_admin)) {
                            $skipped_count++;
                            error_log("IMPOR ERROR: Admin mencoba mengunggah peran '" . $role . "' yang tidak diizinkan. Baris dilewati: Username: " . $username . " (Baris Excel: " . $row . ")");
                            $is_skipped_due_to_role = true;
                        }
                    }
                    // --- AKHIR LOGIKA VALIDASI PERAN ---

                    // Validasi dasar data dan cek duplikasi hanya jika baris tidak dilewati karena batasan peran.
                    // Password, Username, Email, Role WAJIB ada dan valid. Jurusan dan Kelas opsional di sini.
                    if (!$is_skipped_due_to_role) {
                        if (!empty($username) && !empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($role) && !empty($password_from_file)) { 
                            // Cek apakah username atau email sudah ada di database (duplikasi).
                            $check_stmt = $koneksi->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
                            if ($check_stmt === false) {
                                error_log("IMPOR ERROR: Gagal menyiapkan check_stmt duplikasi - " . $koneksi->error);
                                $skipped_count++;
                                continue; // Lanjutkan ke baris berikutnya
                            }
                            $check_stmt->bind_param("ss", $username, $email);
                            $check_stmt->execute();
                            $check_result = $check_stmt->get_result();

                            if ($check_result->num_rows == 0) {
                                // Jika tidak ada duplikasi, lanjutkan proses INSERT.
                                $hashed_password = password_hash($password_from_file, PASSWORD_DEFAULT); 

                                // Bind parameter sesuai urutan di query INSERT: username, email, full_name, role, password, jurusan, kelas
                                $stmt->bind_param("sssssss", $username, $email, $full_name, $role, $hashed_password, $jurusan, $kelas); 
                                if ($stmt->execute()) {
                                    $imported_count++;
                                } else {
                                    error_log("IMPOR ERROR: Gagal menyisipkan data database untuk Username: " . $username . ", Email: " . $email . ". MySQL Error: " . $stmt->error . " (Baris Excel: " . $row . ")");
                                    $skipped_count++;
                                }
                            } else {
                                // Baris dilewati karena duplikasi username atau email.
                                $skipped_count++;
                                error_log("IMPOR ERROR: Duplikasi Username atau Email ditemukan. Baris dilewati: Username: " . $username . ", Email: " . $email . " (Baris Excel: " . $row . ")");
                            }
                            $check_stmt->close(); 
                        } else {
                            // Baris dilewati karena data tidak valid atau tidak lengkap.
                            $skipped_count++;
                            error_log("IMPOR ERROR: Data tidak valid/lengkap (username/email/role/password kosong/email tidak valid). Baris dilewati: Username: '" . $username . "', Email: '" . $email . "'. (Email valid: " . (filter_var($email, FILTER_VALIDATE_EMAIL) ? 'true' : 'false') . ", Username empty: " . (empty($username) ? 'true' : 'false') . ", Email empty: " . (empty($email) ? 'true' : 'false') . ", Role empty: " . (empty($role) ? 'true' : 'false') . ", Password empty: " . (empty($password_from_file) ? 'true' : 'false') . ") (Baris Excel: " . $row . ")");
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
            $upload_error = "Gagal membaca file Excel/CSV: " . $e->getMessage();
            error_log("IMPOR EXCEL ERROR (ReaderException): " . $e->getMessage());
        } catch (Exception $e) {
            $upload_error = "Terjadi kesalahan tak terduga: " . $e->getMessage();
            error_log("IMPOR UMUM ERROR (Exception): " . $e->getMessage());
        }
    } else {
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
if (isset($koneksi) && is_object($koneksi) && $koneksi->ping()) { 
    $koneksi->close();
}
include '../dashboard_footer.php'; // Sertakan footer dashboard
?>