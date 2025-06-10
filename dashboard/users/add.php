<?php
session_start();
// Pastikan dashboard_header.php berada di level yang benar
// Jika file ini di 'dashboard/manage_data/users/add_user.php'
// Maka 'dashboard_header.php' ada di 'dashboard/'
// Jadi '../dashboard_header.php' adalah path yang benar
// Path diperbaiki
include '../../includes/inc_koneksi.php'; // Path ke koneksi database

// --- Logika Otorisasi: Hanya Admin dan Developer yang Diizinkan ---
// Perbaikan pada logika otorisasi untuk memastikan hanya admin atau developer yang bisa mengakses
if (!isset($_SESSION['user_id']) || (!($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'developer'))) {
    // Simpan pesan error di sesi
    $_SESSION['error_message'] = "Anda tidak memiliki akses untuk melihat halaman ini.";
    // Redirect ke halaman login
    header('Location:../../login.php'); // Sesuaikan path jika login.php tidak di root
    exit();
}

$page_title = "Tambah Pengguna Baru"; // Atur judul halaman secara spesifik

$error_message = '';
$username = '';
$email = '';
$full_name = ''; // Tambahkan inisialisasi untuk full_name
$role = ''; // Inisialisasi variabel untuk mempertahankan nilai di form

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']); // Gunakan trim untuk menghapus spasi
    $email = trim($_POST['email']);
    $full_name = trim($_POST['full_name']); // Ambil data full_name dari POST
    $password = $_POST['password']; // Password tidak perlu trim karena akan di-hash
    $role = $_POST['role'];

    // Validasi input
    if (empty($username) || empty($email) || empty($password) || empty($role) || empty($full_name)) { // full_name ditambahkan ke validasi
        $error_message = "Semua field harus diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Format email tidak valid.";
    } elseif (strlen($password) < 6) { // Contoh validasi panjang password
        $error_message = "Password minimal 6 karakter.";
    } else {
        // Cek apakah username atau email sudah ada di database
        $check_sql = "SELECT id FROM users WHERE username = ? OR email = ?";
        $check_stmt = $koneksi->prepare($check_sql);
        if ($check_stmt) {
            $check_stmt->bind_param("ss", $username, $email);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();

            if ($check_result->num_rows > 0) {
                $error_message = "Username atau Email sudah terdaftar. Silakan gunakan yang lain.";
            } else {
                // Hash password sebelum menyimpan ke database
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Query untuk memasukkan pengguna baru, menyertakan full_name
                $sql = "INSERT INTO users (username, email, password, full_name, role) VALUES (?, ?, ?, ?, ?)";
                $stmt = $koneksi->prepare($sql);
                if ($stmt) {
                    // Perhatikan 'sssss' karena ada 5 parameter string
                    $stmt->bind_param("sssss", $username, $email, $hashed_password, $full_name, $role);

                    if ($stmt->execute()) {
                        $_SESSION['success_message'] = "Pengguna <b>" . htmlspecialchars($username) . "</b> berhasil ditambahkan.";
                        header('Location: index.php'); // Redirect ke halaman daftar pengguna
                        exit();
                    } else {
                        $error_message = "Gagal menambahkan pengguna: " . $stmt->error;
                    }
                    $stmt->close();
                } else {
                    $error_message = "Gagal menyiapkan query insert: " . $koneksi->error;
                }
            }
            $check_stmt->close();
        } else {
            $error_message = "Gagal menyiapkan query cek: " . $koneksi->error;
        }
    }
}
include '../dashboard_header.php'; 
?>

<main class="content-wrapper">
    <section class="content-header">
        <h1>Tambah Pengguna</h1>
        <ol class="breadcrumb gap-2">
            <li><a href="../../index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="index.php">Manajemen Pengguna</a></li>
            <li class="active">Tambah Pengguna</li>
        </ol>
    </section>

    <section class="content">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Form Tambah Pengguna Baru</h3>
            </div>
            <div class="box-body">
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                        <?php echo $error_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form action="" method="POST">
                    <div class="form-group mb-3">
                        <label for="username">Username:</label>
                        <input type="text" class="form-control" id="username" name="username" required value="<?php echo htmlspecialchars($username); ?>">
                    </div>
                    <div class="form-group mb-3">
                        <label for="full_name">Nama Lengkap:</label> <input type="text" class="form-control" id="full_name" name="full_name" required value="<?php echo htmlspecialchars($full_name); ?>">
                    </div>
                    <div class="form-group mb-3">
                        <label for="email">Email:</label>
                        <input type="email" class="form-control" id="email" name="email" required value="<?php echo htmlspecialchars($email); ?>">
                    </div>
                    <div class="form-group mb-3">
                        <label for="password">Password:</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="role">Role:</label>
                        <select class="form-control" id="role" name="role" required>
                            <option value="">Pilih Role</option>
                            <option value="admin" <?php echo ($role == 'admin') ? 'selected' : ''; ?>>Admin</option>
                            <?php
                            // Opsi developer hanya muncul jika user yang login adalah developer
                            if ($_SESSION['role'] === 'developer'):
                            ?>
                                <option value="developer" <?php echo ($role == 'developer') ? 'selected' : ''; ?>>Developer</option>
                            <?php endif; ?>
                            <option value="siswa" <?php echo ($role == 'siswa') ? 'selected' : ''; ?>>Siswa</option>
                            <option value="teknisi" <?php echo ($role == 'teknisi') ? 'selected' : ''; ?>>Teknisi</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success">Tambah Pengguna</button>
                    <a href="index.php" class="btn btn-default">Batal</a>
                </form>
            </div>
        </div>
    </section>
</main>

<?php
$koneksi->close();
include '../dashboard_footer.php';
?>