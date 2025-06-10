<?php
// dashboard/user_badges/edit.php
session_start();
require_once '../../includes/inc_koneksi.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'developer', 'teknisi'])) {
    $_SESSION['error_message'] = "Akses ditolak.";
    header("Location: ../../login.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "ID tidak valid.";
    header("Location: index.php");
    exit();
}

$id = intval($_GET['id']);

// Ambil data lama
$query = "SELECT * FROM user_badges WHERE id = $id";
$result = mysqli_query($koneksi, $query);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    $_SESSION['error_message'] = "Data tidak ditemukan.";
    header("Location: index.php");
    exit();
}

// Ambil pilihan badge & siswa
$users = mysqli_query($koneksi, "SELECT id, full_name FROM users WHERE role = 'siswa' ORDER BY full_name");
$badges = mysqli_query($koneksi, "SELECT id, name FROM badges ORDER BY name");

// Tangani update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $badge_id = $_POST['badge_id'];
    $note = $_POST['note'];

    $stmt = mysqli_prepare($koneksi, "UPDATE user_badges SET user_id = ?, badge_id = ?, note = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "iisi", $user_id, $badge_id, $note, $id);
    mysqli_stmt_execute($stmt);

    $_SESSION['success_message'] = "Data berhasil diperbarui.";
    header("Location: index.php");
    exit();
}

require_once '../dashboard_header.php';
?>

<div class="container mt-5">
    <h2>Edit Badge Siswa</h2>
    <form method="POST" class="mt-4">
        <div class="mb-3">
            <label for="user_id" class="form-label">Siswa</label>
            <select name="user_id" id="user_id" class="form-select" required>
                <option value="">-- Pilih Siswa --</option>
                <?php while ($siswa = mysqli_fetch_assoc($users)): ?>
                    <option value="<?= $siswa['id'] ?>" <?= $siswa['id'] == $data['user_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($siswa['full_name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="badge_id" class="form-label">Badge</label>
            <select name="badge_id" id="badge_id" class="form-select" required>
                <option value="">-- Pilih Badge --</option>
                <?php while ($badge = mysqli_fetch_assoc($badges)): ?>
                    <option value="<?= $badge['id'] ?>" <?= $badge['id'] == $data['badge_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($badge['name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="note" class="form-label">Catatan</label>
            <textarea name="note" id="note" class="form-control" rows="3"><?= htmlspecialchars($data['note']) ?></textarea>
        </div>

        <button type="submit" class="btn btn-success">Simpan Perubahan</button>
        <a href="index.php" class="btn btn-secondary">Kembali</a>
    </form>
</div>

<?php require_once '../dashboard_footer.php'; ?>
