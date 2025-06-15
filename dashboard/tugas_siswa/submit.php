<?php
// user/assignments/submit.php
session_start();
require_once '../../includes/inc_koneksi.php';
require_once '../dashboard_header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'siswa') {
    header("Location: ../../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "ID tugas tidak valid.";
    header("Location: index.php");
    exit();
}

$assignment_id = (int)$_GET['id'];
$assignment = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM assignments WHERE id = $assignment_id"));
if (!$assignment) {
    $_SESSION['error_message'] = "Tugas tidak ditemukan.";
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['file']['tmp_name'];
        $file_name = time() . '_' . basename($_FILES['file']['name']);
        $file_path = '../../uploads/' . $file_name;

        if (move_uploaded_file($file_tmp, $file_path)) {
            $stmt = mysqli_prepare($koneksi, "INSERT INTO assignment_submissions (assignment_id, user_id, file_path, status) VALUES (?, ?, ?, 'pending')");
            mysqli_stmt_bind_param($stmt, "iis", $assignment_id, $user_id, $file_name);
            mysqli_stmt_execute($stmt);

            $_SESSION['success_message'] = "Tugas berhasil dikumpulkan.";
            header("Location: index.php");
            exit();
        } else {
            $error = "Gagal mengunggah file.";
        }
    } else {
        $error = "File tidak valid.";
    }
}
?>

<div class="content-wrapper mb-5">
    <section class="content-header">
        <h1>Kumpulkan Tugas</h1>
        <ol class="breadcrumb">
            <li><a href="../../index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="index.php">Tugas</a></li>
            <li class="active">Kumpulkan</li>
        </ol>
    </section>

    <div class="container mt-4">
        <h4><?= htmlspecialchars($assignment['title']) ?></h4>
        <p><strong>Deadline:</strong> <?= htmlspecialchars($assignment['deadline']) ?></p>
        <p><?= nl2br(($assignment['description'])) ?></p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="file" class="form-label">File Tugas (PDF, DOCX, ZIP, dll)</label>
                <input type="file" class="form-control" name="file" id="file" required>
            </div>
            <button type="submit" class="btn btn-success">Kumpulkan</button>
            <a href="index.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>

<?php require_once '../dashboard_footer.php'; ?>
