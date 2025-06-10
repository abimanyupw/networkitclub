<?php
// dashboard/categories/add.php

session_start();

include '../dashboard_header.php';
include '../../includes/inc_koneksi.php';

// Cek apakah user sudah login dan memiliki role admin/developer/teknisi
if (!isset($_SESSION['user_id']) || (!($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'developer' || $_SESSION['role'] === 'teknisi'))) {
    $_SESSION['error_message'] = "Anda tidak memiliki akses untuk menambah kategori.";
    header('Location: index.php'); // Arahkan kembali ke halaman manajemen kategori
    exit();
}

$page_title = "Tambah Kategori Baru";
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($name)) {
        $error_message = "Nama kategori tidak boleh kosong.";
    } else {
        // Cek duplikasi nama kategori
        $stmt = $koneksi->prepare("SELECT COUNT(id) FROM categories WHERE name = ?");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            $error_message = "Nama kategori '$name' sudah ada.";
        } else {
            $stmt = $koneksi->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
            if ($stmt === false) {
                $error_message = "Gagal menyiapkan statement: " . $koneksi->error;
            } else {
                $stmt->bind_param("ss", $name, $description);

                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "Kategori '$name' berhasil ditambahkan.";
                    header('Location: index.php');
                    exit();
                } else {
                    $error_message = "Gagal menambahkan kategori: " . $stmt->error;
                }
                $stmt->close();
            }
        }
    }
}
?>

<main class="content-wrapper">
    <section class="content-header">
        <h1><?php echo $page_title; ?></h1>
        <ol class="breadcrumb gap-2">
            <li><a href="../../index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="index.php">Manajemen Kategori Materi</a></li>
            <li class="active">Tambah Kategori</li>
        </ol>
    </section>

    <section class="content">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Form Tambah Kategori</h3>
            </div>
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                <div class="box-body">
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($error_message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <div class="form-group mb-3">
                        <label for="name">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="description">Deskripsi</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($description ?? ''); ?></textarea>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save me-1"></i> Simpan</button>
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