<?php
// dashboard/settings/index.php
session_start();
require_once '../../includes/inc_koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$message_type = '';

// Data ENUM untuk dropdown Jurusan dan Kelas
$jurusan_options = [
    'TEKNIK KOMPUTER DAN JARINGAN',
    'TEKNIK PENGEMBANGAN PERANGKAT LUNAK',
    'MULTIMEDIA'
];
$kelas_options = [
    'X',
    'XI',
    'XII'
];

// Fungsi untuk mendapatkan opsi ENUM dari database
function getEnumOptions($koneksi, $table, $column) {
    $options = [];
    $query = "SHOW COLUMNS FROM `$table` LIKE '$column'";
    $result = mysqli_query($koneksi, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $enum_string = $row['Type']; 
        preg_match_all("/'([^']+)'/", $enum_string, $matches);
        if (!empty($matches[1])) {
            $options = $matches[1];
        }
    } else {
        error_log("Error: Could not get ENUM options for $table.$column - " . mysqli_error($koneksi));
    }
    return $options;
}

// Dapatkan opsi untuk Jurusan secara dinamis
$jurusan_options = getEnumOptions($koneksi, 'users', 'jurusan');

// Dapatkan opsi untuk Kelas secara dinamis
$kelas_options = getEnumOptions($koneksi, 'users', 'kelas');

// Ambil data user, termasuk jurusan, kelas, dan phone_number
$query = $koneksi->prepare("SELECT username, email, full_name, photo, role, jurusan, kelas, phone_number FROM users WHERE id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
if ($result->num_rows === 0) {
    echo "User tidak ditemukan.";
    exit();
}
$user = $result->fetch_assoc();
$query->close();

$page_title = 'Pengaturan Profil'; 
require_once '../dashboard_header.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $jurusan = trim($_POST['jurusan'] ?? '');   
    $kelas = trim($_POST['kelas'] ?? '');       
    $phone_number = trim($_POST['phone_number'] ?? ''); 

    // Validasi dasar
    if ($username === '' || $full_name === '' || $email === '' || $phone_number === '') {
        $message = "Username, Nama Lengkap, Email, dan Nomor HP wajib diisi.";
        $message_type = 'danger';
    } 
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Format email tidak valid.";
        $message_type = 'danger';
    }
    elseif (!preg_match('/^[0-9\s\-\(\)\+]{10,20}$/', $phone_number)) {
        $message = "Format Nomor HP tidak valid.";
        $message_type = 'danger';
    }
    else {
        // Cek username sudah digunakan oleh orang lain
        $stmt_check_username = $koneksi->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $stmt_check_username->bind_param("si", $username, $user_id);
        $stmt_check_username->execute();
        $stmt_check_username->store_result();
        if ($stmt_check_username->num_rows > 0) {
            $message = "Username sudah digunakan oleh pengguna lain.";
            $message_type = 'danger';
            $stmt_check_username->close();
        } else {
            $stmt_check_username->close(); 

            $photo_filename = $user['photo']; 
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                if (!in_array($_FILES['photo']['type'], $allowed_types)) {
                    $message = "Format foto tidak didukung. Harap gunakan JPG, PNG, atau GIF.";
                    $message_type = 'danger';
                } else {
                    $upload_dir = '../../uploads/profile_photos/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
                    $new_name = 'user_' . $user_id . '_' . time() . '.' . $ext;
                    $destination = $upload_dir . $new_name;

                    if (move_uploaded_file($_FILES['photo']['tmp_name'], $destination)) {
                        if ($photo_filename && $photo_filename !== 'default.png' && file_exists($upload_dir . $photo_filename)) {
                            unlink($upload_dir . $photo_filename);
                        }
                        $photo_filename = $new_name; 
                    } else {
                        $message = "Gagal mengunggah foto.";
                        $message_type = 'danger';
                    }
                }
            }

            if ($message_type !== 'danger') {
                $update_query_parts = [];
                $bind_types = "";
                $bind_params_values = []; // Array untuk menyimpan nilai parameter

                $update_query_parts[] = "username = ?";
                $bind_types .= "s";
                $bind_params_values[] = $username;

                $update_query_parts[] = "full_name = ?";
                $bind_types .= "s";
                $bind_params_values[] = $full_name;

                $update_query_parts[] = "email = ?";
                $bind_types .= "s";
                $bind_params_values[] = $email;

                $update_query_parts[] = "jurusan = ?";
                $bind_types .= "s";
                $bind_params_values[] = $jurusan; 

                $update_query_parts[] = "kelas = ?";
                $bind_types .= "s";
                $bind_params_values[] = $kelas;   

                $update_query_parts[] = "phone_number = ?";
                $bind_types .= "s";
                $bind_params_values[] = $phone_number;

                if ($password !== '') {
                    $password_hashed = password_hash($password, PASSWORD_DEFAULT);
                    $update_query_parts[] = "password = ?";
                    $bind_types .= "s";
                    $bind_params_values[] = $password_hashed;
                }

                $update_query_parts[] = "photo = ?";
                $bind_types .= "s";
                $bind_params_values[] = $photo_filename;
                
                $sql_update = "UPDATE users SET " . implode(", ", $update_query_parts) . " WHERE id = ?";
                $bind_types .= "i"; 
                $bind_params_values[] = $user_id; 

                $stmt = $koneksi->prepare($sql_update);
                if (!$stmt) {
                    error_log("Failed to prepare UPDATE statement for profile: " . $koneksi->error);
                    $message = "Terjadi kesalahan sistem saat menyiapkan pembaruan profil.";
                    $message_type = 'danger';
                } else {
                    // --- PERBAIKAN DI SINI: MENCIPTAKAN ARRAY REFERENSI ---
                    $bind_params_refs = [];
                    $bind_params_refs[] = $bind_types; // Tipe string selalu pass by value
                    foreach ($bind_params_values as $key => $value) {
                        $bind_params_refs[] = &$bind_params_values[$key]; // Variabel harus pass by reference
                    }
                    // --- AKHIR PERBAIKAN ---

                    // Menggunakan call_user_func_array untuk memanggil bind_param dengan array referensi
                    call_user_func_array([$stmt, 'bind_param'], $bind_params_refs);

                    if ($stmt->execute()) {
                        // Perbarui sesi dengan data terbaru
                        $_SESSION['username'] = $username;
                        $_SESSION['full_name'] = $full_name;
                        $_SESSION['photo'] = $photo_filename;
                        $_SESSION['jurusan'] = $jurusan;
                        $_SESSION['kelas'] = $kelas;
                        $_SESSION['phone_number'] = $phone_number;
                        
                        $_SESSION['success_message'] = "Profil berhasil diperbarui.";
                        header("Location: index.php");
                        exit();
                    } else {
                        $message = "Gagal memperbarui profil: " . $stmt->error;
                        $message_type = 'danger';
                    }
                    $stmt->close();
                }
            }
        }
    }
}
?>

<div class="container mt-4">
    <h1><?= $page_title ?></h1>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['success_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php elseif ($message): ?>
        <div class="alert alert-<?= $message_type ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <?php if ($user['photo'] && file_exists('../../uploads/profile_photos/' . $user['photo'])): ?>
                <img src="../../uploads/profile_photos/<?= htmlspecialchars($user['photo']) ?>?v=<?= time() ?>" alt="Foto Profil" class="img-thumbnail" style="max-width:150px; display:block; margin-bottom:10px;">
            <?php else: ?>
                <p>Tidak ada foto profil</p>
                <img src="../../uploads/profile_photos/default.png?v=<?= time() ?>" alt="Default Foto Profil" class="img-thumbnail" style="max-width:150px; display:block; margin-bottom:10px;">
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label for="photo" class="form-label">Foto Profil (JPG, PNG, GIF)</label>
            <input type="file" name="photo" id="photo" class="form-control" accept="image/*">
        </div>

        <div class="mb-3">
            <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
            <input type="text" name="username" id="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="full_name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
            <input type="text" name="full_name" id="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
            <input type="email" name="email" id="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="jurusan" class="form-label">Jurusan</label>
            <select name="jurusan" id="jurusan" class="form-control">
                <option value="">-- Pilih Jurusan --</option>
                <?php foreach ($jurusan_options as $option): ?>
                    <option value="<?= htmlspecialchars($option) ?>" <?= ($user['jurusan'] ?? '') == $option ? 'selected' : '' ?>>
                        <?= htmlspecialchars($option) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="kelas" class="form-label">Kelas</label>
            <select name="kelas" id="kelas" class="form-control">
                <option value="">-- Pilih Kelas --</option>
                <?php foreach ($kelas_options as $option): ?>
                    <option value="<?= htmlspecialchars($option) ?>" <?= ($user['kelas'] ?? '') == $option ? 'selected' : '' ?>>
                        <?= htmlspecialchars($option) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="phone_number" class="form-label">Nomor HP / WhatsApp <span class="text-danger">*</span></label>
            <input type="text" name="phone_number" id="phone_number" class="form-control" value="<?= htmlspecialchars($user['phone_number'] ?? '') ?>" placeholder="Cth: 081234567890" required>
            <div class="form-text">Mohon pastikan nomor HP aktif untuk komunikasi.</div>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password Baru (kosongkan jika tidak diubah)</label>
            <input type="password" name="password" id="password" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
    </form>
</div>

<?php require_once '../dashboard_footer.php'; ?>