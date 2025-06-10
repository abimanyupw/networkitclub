<?php
// dashboard/assignments/edit.php
session_start();
require_once '../../includes/inc_koneksi.php';
require_once '../dashboard_header.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'developer', 'teknisi'])) {
    $_SESSION['error_message'] = "Akses ditolak.";
    header("Location: ../../login.php");
    exit();
}

$page_title = "Edit Tugas";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "ID tidak valid.";
    header("Location: index.php");
    exit();
}

$id = (int) $_GET['id'];
$data = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM assignments WHERE id = $id"));
if (!$data) {
    $_SESSION['error_message'] = "Tugas tidak ditemukan.";
    header("Location: index.php");
    exit();
}

$kelas_result = mysqli_query($koneksi, "SELECT id, name FROM classes ORDER BY name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $deadline = $_POST['deadline'];
    $kelas_id = $_POST['kelas_id'] ?: null;

    $stmt = mysqli_prepare($koneksi, "UPDATE assignments SET title = ?, description = ?, deadline = ?, kelas_id = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "sssii", $title, $description, $deadline, $kelas_id, $id);
    mysqli_stmt_execute($stmt);

    $_SESSION['success_message'] = "Tugas berhasil diperbarui.";
    header("Location: index.php");
    exit();
}
?>

<div class="content-wrapper mb-5" style="min-width: 100%;">
    <section class="content-header">
        <h1><?= $page_title ?></h1>
        <ol class="breadcrumb gap-2">
            <li><a href="../../index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="index.php">Manajemen Tugas</a></li>
            <li class="active">Edit</li>
        </ol>
    </section>

    <div class="container mt-4">
        <form method="POST">
            <div class="mb-3">
                <label for="title" class="form-label">Judul Tugas</label>
                <input type="text" class="form-control" name="title" id="title" value="<?= htmlspecialchars($data['title']) ?>" required>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Deskripsi</label>
                <textarea name="description" id="description" class="form-control" rows="4"><?= htmlspecialchars($data['description']) ?></textarea>
            </div>

            <div class="mb-3">
                <label for="kelas_id" class="form-label">Kelas</label>
                <select name="kelas_id" id="kelas_id" class="form-select">
                    <option value="">-- Pilih Kelas --</option>
                    <?php while ($kelas = mysqli_fetch_assoc($kelas_result)): ?>
                        <option value="<?= $kelas['id'] ?>" <?= $kelas['id'] == $data['kelas_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($kelas['name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="deadline" class="form-label">Deadline</label>
                <input type="datetime-local" class="form-control" name="deadline" id="deadline" value="<?= date('Y-m-d\TH:i', strtotime($data['deadline'])) ?>" required>
            </div>

            <button type="submit" class="btn btn-success">Simpan Perubahan</button>
            <a href="index.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>

<?php require_once '../dashboard_footer.php'; ?>