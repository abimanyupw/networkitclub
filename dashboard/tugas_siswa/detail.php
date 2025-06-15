<?php
session_start();

// Aktifkan pelaporan kesalahan PHP untuk debugging.
// Harap nonaktifkan ini di lingkungan produksi untuk keamanan.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../includes/inc_koneksi.php'; // Memuat koneksi database
require_once '../dashboard_header.php'; // Memuat header dashboard

// Cek apakah user sudah login dan memiliki role 'siswa'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'siswa') {
    $_SESSION['error_message'] = "Anda tidak memiliki akses untuk melihat detail tugas.";
    header("Location: ../../login.php");
    exit();
}

$user_id = $_SESSION['user_id']; // Ambil ID user dari sesi
$page_title = "Detail Tugas"; // Judul halaman
$assignment_data = null; // Variabel untuk menyimpan data tugas yang diambil

// Ambil ID tugas dari parameter GET di URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $assignment_id = intval($_GET['id']); // Pastikan ID adalah integer

    // Query untuk mengambil detail tugas dan status pengumpulan user untuk tugas ini
    $query = "
    SELECT a.id, a.title, a.deadline, a.description, c.name AS kelas_name,
           s.status AS submission_status, s.file_path AS submission_file_path, s.submitted_at
    FROM assignments a
    LEFT JOIN classes c ON a.kelas_id = c.id
    LEFT JOIN assignment_submissions s ON a.id = s.assignment_id AND s.user_id = ?
    WHERE a.id = ?
    LIMIT 1"; // Ambil hanya 1 baris karena ID tugas unik
    
    $stmt = $koneksi->prepare($query);
    if ($stmt) { // Pastikan prepared statement berhasil dibuat
        $stmt->bind_param("ii", $user_id, $assignment_id); // Bind user_id dan assignment_id
        $stmt->execute(); // Eksekusi query
        $result = $stmt->get_result(); // Dapatkan hasil query

        if ($result->num_rows > 0) { // Jika tugas ditemukan
            $assignment_data = $result->fetch_assoc(); // Ambil data tugas
            // Inisialisasi nilai default jika kolom bisa NULL di database untuk mencegah warning
            $assignment_data['kelas_name'] = $assignment_data['kelas_name'] ?? '-';
            $assignment_data['submission_status'] = $assignment_data['submission_status'] ?? 'belum'; // Default status 'belum'
            $assignment_data['submission_file_path'] = $assignment_data['submission_file_path'] ?? null;
            $assignment_data['submitted_at'] = $assignment_data['submitted_at'] ?? null;

        } else { // Jika tugas tidak ditemukan berdasarkan ID atau user ID
            $_SESSION['error_message'] = "Tugas tidak ditemukan atau Anda tidak memiliki akses ke tugas ini.";
            header("Location: index.php"); // Redirect kembali ke daftar tugas
            exit();
        }
        $stmt->close(); // Tutup statement
    } else { // Jika prepared statement gagal dibuat
        $_SESSION['error_message'] = "Error menyiapkan query detail tugas: " . mysqli_error($koneksi);
        header("Location: index.php");
        exit();
    }
} else { // Jika ID tugas tidak valid atau tidak ada di URL
    $_SESSION['error_message'] = "ID Tugas tidak valid.";
    header("Location: index.php"); // Redirect kembali ke daftar tugas
    exit();
}

// Tutup koneksi database jika tidak digunakan lagi oleh footer
// (asumsi dashboard_footer.php tidak menggunakan $koneksi)
// Koneksi akan ditutup di akhir script ini.
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1><?= $page_title ?></h1>
        <ol class="breadcrumb gap-2">
            <li><a href="../../index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="index.php">Tugas</a></li>
            <li class="active"><?= $page_title ?></li>
        </ol>
    </section>

    <div class="container mt-4">
        <?php if ($assignment_data): // Pastikan data tugas berhasil dimuat sebelum menampilkan card ?>
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title">Detail Tugas: <?= htmlspecialchars($assignment_data['title']) ?></h3>
                </div>
                <div class="card-body">
                    <p><strong>Judul Tugas:</strong> <?= htmlspecialchars($assignment_data['title']) ?></p>
                    <p><strong>Kelas:</strong> <?= htmlspecialchars($assignment_data['kelas_name']) ?></p>
                    <p><strong>Deadline:</strong> <?= htmlspecialchars($assignment_data['deadline']) ?></p>
                    
                    <h5 class="mt-4">Deskripsi:</h5>
                    <div class="card p-3" style="background-color: #f8f9fa;">
                        <?= nl2br(($assignment_data['description'])) ?>
                    </div>

                    <h5 class="mt-4">Status Pengumpulan Anda:</h5>
                    <?php
                    // Tentukan kelas badge berdasarkan status pengumpulan
                    $status_text = $assignment_data['submission_status'];
                    $status_badge_class = match ($status_text) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'secondary' // 'belum' atau status lain yang tidak dikenal
                    };
                    ?>
                    <p>Status: <span class="badge bg-<?= $status_badge_class ?>"><?= ucfirst($status_text) ?></span></p>

                    <?php if ($assignment_data['submitted_at']): // Tampilkan tanggal dikumpulkan jika sudah ada ?>
                        <p>Dikumpulkan pada: <?= htmlspecialchars($assignment_data['submitted_at']) ?></p>
                    <?php endif; ?>

                    <?php if ($assignment_data['submission_file_path']): // Tampilkan link berkas jika sudah ada ?>
                        <p>
                            <strong>Berkas Anda:</strong> 
                            <a href="../../uploads/<?= htmlspecialchars($assignment_data['submission_file_path']) ?>" target="_blank" class="btn btn-sm btn-secondary">Lihat Berkas</a>
                        </p>
                    <?php endif; ?>

                    <div class="mt-4 text-end">
                        <a href="index.php" class="btn btn-secondary">Kembali ke Daftar Tugas</a>
                        <?php 
                        // Tampilkan tombol "Kumpulkan Tugas" jika statusnya 'belum' atau 'rejected'
                        if ($assignment_data['submission_status'] === 'belum' || $assignment_data['submission_status'] === 'rejected'): ?>
                            <a href="submit.php?id=<?= htmlspecialchars($assignment_data['id']) ?>" class="btn btn-primary">Kumpulkan Tugas</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php else: // Tampilkan pesan jika data tugas tidak ditemukan atau error ?>
            <div class="alert alert-danger">Tugas tidak dapat dimuat atau tidak ditemukan.</div>
        <?php endif; ?>
    </div>
</div>

<?php 
// Tutup koneksi database di akhir script PHP utama
if (isset($koneksi) && is_object($koneksi) && $koneksi->ping()) {
    $koneksi->close();
}
require_once '../dashboard_footer.php'; 
?>