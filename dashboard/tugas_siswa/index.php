<?php
session_start();
require_once '../../includes/inc_koneksi.php'; // Pastikan path ini benar
require_once '../dashboard_header.php'; // Pastikan path ini benar

// Cek apakah user sudah login dan memiliki role 'siswa'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'siswa') {
    $_SESSION['error_message'] = "Anda tidak memiliki akses untuk melihat tugas.";
    header("Location: ../../login.php");
    exit();
}

$user_id = $_SESSION['user_id']; // Ambil ID user dari sesi
$page_title = "Tugas Saya"; // Judul halaman

// Ambil parameter pencarian dari URL
$search_query = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';

// Pengaturan paginasi
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10; // Jumlah tugas per halaman
$offset = ($current_page - 1) * $limit; // Offset untuk query database

// Query untuk menghitung total data tugas yang cocok dengan pencarian
$count_query = "
SELECT COUNT(*) as total
FROM assignments a
LEFT JOIN classes c ON a.kelas_id = c.id
WHERE a.title LIKE '%$search_query%' OR c.name LIKE '%$search_query%'
";
$total_data = mysqli_fetch_assoc(mysqli_query($koneksi, $count_query))['total'];
$total_pages = ceil($total_data / $limit); // Total halaman yang dibutuhkan

// Query utama untuk mengambil data tugas dan status pengumpulan user
$query = "
SELECT a.id, a.title, a.deadline, a.description, c.name AS kelas,
       s.status, s.file_path, s.submitted_at
FROM assignments a
LEFT JOIN classes c ON a.kelas_id = c.id
LEFT JOIN assignment_submissions s ON a.id = s.assignment_id AND s.user_id = $user_id
WHERE a.title LIKE '%$search_query%' OR c.name LIKE '%$search_query%'
ORDER BY a.deadline ASC
LIMIT $limit OFFSET $offset
";
$result = mysqli_query($koneksi, $query);

// Handle error jika query gagal
if (!$result) {
    echo "Error: " . mysqli_error($koneksi);
    exit();
}
?>

<div class="content-wrapper">
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
        <form action="" method="GET" class="search-form h-100 mb-2">
          <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Cari tugas atau kelas..." value="<?= htmlspecialchars($search_query) ?>">
            <div class="input-group-btn">
              <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
            </div>
          </div>
        </form>
      </div>
    </div>
    <div class="table-responsive">
      <table class="table table-bordered table-striped table-primary mt-2">
        <thead>
          <tr>
            <th>No</th>
            <th>Judul</th>
            <th>Kelas</th>
            <th>Deadline</th>
            <th>Status</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
           <?php if (mysqli_num_rows($result) > 0): $no = $offset + 1; ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
              <tr>
                 <td><?= $no++ ?></td>
                <td><?= htmlspecialchars($row['title']) ?></td>
                <td><?= htmlspecialchars($row['kelas'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['deadline']) ?></td>
                <td>
                  <?php
                  $status = $row['status'] ?? 'belum'; // Default status jika belum ada pengumpulan
                  $badge = match ($status) {
                    'pending' => 'warning',
                    'approved' => 'success',
                    'rejected' => 'danger',
                    default => 'secondary' // Untuk 'belum'
                  };
                  ?>
                  <span class="badge bg-<?= $badge ?>"><?= ucfirst($status) ?></span>
                </td>
                <td>
                  <a href="detail.php?id=<?= htmlspecialchars($row['id']) ?>" class="btn btn-sm btn-info me-1">Detail</a> <?php if (!$row['status']): // Jika belum ada status, berarti belum dikumpulkan ?>
                    <a href="submit.php?id=<?= htmlspecialchars($row['id']) ?>" class="btn btn-sm btn-primary">Kumpulkan</a>
                  <?php elseif ($row['file_path']): // Jika sudah ada status dan ada file path (sudah mengumpulkan) ?>
                    <a href="../../uploads/<?= htmlspecialchars($row['file_path']) ?>" target="_blank" class="btn btn-sm btn-secondary">Lihat Berkas</a>
                  <?php else: // Jika sudah ada status tapi tidak ada file path (misal status pending tanpa file, atau error) ?>
                    <span class="text-muted">Aksi tidak tersedia</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="5" class="text-center">Tidak ada tugas ditemukan.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

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

<?php 
// Tutup koneksi database
// Asumsi $koneksi tidak ditutup di dashboard_footer.php
if (isset($koneksi) && is_object($koneksi) && $koneksi->ping()) {
    $koneksi->close();
}
require_once '../dashboard_footer.php'; 
?>