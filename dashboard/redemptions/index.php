<?php
// dashboard/redemptions/index.php
session_start();
require_once '../../includes/inc_koneksi.php';


if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'developer', 'teknisi'])) {
    header("Location: ../../login.php");
    exit();
}

$page_title = "Permintaan Penukaran";
$status_filter = $_GET['status'] ?? '';

// Ambil data penukaran
$query = "SELECT r.*, u.full_name, s.name AS item_name
          FROM redemptions r
          JOIN users u ON r.user_id = u.id
          JOIN store_items s ON r.item_id = s.id";
if (in_array($status_filter, ['pending', 'approved', 'rejected'])) {
    $query .= " WHERE r.status = '" . mysqli_real_escape_string($koneksi, $status_filter) . "'";
}
$query .= " ORDER BY r.created_at DESC";
$result = mysqli_query($koneksi, $query);
require_once '../dashboard_header.php';
?>

<div class="content-wrapper mb-5">
    <section class="content-header">
        <h1><?= $page_title ?></h1>
        <ol class="breadcrumb gap-2">
            <li><a href="../../index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">Redemptions</li>
        </ol>
    </section>

    <div class="container mt-4">
        <form method="GET" class="d-flex mb-3">
            <select name="status" class="form-select me-2" onchange="this.form.submit()">
                <option value="">-- Semua Status --</option>
                <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="approved" <?= $status_filter === 'approved' ? 'selected' : '' ?>>Approved</option>
                <option value="rejected" <?= $status_filter === 'rejected' ? 'selected' : '' ?>>Rejected</option>
            </select>
        </form>
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-primary">
                <thead>
                    <tr>
                        <th>Nama Siswa</th>
                        <th>Barang</th>
                        <th>Status</th>
                        <th>Waktu</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['full_name']) ?></td>
                                <td><?= htmlspecialchars($row['item_name']) ?></td>
                                <td><?= ucfirst($row['status']) ?></td>
                                <td><?= $row['created_at'] ?></td>
                                <td>
                                    <?php if ($row['status'] === 'pending'): ?>
                                        <a href="process.php?id=<?= $row['id'] ?>&action=approve" class="btn btn-sm btn-success" onclick="return confirm('Setujui penukaran ini?')">Terima</a>
                                        <a href="process.php?id=<?= $row['id'] ?>&action=reject" class="btn btn-sm btn-danger" onclick="return confirm('Tolak penukaran ini?')">Tolak</a>
                                    <?php else: ?>
                                        <span class="text-muted">Tidak ada aksi</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center">Tidak ada data.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../dashboard_footer.php'; ?>
