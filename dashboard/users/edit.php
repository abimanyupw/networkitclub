<?php
ob_start();
session_start();
// Pastikan dashboard_header.php berada di level yang benar
// Jika file ini di 'dashboard/manage_data/users/edit_user.php'
// Maka 'dashboard_header.php' ada di 'dashboard/'
// Jadi '../dashboard_header.php' adalah path yang benar

include '../../includes/inc_koneksi.php';

// --- Logika Otorisasi: Hanya Admin dan Developer yang Diizinkan ---
// PERBAIKAN LOGIKA OTORISASI
if (!isset($_SESSION['user_id']) || (!($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'developer'))) {
    $_SESSION['error_message'] = "Anda tidak memiliki akses untuk mengedit pengguna.";
    header('Location: ../../login.php'); // Sesuaikan path jika login.php tidak di root
    exit();
}

$page_title = "Edit Pengguna"; // Atur judul halaman secara spesifik

$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error_message = '';
$user_data = null; // Inisialisasi null

// Ambil data pengguna jika ID valid
if ($user_id > 0) {
    // TAMBAHKAN full_name dalam query SELECT
    $sql_fetch = "SELECT id, username, email, full_name, role FROM users WHERE id = ?";
    $stmt_fetch = $koneksi->prepare($sql_fetch);

    if ($stmt_fetch) {
        $stmt_fetch->bind_param("i", $user_id);
        $stmt_fetch->execute();
        $result_fetch = $stmt_fetch->get_result();

        if ($result_fetch->num_rows > 0) {
            $user_data = $result_fetch->fetch_assoc();

            // Pencegahan: Admin tidak bisa mengedit akun developer
            // Dan tidak bisa mengedit akun sendiri dengan hak yang lebih tinggi (jika ada)
            if ($_SESSION['role'] === 'admin' && $user_data['role'] === 'developer') {
                $_SESSION['error_message'] = "Admin tidak dapat mengedit akun Developer.";
                header('Location: index.php');
                exit();
            }
            // Pencegahan: User tidak bisa mengedit dirinya sendiri atau user lain dengan role yang lebih tinggi (developer/admin)
            // Hanya developer yang bisa mengedit user lain yang developer
            if ($_SESSION['user_id'] == $user_data['id'] && $_SESSION['role'] === 'developer' && $user_data['role'] === 'developer') {
                // Developer bisa mengedit akun developer miliknya sendiri
            } else if ($_SESSION['user_id'] == $user_data['id'] && $_SESSION['role'] === 'admin' && $user_data['role'] === 'admin') {
                // Admin bisa mengedit akun admin miliknya sendiri
            } else if ($_SESSION['user_id'] == $user_data['id'] && $_SESSION['role'] !== $user_data['role'] ) {
                 // Misal, admin mencoba edit akun user, atau user mencoba edit akun admin.
                 // Ini secara umum diatur oleh kondisi otorisasi di awal.
                 // Batasan lebih lanjut: Jika user yang login adalah 'user' dan mencoba mengedit
                 // dirinya sendiri atau orang lain, ini perlu penanganan khusus.
                 // Untuk kasus ini, asumsi hanya admin/developer yang bisa akses halaman ini.
            } else if ($_SESSION['user_id'] != $user_data['id'] && $_SESSION['role'] === 'admin' && $user_data['role'] === 'admin') {
                // Admin tidak bisa mengedit admin lain
                 $_SESSION['error_message'] = "Admin tidak dapat mengedit akun Admin lain.";
                 header('Location: index.php');
                 exit();
            }

        } else {
            $_SESSION['error_message'] = "Pengguna dengan ID tersebut tidak ditemukan.";
            header('Location: index.php');
            exit();
        }
        $stmt_fetch->close();
    } else {
        $error_message = "Gagal menyiapkan query ambil data pengguna: " . $koneksi->error;
    }
} else {
    $_SESSION['error_message'] = "ID Pengguna tidak valid. Mohon berikan ID yang benar.";
    header('Location: index.php'); // Redirect kembali ke daftar pengguna
    exit();
}

// Logika POST untuk memperbarui data
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $user_data) { // Pastikan $user_data ada sebelum memproses POST
    $username = trim($_POST['username']);
    $full_name = trim($_POST['full_name']); // Ambil full_name dari POST
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $password = $_POST['password']; // Password bisa kosong jika tidak diubah

    // Validasi input
    if (empty($username) || empty($email) || empty($role) || empty($full_name)) { // full_name ditambahkan
        $error_message = "Username, Email, Nama Lengkap, dan Role harus diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Format email tidak valid.";
    } elseif (!empty($password) && strlen($password) < 6) { // Hanya validasi panjang jika password diisi
        $error_message = "Password minimal 6 karakter jika diubah.";
    } else {
        // Pencegahan: Admin tidak bisa mengubah role ke developer
        if ($_SESSION['role'] === 'admin' && $role === 'developer') {
            $error_message = "Anda tidak memiliki izin untuk mengatur role ke Developer.";
        }
        // Pencegahan: Developer tidak bisa mengubah role pengguna lain menjadi developer
        // Dan tidak bisa mengubah role dirinya sendiri menjadi non-developer jika dia developer
        if ($_SESSION['role'] === 'developer' && $user_data['id'] !== $_SESSION['user_id'] && $role === 'developer') {
             $error_message = "Developer tidak dapat mengubah role pengguna lain menjadi Developer.";
        }
        // Jika admin mencoba mengubah role developer, atau developer mengubah role developer lain
        // Ini ditangani oleh cek awal, tapi bisa diperkuat di sini
        if (empty($error_message)) { // Lanjutkan hanya jika tidak ada error otorisasi role
            // Cek apakah username atau email sudah ada di pengguna lain (kecuali pengguna yang sedang diedit)
            $check_sql = "SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?";
            $check_stmt = $koneksi->prepare($check_sql);
            if ($check_stmt) {
                $check_stmt->bind_param("ssi", $username, $email, $user_id);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();

                if ($check_result->num_rows > 0) {
                    $error_message = "Username atau Email sudah digunakan oleh pengguna lain.";
                } else {
                    // Bangun query UPDATE secara dinamis
                    // TAMBAHKAN full_name dalam UPDATE
                    $update_sql = "UPDATE users SET username = ?, email = ?, full_name = ?, role = ?";
                    $params = "ssss"; // ssss untuk username, email, full_name, role
                    $param_values = [$username, $email, $full_name, $role];

                    if (!empty($password)) {
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        $update_sql .= ", password = ?";
                        $params .= "s"; // s untuk password
                        $param_values[] = $hashed_password;
                    }
                    $update_sql .= " WHERE id = ?";
                    $params .= "i"; // i untuk id
                    $param_values[] = $user_id;

                   $stmt_update = $koneksi->prepare($update_sql);
if ($stmt_update) {
    function refValues($arr) {
        $refs = [];
        foreach ($arr as $key => $value) {
            $refs[$key] = &$arr[$key];
        }
        return $refs;
    }
    call_user_func_array([$stmt_update, 'bind_param'], refValues(array_merge([$params], $param_values)));


                        if ($stmt_update->execute()) {
                            $_SESSION['success_message'] = "Pengguna <b>" . htmlspecialchars($username) . "</b> berhasil diperbarui.";
                            header('Location: index.php');
                            exit();
                        } else {
                            $error_message = "Gagal memperbarui pengguna: " . $stmt_update->error;
                        }
                        $stmt_update->close();
                    } else {
                        $error_message = "Gagal menyiapkan query update: " . $koneksi->error;
                    }
                }
                $check_stmt->close();
            } else {
                $error_message = "Gagal menyiapkan query cek duplikasi: " . $koneksi->error;
            }
        }
    }
    // Jika ada error pada POST, update user_data agar nilai form tetap terisi
    // Ini penting jika pengguna memasukkan data tidak valid dan form perlu ditampilkan lagi
    $user_data['username'] = $username;
    $user_data['email'] = $email;
    $user_data['full_name'] = $full_name; // Pastikan ini juga di-update
    $user_data['role'] = $role;
    // Password tidak disimpan kembali karena sudah dihash atau kosong
}
include '../dashboard_header.php';
?>

<main class="content-wrapper">
    <section class="content-header">
        <h1>Edit Pengguna</h1>
        <ol class="breadcrumb">
            <li><a href="../../index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="index.php">Manajemen Pengguna</a></li>
            <li class="active">Edit Pengguna</li>
        </ol>
    </section>

    <section class="content">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Form Edit Pengguna</h3>
            </div>
            <div class="box-body">
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                        <?php echo htmlspecialchars($error_message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if ($user_data): // Pastikan data pengguna ditemukan sebelum menampilkan form ?>
                    <form action="" method="POST">
                        <div class="form-group mb-3">
                            <label for="username">Username:</label>
                            <input type="text" class="form-control" id="username" name="username" required value="<?php echo htmlspecialchars($user_data['username']); ?>">
                        </div>
                        <div class="form-group mb-3">
                            <label for="full_name">Nama Lengkap:</label> <input type="text" class="form-control" id="full_name" name="full_name" required value="<?php echo htmlspecialchars($user_data['full_name']); ?>">
                        </div>
                        <div class="form-group mb-3">
                            <label for="email">Email:</label>
                            <input type="email" class="form-control" id="email" name="email" required value="<?php echo htmlspecialchars($user_data['email']); ?>">
                        </div>
                        <div class="form-group mb-3">
                            <label for="password">Password (kosongkan jika tidak ingin diubah):</label>
                            <input type="password" class="form-control" id="password" name="password">
                            <small class="form-text text-muted">Biarkan kosong jika tidak ingin mengubah password.</small>
                        </div>
                        <div class="form-group mb-4">
                            <label for="role">Role:</label>
                            <select class="form-control" id="role" name="role" required>
                                <option value="admin" <?php echo ($user_data['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                                <?php
                                // Opsi developer hanya muncul jika user yang login adalah developer
                                // Dan jika user yang diedit bukan developer (admin tidak bisa mengubah developer)
                                // atau jika user yang diedit adalah developer dan user yang login adalah developer
                                if ($_SESSION['role'] === 'developer' || ($user_data['role'] === 'developer' && $_SESSION['role'] === 'developer')) :
                                ?>
                                    <option value="developer" <?php echo ($user_data['role'] == 'developer') ? 'selected' : ''; ?>>Developer</option>
                                <?php endif; ?>
                                
                                <option value="siswa" <?php echo ($user_data['role'] == 'siswa') ? 'selected' : ''; ?>>Siswa</option>
                                <option value="teknisi" <?php echo ($user_data['role'] == 'teknisi') ? 'selected' : ''; ?>>Teknisi</option>
                            </select>
                            <?php
                            // Tambahkan pesan peringatan jika admin mencoba mengedit developer
                            if ($_SESSION['role'] === 'admin' && $user_data['role'] === 'developer'):
                            ?>
                                <small class="form-text text-danger">Admin tidak dapat mengubah role Developer.</small>
                            <?php endif; ?>
                            <?php
                            // Tambahkan pesan peringatan jika admin mencoba mengedit admin lain
                            if ($_SESSION['role'] === 'admin' && $user_data['role'] === 'admin' && $user_data['id'] !== $_SESSION['user_id']):
                            ?>
                                <small class="form-text text-danger">Admin tidak dapat mengubah role Admin lain.</small>
                            <?php endif; ?>
                        </div>
                        <button type="submit" class="btn btn-success me-2">Perbarui Pengguna</button>
                        <a href="index.php" class="btn btn-default">Batal</a>
                    </form>
                <?php else: ?>
                    <div class="alert alert-warning" role="alert">
                        Data pengguna tidak dapat dimuat. Silakan kembali ke daftar pengguna.
                    </div>
                    <a href="index.php" class="btn btn-primary">Kembali ke Daftar Pengguna</a>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>

<?php
$koneksi->close();
include '../dashboard_footer.php';
?>