<?php
ob_start();
session_start();
include '../dashboard_header.php';
include '../../includes/inc_koneksi.php';

// Akses kontrol
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'developer'])) {
    $_SESSION['error_message'] = "Anda tidak memiliki akses.";
    header('Location: ../../login.php');
    exit();
}

$page_title = "Tambah Kelas Pembelajaran Baru";
$error_message = '';
$success_message = '';

$name = '';
$description = '';
$image_url = '';

// Proses form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $koneksi->real_escape_string($_POST['name'] ?? '');
    $description = $koneksi->real_escape_string($_POST['description'] ?? '');
    $created_by = $_SESSION['user_id'];

    // Proses upload gambar
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $upload_dir = '../../uploads/class_images/';
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if (in_array($file_ext, $allowed_extensions)) {
            $new_file_name = 'class_' . uniqid() . '.' . $file_ext;
            $destination = $upload_dir . $new_file_name;

            if (move_uploaded_file($file_tmp, $destination)) {
                $image_url = 'uploads/class_images/' . $new_file_name;
            } else {
                $error_message = "Gagal mengunggah gambar.";
            }
        } else {
            $error_message = "Format gambar tidak didukung (hanya jpg, jpeg, png, gif).";
        }
    }

    // Validasi dan insert
    if (empty($name)) {
        $error_message = "Nama Kelas wajib diisi.";
    } elseif (empty($error_message)) {
        $sql = "INSERT INTO classes (name, description, image_url, created_by) VALUES (?, ?, ?, ?)";
        if ($stmt = $koneksi->prepare($sql)) {
            $stmt->bind_param("sssi", $name, $description, $image_url, $created_by);
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Kelas '$name' berhasil ditambahkan!";
                header('Location: index.php');
                exit();
            } else {
                $error_message = "Gagal menyimpan data: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error_message = "Kesalahan query: " . $koneksi->error;
        }
    }
}
?>

<div class="content-wrapper mb-5">
    <section class="content-header">
        <h1><?php echo $page_title; ?></h1>
        <ol class="breadcrumb gap-2">
            <li><a href="../../index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="index.php">Manajemen Kelas</a></li>
            <li class="active"><?php echo $page_title; ?></li>
        </ol>
    </section>

    <section class="content">
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Form Tambah Kelas</h3>
            </div>
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="box-body">
                    <div class="form-group">
                        <label for="name">Nama Kelas <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Deskripsi</label>
                        <textarea class="form-control" name="description" rows="4"><?php echo htmlspecialchars($description); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="image">Upload Gambar Kelas</label>
                        <input type="file" name="image" class="form-control">
                        <small class="form-text text-muted">Hanya JPG, JPEG, PNG, atau GIF.</small>
                    </div>
                </div>

                <div class="box-footer">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="index.php" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </section>
</div>

<?php
$koneksi->close();
include '../dashboard_footer.php';
?>
