<?php
session_start();
require_once '../../includes/inc_koneksi.php'; // sesuaikan path
require_once '../dashboard_header.php';

if (!isset($_SESSION['user_id']) || (!in_array($_SESSION['role'], ['developer', 'admin']))) {
    header('Location: ../../login.php');
    exit();
}
$page_title = "Manajemen Informasi";
$message = '';
$message_type = '';

// HANDLE DELETE
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $del_query = "DELETE FROM informasi WHERE id = $id";
    if (mysqli_query($koneksi, $del_query)) {
        $message = "Data berhasil dihapus.";
        $message_type = 'success';
    } else {
        $message = "Gagal menghapus data: " . mysqli_error($koneksi);
        $message_type = 'danger';
    }
}

// HANDLE ADD / EDIT
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $id = intval($_POST['id'] ?? 0);

    if ($title === '' || $content === '') {
        $message = "Judul dan Konten wajib diisi.";
        $message_type = 'danger';
    } else {
        $title_esc = mysqli_real_escape_string($koneksi, $title);
        $content_esc = mysqli_real_escape_string($koneksi, $content);

        if ($id > 0) {
            // Update existing
            $update_query = "UPDATE informasi SET title = '$title_esc', content = '$content_esc' WHERE id = $id";
            if (mysqli_query($koneksi, $update_query)) {
                $message = "Data berhasil diperbarui.";
                $message_type = 'success';
            } else {
                $message = "Gagal memperbarui data: " . mysqli_error($koneksi);
                $message_type = 'danger';
            }
        } else {
            // Insert new
            $insert_query = "INSERT INTO informasi (title, content) VALUES ('$title_esc', '$content_esc')";
            if (mysqli_query($koneksi, $insert_query)) {
                $message = "Data berhasil ditambahkan.";
                $message_type = 'success';
            } else {
                $message = "Gagal menambahkan data: " . mysqli_error($koneksi);
                $message_type = 'danger';
            }
        }
    }
}

// Ambil data semua informasi
$informasi_list = [];
$query = "SELECT * FROM informasi ORDER BY created_at DESC";
$result = mysqli_query($koneksi, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $informasi_list[] = $row;
    }
    mysqli_free_result($result);
}

mysqli_close($koneksi);
?>

<div class="content-wrapper mb-5" style="min-width: 100%;">
    <section class="content-header">
        <h1><?php echo $page_title; ?></h1>
        <ol class="breadcrumb gap-2">
            <li><a href="../../index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active"><?php echo $page_title; ?></li>
        </ol>
    </section>



<div class="container mt-4">
    <h1>Kelola Informasi</h1>

    <?php if ($message): ?>
        <div class="alert alert-<?= $message_type ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <!-- Form Tambah / Edit -->
    <div class="card mb-4">
        <div class="card-header">Tambah / Edit Informasi</div>
        <div class="card-body">
            <form method="POST" action="index.php" id="informasiForm">
                <input type="hidden" name="id" id="id" value="">
                <div class="mb-3">
                    <label for="title" class="form-label">Judul</label>
                    <input type="text" class="form-control" name="title" id="title" required>
                </div>
                <div class="mb-3">
                    <label for="content" class="form-label">Konten</label>
                    <textarea class="form-control" name="content" id="content" rows="4" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Simpan</button>
                <button type="button" class="btn btn-secondary" id="btnCancel">Batal</button>
            </form>
        </div>
    </div>

    <!-- Daftar Informasi -->
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>Judul</th>
                <th>Konten</th>
                <th>Tanggal Dibuat</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($informasi_list) === 0): ?>
                <tr><td colspan="4" class="text-center">Belum ada data informasi.</td></tr>
            <?php else: ?>
                <?php foreach ($informasi_list as $info): ?>
                    <tr>
                        <td><?= htmlspecialchars($info['title']) ?></td>
                        <td><?= nl2br(htmlspecialchars($info['content'])) ?></td>
                        <td><?= $info['created_at'] ?></td>
                        <td>
                            <button class="btn btn-sm btn-warning btn-edit" data-id="<?= $info['id'] ?>"
                                data-title="<?= htmlspecialchars($info['title'], ENT_QUOTES) ?>"
                                data-content="<?= htmlspecialchars($info['content'], ENT_QUOTES) ?>">Edit</button>
                            <a href="index.php?delete=<?= $info['id'] ?>" class="btn btn-sm btn-danger"
                                onclick="return confirm('Hapus data ini?')">Hapus</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
// Script untuk isi form edit
document.querySelectorAll('.btn-edit').forEach(btn => {
    btn.addEventListener('click', () => {
        const id = btn.getAttribute('data-id');
        const title = btn.getAttribute('data-title');
        const content = btn.getAttribute('data-content');

        document.getElementById('id').value = id;
        document.getElementById('title').value = title;
        document.getElementById('content').value = content;

        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
});

document.getElementById('btnCancel').addEventListener('click', () => {
    document.getElementById('informasiForm').reset();
    document.getElementById('id').value = '';
});
</script>

<?php require_once '../dashboard_footer.php'; ?>
