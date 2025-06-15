<?php
session_start();
include '../../includes/inc_koneksi.php'; // Pastikan file ini mendefinisikan $koneksi

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'developer', 'teknisi'])) {
    $_SESSION['error_message'] = "Anda tidak memiliki akses untuk melihat halaman manajemen materi.";
    header('Location: ../../login.php');
    exit();
}

$page_title = "Tambah Badges User";
// Ambil semua user dengan role siswa
$users = mysqli_query($koneksi, "SELECT id, full_name FROM users WHERE role = 'siswa' ORDER BY full_name");
$badges = mysqli_query($koneksi, "SELECT id, name FROM badges ORDER BY name");

// Tangani POST form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $badge_id = $_POST['badge_id'];
    $note = $_POST['note'];
    $given_by = $_SESSION['user_id'] ?? null; // sesuaikan dengan session loginmu

    $stmt = mysqli_prepare($koneksi, "INSERT INTO user_badges (user_id, badge_id, note, given_by) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "iisi", $user_id, $badge_id, $note, $given_by);
    mysqli_stmt_execute($stmt);

    header("Location: index.php");
    exit;
}
include '../dashboard_header.php';
?>
<div class="content-wrapper mb-5" style="min-width: 100%;">
    <section class="content-header">
        <h1><?= $page_title ?></h1>
        <ol class="breadcrumb gap-2">
            <li><a href="../../index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="index.php">Manajemen Badges User</a></li>
        </ol>
    </section>
    <form method="POST" class="mt-4">
        <div class="mb-3">
            <label for="user_id" class="form-label">Siswa</label>
            <select name="user_id" id="user_id" class="form-select" required>
                <option value="">-- Pilih Siswa --</option>
                <?php while ($siswa = mysqli_fetch_assoc($users)): ?>
                    <option value="<?= $siswa['id'] ?>"><?= htmlspecialchars($siswa['full_name']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="badge_id" class="form-label">Badge</label>
            <select name="badge_id" id="badge_id" class="form-select" required>
                <option value="">-- Pilih Badge --</option>
                <?php while ($badge = mysqli_fetch_assoc($badges)): ?>
                    <option value="<?= $badge['id'] ?>"><?= htmlspecialchars($badge['name']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="note" class="form-label">Catatan (opsional)</label>
            <textarea name="note" id="note" class="form-control" rows="3"></textarea>
        </div>

        <button type="submit" class="btn btn-success">Simpan</button>
        <a href="index.php" class="btn btn-secondary">Kembali</a>
    </form>
</div>

<?php require_once '../dashboard_footer.php'; ?>
