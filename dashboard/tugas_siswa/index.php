<?php
// user/assignments/index.php
session_start();
require_once '../../includes/inc_koneksi.php';
require_once '../dashboard_header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'siswa') {
    header("Location: ../../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$page_title = "Tugas Saya";

$search_query = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10;
$offset = ($current_page - 1) * $limit;

$count_query = "
SELECT COUNT(*) as total
FROM assignments a
LEFT JOIN classes c ON a.kelas_id = c.id
LEFT JOIN assignment_submissions s ON a.id = s.assignment_id AND s.user_id = $user_id
WHERE a.title LIKE '%$search_query%' OR c.name LIKE '%$search_query%'
";
$total_data = mysqli_fetch_assoc(mysqli_query($koneksi, $count_query))['total'];
$total_pages = ceil($total_data / $limit);

$query = "
SELECT a.id, a.title, a.deadline, a.description, c.name AS kelas,
       s.status, s.file_path
FROM assignments a
LEFT JOIN classes c ON a.kelas_id = c.id
LEFT JOIN assignment_submissions s ON a.id = s.assignment_id AND s.user_id = $user_id
WHERE a.title LIKE '%$search_query%' OR c.name LIKE '%$search_query%'
ORDER BY a.deadline ASC
LIMIT $limit OFFSET $offset
";
$result = mysqli_query($koneksi, $query);
?>

<div class="content-wrapper mb-5" style="min-width: 100%;">
  <section class="content-header">
    <h1><?= $page_title ?></h1>
    <ol class="breadcrumb gap-2">
      <li><a href="../../index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
      <li class="active">Tugas</li>
    </ol>
  </section>

  <div class="container mt-4">
    <div class="box-header with-border">
      <h3 class="box-title">Daftar Tugas</h3>
      <div class="box-tools pull-right d-flex align-items-center">
        <form action="" method="GET" class="search-form me-3 m-3 h-100">
          <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Cari tugas atau kelas..." value="<?= htmlspecialchars($search_query) ?>">
            <div class="input-group-btn">
              <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <table class="table table-bordered table-striped mt-2">
      <thead>
        <tr>
          <th>Judul</th>
          <th>Kelas</th>
          <th>Deadline</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (mysqli_num_rows($result) > 0): ?>
          <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
              <td><?= htmlspecialchars($row['title']) ?></td>
              <td><?= htmlspecialchars($row['kelas'] ?? '-') ?></td>
              <td><?= htmlspecialchars($row['deadline']) ?></td>
              <td>
                <?php
                $status = $row['status'] ?? 'belum';
                $badge = match ($status) {
                  'pending' => 'warning',
                  'approved' => 'success',
                  'rejected' => 'danger',
                  default => 'secondary'
                };
                ?>
                <span class="badge bg-<?= $badge ?>"><?= ucfirst($status) ?></span>
              </td>
              <td>
                <?php if (!$row['status']): ?>
                  <a href="submit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">Kumpulkan</a>
                <?php elseif ($row['file_path']): ?>
                  <a href="../../uploads/<?= $row['file_path'] ?>" target="_blank" class="btn btn-sm btn-info">Lihat File</a>
                <?php else: ?>
                  <span class="text-muted">-</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="5" class="text-center">Tidak ada tugas ditemukan.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>

    <!-- Pagination -->
    <nav aria-label="Page navigation">
      <ul class="pagination justify-content-center mt-4">
        <?php if ($current_page > 1): ?>
          <li class="page-item">
            <a class="page-link" href="?page=<?= $current_page - 1 ?>&search=<?= urlencode($search_query) ?>">&laquo;</a>
          </li>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
          <li class="page-item <?= $i == $current_page ? 'active' : '' ?>">
            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search_query) ?>"><?= $i ?></a>
          </li>
        <?php endfor; ?>

        <?php if ($current_page < $total_pages): ?>
          <li class="page-item">
            <a class="page-link" href="?page=<?= $current_page + 1 ?>&search=<?= urlencode($search_query) ?>">&raquo;</a>
          </li>
        <?php endif; ?>
      </ul>
    </nav>
  </div>
</div>

<?php require_once '../dashboard_footer.php'; ?>
