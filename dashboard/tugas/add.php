<?php
// dashboard/assignments/add.php
session_start();
require_once '../../includes/inc_koneksi.php';


if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'developer', 'teknisi'])) {
    $_SESSION['error_message'] = "Akses ditolak.";
    header("Location: ../../login.php");
    exit();
}

$page_title = "Tambah Tugas";
$kelas_result = mysqli_query($koneksi, "SELECT id, name FROM classes ORDER BY name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $deadline = $_POST['deadline'];
    $kelas_id = $_POST['kelas_id'] ?: null;
    $created_by = $_SESSION['user_id'];

    $stmt = mysqli_prepare($koneksi, "INSERT INTO assignments (title, description, deadline, kelas_id, created_by) VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sssii", $title, $description, $deadline, $kelas_id, $created_by);
    mysqli_stmt_execute($stmt);

    $_SESSION['success_message'] = "Tugas berhasil ditambahkan.";
    header("Location: index.php");
    exit();
}
require_once '../dashboard_header.php';
?>

<div class="content-wrapper mb-5" style="min-width: 100%;">
    <section class="content-header">
        <h1><?= $page_title ?></h1>
        <ol class="breadcrumb gap-2">
            <li><a href="../../index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="index.php">Manajemen Tugas</a></li>
            <li class="active">Tambah</li>
        </ol>
    </section>

    <div class="container mt-4">
        <form method="POST">
            <div class="mb-3">
                <label for="title" class="form-label">Judul Tugas</label>
                <input type="text" class="form-control" name="title" id="title" required>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Deskripsi</label>
                <textarea name="description" id="description" class="form-control" rows="4"></textarea>
            </div>

            <div class="mb-3">
                <label for="kelas_id" class="form-label">Kelas</label>
                <select name="kelas_id" id="kelas_id" class="form-select">
                    <option value="">-- Pilih Kelas --</option>
                    <?php while ($kelas = mysqli_fetch_assoc($kelas_result)): ?>
                        <option value="<?= $kelas['id'] ?>"><?= htmlspecialchars($kelas['name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="deadline" class="form-label">Deadline</label>
                <input type="datetime-local" class="form-control" name="deadline" id="deadline" required>
            </div>

            <button type="submit" class="btn btn-success">Simpan</button>
            <a href="index.php" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</div>

<?php require_once '../dashboard_footer.php'; ?>