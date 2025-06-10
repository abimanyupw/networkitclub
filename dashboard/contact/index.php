<?php
session_start();
require_once '../../includes/inc_koneksi.php';
require_once '../dashboard_header.php';

if (!isset($_SESSION['user_id']) || (!in_array($_SESSION['role'], ['developer', 'admin']))) {
    header('Location: ../../login.php');
    exit();
}

$page_title = 'Kelola Halaman Contact';
$message = '';
$message_type = '';

// Default nilai
$contact_content = [
    'map_embed' => '',
    'contact_heading' => ''
];

// Ambil data dari database
$query = "SELECT map_embed, contact_heading FROM contact_content WHERE id = 1";
$result = mysqli_query($koneksi, $query);

if ($result) {
    $data = mysqli_fetch_assoc($result);
    if ($data) {
        $contact_content['map_embed'] = $data['map_embed'];
        $contact_content['contact_heading'] = $data['contact_heading'];
    }
    mysqli_free_result($result);
} else {
    $message = 'Gagal mengambil data: ' . mysqli_error($koneksi);
    $message_type = 'danger';
}

// Proses simpan jika ada post
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $map_embed = trim($_POST['map_embed'] ?? '');
    $contact_heading = trim($_POST['contact_heading'] ?? '');

    if (empty($map_embed) || empty($contact_heading)) {
        $message = 'Semua field harus diisi.';
        $message_type = 'danger';
    } else {
        $map_embed_escaped = mysqli_real_escape_string($koneksi, $map_embed);
        $contact_heading_escaped = mysqli_real_escape_string($koneksi, $contact_heading);

        $update = "UPDATE contact_content SET map_embed = '$map_embed_escaped', contact_heading = '$contact_heading_escaped' WHERE id = 1";
        if (mysqli_query($koneksi, $update)) {
            $message = 'Konten Contact berhasil diperbarui.';
            $message_type = 'success';

            // Perbarui nilai tampil di form juga
            $contact_content['map_embed'] = $map_embed;
            $contact_content['contact_heading'] = $contact_heading;
        } else {
            $message = 'Gagal memperbarui data: ' . mysqli_error($koneksi);
            $message_type = 'danger';
        }
    }
}
?>

<div class="container py-4">
    <h1 class="mb-4"><?= $page_title ?></h1>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= $message_type ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card border-light shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Edit Contact Section</h5>
        </div>
        <div class="card-body">
            <form action="index.php" method="POST">
                <div class="mb-3">
                    <label for="contact_heading" class="form-label">Judul Bagian Kontak</label>
                    <input type="text" class="form-control" id="contact_heading" name="contact_heading"
                        value="<?= htmlspecialchars($contact_content['contact_heading']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="map_embed" class="form-label">Embed Google Maps (iframe)</label>
                    <textarea class="form-control" id="map_embed" name="map_embed" rows="5" required><?= htmlspecialchars($contact_content['map_embed']) ?></textarea>
                    <div class="form-text">Masukkan kode <code>&lt;iframe&gt;</code> dari Google Maps dan juga beri class="map" pada embednya.</div>
                </div>
                <button type="submit" class="btn btn-success">Simpan Perubahan</button>
            </form>
        </div>
    </div>
</div>

<?php require_once '../dashboard_footer.php'; ?>
