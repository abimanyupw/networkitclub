<?php
session_start();
include '../../includes/inc_koneksi.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'developer', 'teknisi'])) {
    $_SESSION['error_message'] = "Anda tidak memiliki akses untuk menambah badge.";
    header('Location: ../../login.php');
    exit();
}
$page_title='Tambah Badge';
$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim(mysqli_real_escape_string($koneksi, $_POST['name']));
    $description = trim(mysqli_real_escape_string($koneksi, $_POST['description']));

    $image_url = '';

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
                $image_url = $new_file_name;
            } else {
                $error_message = "Gagal mengupload gambar.";
            }
        } else {
            $error_message = "Format gambar tidak didukung. Gunakan JPG, PNG, atau GIF.";
        }
    }

    if (empty($error_message)) {
        $sql = "INSERT INTO badges (name, description, image_url) VALUES ('$name', '$description', '$image_url')";
        $insert = mysqli_query($koneksi, $sql);

        if ($insert) {
            $_SESSION['success_message'] = "Badge berhasil ditambahkan.";
            header('Location: index.php');
            exit();
        } else {
            $error_message = "Gagal menyimpan data ke database: " . mysqli_error($koneksi);
        }
    }
}

// Setelah proses POST selesai, baru include header dan tampilkan form
include '../dashboard_header.php';
?>


<div class="content-wrapper mb-5" style="min-width: 100%;">
    <section class="content-header">
        <h1><?= $page_title ?></h1>
        <ol class="breadcrumb gap-2">
            <li><a href="../../index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="index.php">Manajemen Badges</a></li>
        </ol>
    </section>

    <?php if ($error_message): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="name" class="form-label">Nama Badge</label>
            <input type="text" name="name" id="name" class="form-control" required value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>">
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Deskripsi</label>
            <textarea name="description" id="description" class="form-control" rows="4"><?= isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '' ?></textarea>
        </div>
        <div class="mb-3">
            <label for="image" class="form-label">Gambar Badge (opsional)</label>
            <input type="file" name="image" id="image" class="form-control" accept="image/*">
        </div>

        <button type="submit" class="btn btn-success">Simpan</button>
        <a href="index.php" class="btn btn-secondary">Batal</a>
    </form>
</div>

<?php include '../dashboard_footer.php'; ?>

