<?php
session_start();

include '../../includes/inc_koneksi.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'developer'])) {
    $_SESSION['error_message'] = "Anda tidak memiliki akses untuk mengedit kelas.";
    header('Location: ../../login.php');
    exit();
}

$page_title = "Edit Kelas Pembelajaran";
$error_message = '';
$success_message = '';

$class_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($class_id === 0) {
    $_SESSION['error_message'] = "ID kelas tidak valid.";
    header('Location: index.php');
    exit();
}

// Ambil data kelas
$sql_get_class = "SELECT id, name, description, image_url FROM classes WHERE id = ?";
$stmt_get_class = $koneksi->prepare($sql_get_class);
if ($stmt_get_class) {
    $stmt_get_class->bind_param("i", $class_id);
    $stmt_get_class->execute();
    $result_get_class = $stmt_get_class->get_result();
    if ($result_get_class->num_rows === 0) {
        $_SESSION['error_message'] = "Kelas tidak ditemukan.";
        header('Location: index.php');
        exit();
    }
    $class_data = $result_get_class->fetch_assoc();
    $stmt_get_class->close();

    $name = $class_data['name'];
    $description = $class_data['description'];
    $image_url = $class_data['image_url'];
} else {
    $error_message = "Gagal menyiapkan statement: " . $koneksi->error;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $koneksi->real_escape_string($_POST['name'] ?? '');
    $description = $koneksi->real_escape_string($_POST['description'] ?? '');
    $image_url = $class_data['image_url']; // Gunakan default lama

    // Proses upload gambar jika ada
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

    if (empty($name)) {
        $error_message = "Nama Kelas wajib diisi.";
    }

    if (empty($error_message)) {
        $sql_update = "UPDATE classes SET name = ?, description = ?, image_url = ? WHERE id = ?";
        if ($stmt = $koneksi->prepare($sql_update)) {
            $stmt->bind_param("sssi", $name, $description, $image_url, $class_id);
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Kelas " . htmlspecialchars($name) . " berhasil diperbarui!";
                header('Location: index.php');
                exit();
            } else {
                $error_message = "Gagal memperbarui kelas: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error_message = "Gagal menyiapkan statement: " . $koneksi->error;
        }
    }
}
include '../dashboard_header.php';
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
            <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                <?php echo htmlspecialchars($success_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                <?php echo htmlspecialchars($error_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Form Edit Kelas</h3>
            </div>
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="box-body">
                    <div class="form-group">
                        <label for="name">Nama Kelas <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Deskripsi</label>
                        <textarea class="form-control" id="description" name="description" rows="5"><?php echo htmlspecialchars($description); ?></textarea>
                    </div>

                    <?php if (!empty($image_url)): ?>
                        <div class="form-group">
                            <label>Gambar Saat Ini:</label><br>
                            <img src="../../<?php echo htmlspecialchars($image_url); ?>" alt="Gambar Kelas" style="max-width: 200px; margin-bottom: 10px;">
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="image">Upload Gambar Baru (Opsional)</label>
                        <input type="file" name="image" class="form-control">
                        <small class="form-text text-muted">Kosongkan jika tidak ingin mengubah gambar. Hanya JPG, JPEG, PNG, GIF.</small>
                    </div>
                </div>

                <div class="box-footer">
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <a href="index.php" class="btn btn-default">Batal</a>
                </div>
            </form>
        </div>
    </section>
</div>

<?php
$koneksi->close();
include '../dashboard_footer.php';
?>
