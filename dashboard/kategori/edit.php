<?php
// dashboard/categories/edit.php

session_start();

include '../dashboard_header.php';
include '../../includes/inc_koneksi.php';

// Cek apakah user sudah login dan memiliki role admin/developer/teknisi
if (!isset($_SESSION['user_id']) || (!($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'developer' || $_SESSION['role'] === 'teknisi'))) {
    $_SESSION['error_message'] = "Anda tidak memiliki akses untuk mengedit kategori.";
    header('Location: index.php');
    exit();
}

$page_title = "Edit Kategori";
$error_message = '';
$category_data = null;
$category_id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT);

// Ambil data kategori untuk mengisi form
if ($category_id) {
    $stmt = $koneksi->prepare("SELECT id, name, description FROM categories WHERE id = ?");
    if ($stmt === false) {
        $error_message = "Gagal menyiapkan statement pengambilan data: " . $koneksi->error;
    } else {
        $stmt->bind_param("i", $category_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $category_data = $result->fetch_assoc();
        } else {
            $_SESSION['error_message'] = "Kategori tidak ditemukan.";
            header('Location: index.php');
            exit();
        }
        $stmt->close();
    }
} else {
    $_SESSION['error_message'] = "ID kategori tidak valid.";
    header('Location: index.php');
    exit();
}

// Proses update form jika ada POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updated_id = filter_var($_POST['category_id'] ?? '', FILTER_VALIDATE_INT);
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($updated_id) || $updated_id !== $category_id) {
        $error_message = "ID kategori tidak valid untuk pembaruan.";
    } elseif (empty($name)) {
        $error_message = "Nama kategori tidak boleh kosong.";
    } else {
        // Cek duplikasi nama kategori (kecuali kategori yang sedang diedit)
        $stmt = $koneksi->prepare("SELECT COUNT(id) FROM categories WHERE name = ? AND id != ?");
        $stmt->bind_param("si", $name, $updated_id);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            $error_message = "Nama kategori '$name' sudah digunakan oleh kategori lain.";
        } else {
            $stmt = $koneksi->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
            if ($stmt === false) {
                $error_message = "Gagal menyiapkan statement update: " . $koneksi->error;
            } else {
                $stmt->bind_param("ssi", $name, $description, $updated_id);

                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "Kategori '$name' berhasil diperbarui.";
                    header('Location: index.php');
                    exit();
                } else {
                    $error_message = "Gagal memperbarui kategori: " . $stmt->error;
                }
                $stmt->close();
            }
        }
    }
    // Update category_data with current form values if there's an error
    $category_data['name'] = $name;
    $category_data['description'] = $description;
}
?>

<main class="content-wrapper">
    <section class="content-header">
        <h1><?php echo $page_title; ?></h1>
        <ol class="breadcrumb gap-2">
            <li><a href="../../index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="index.php">Manajemen Kategori Materi</a></li>
            <li class="active">Edit Kategori</li>
        </ol>
    </section>

    <section class="content">
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title">Form Edit Kategori</h3>
            </div>
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?id=' . $category_id); ?>" method="POST">
                <div class="box-body">
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($error_message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <input type="hidden" name="category_id" value="<?php echo htmlspecialchars($category_data['id']); ?>">
                    <div class="form-group mb-3">
                        <label for="name">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($category_data['name'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="description">Deskripsi</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($category_data['description'] ?? ''); ?></textarea>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save me-1"></i> Simpan Perubahan</button>
                    <a href="index.php" class="btn btn-default">Batal</a>
                </div>
            </form>
        </div>
    </section>
</main>

<?php
$koneksi->close();
include '../dashboard_footer.php';
?>