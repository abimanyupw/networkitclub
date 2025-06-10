<?php
session_start();
include '../../includes/inc_koneksi.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'developer', 'teknisi'])) {
    $_SESSION['error_message'] = "Anda tidak memiliki akses untuk mengedit badge.";
    header('Location: ../../login.php');
    exit();
}

$error_message = '';
$success_message = '';

// Ambil id badge dari query string
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "ID badge tidak valid.";
    header('Location: index.php');
    exit();
}

$badge_id = (int) $_GET['id'];

// Ambil data badge berdasarkan id
$sql = "SELECT * FROM badges WHERE id = $badge_id";
$result = mysqli_query($koneksi, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    $_SESSION['error_message'] = "Badge tidak ditemukan.";
    header('Location: index.php');
    exit();
}

$badge = mysqli_fetch_assoc($result);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim(mysqli_real_escape_string($koneksi, $_POST['name']));
    $description = trim(mysqli_real_escape_string($koneksi, $_POST['description']));
    $current_image = $badge['image_url'];  // gambar lama

    $image_url = $current_image;

    // Jika upload gambar baru
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_name = basename($_FILES['image']['name']);
        $file_type = mime_content_type($file_tmp);

        if (in_array($file_type, $allowed_types)) {
            $upload_dir = '../../uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $new_file_name = uniqid('badge_') . '_' . $file_name;
            $target_file = $upload_dir . $new_file_name;

            if (move_uploaded_file($file_tmp, $target_file)) {
                // Hapus file gambar lama jika ada
                if (!empty($current_image) && file_exists($upload_dir . $current_image)) {
                    unlink($upload_dir . $current_image);
                }
                $image_url = $new_file_name;
            } else {
                $error_message = "Gagal mengupload gambar.";
            }
        } else {
            $error_message = "Format gambar tidak didukung. Gunakan JPG, PNG, atau GIF.";
        }
    }

    if (empty($error_message)) {
        $sql_update = "UPDATE badges SET name = '$name', description = '$description', image_url = '$image_url' WHERE id = $badge_id";
        $update = mysqli_query($koneksi, $sql_update);

        if ($update) {
            $_SESSION['success_message'] = "Badge berhasil diupdate.";
            header('Location: index.php');
            exit();
        } else {
            $error_message = "Gagal memperbarui data: " . mysqli_error($koneksi);
        }
    }
}

include '../dashboard_header.php';
?>

<div class="container mt-5">
    <h2>Edit Badge</h2>

    <?php if ($error_message): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="name" class="form-label">Nama Badge</label>
            <input type="text" name="name" id="name" class="form-control" required value="<?= htmlspecialchars($badge['name']) ?>">
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Deskripsi</label>
            <textarea name="description" id="description" class="form-control" rows="4"><?= htmlspecialchars($badge['description']) ?></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Gambar Badge Saat Ini</label><br>
            <?php if (!empty($badge['image_url'])): ?>
                <img src="../../uploads/<?= htmlspecialchars($badge['image_url']) ?>" alt="Badge Image" width="100">
            <?php else: ?>
                <span class="text-muted">(Tidak ada gambar)</span>
            <?php endif; ?>
        </div>
        <div class="mb-3">
            <label for="image" class="form-label">Ganti Gambar Badge (opsional)</label>
            <input type="file" name="image" id="image" class="form-control" accept="image/*">
        </div>

        <button type="submit" class="btn btn-primary">Update</button>
        <a href="index.php" class="btn btn-secondary">Batal</a>
    </form>
</div>

<?php include '../dashboard_footer.php'; ?>
