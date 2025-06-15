<?php
ob_start(); // Mulai output buffering di awal skrip
session_start();
require_once '../../includes/inc_koneksi.php'; // sesuaikan path

// --- PENTING: Aktifkan script Summernote di footer untuk halaman ini ---
$enable_material_scripts = true;
// --- AKHIR PENTING ---

if (!isset($_SESSION['user_id']) || (!in_array($_SESSION['role'], ['developer', 'admin']))) {
    header('Location: ../../login.php');
    exit();
}
$page_title = "Manajemen Informasi";
$message = '';
$message_type = '';

// --- HANDLE DELETE ---
if (isset($_GET['delete'])) {
    $id_to_delete = intval($_GET['delete']);
    
    // Gunakan Prepared Statement untuk DELETE
    $stmt_del = $koneksi->prepare("DELETE FROM informasi WHERE id = ?");
    if ($stmt_del) {
        $stmt_del->bind_param("i", $id_to_delete);
        if ($stmt_del->execute()) {
            $message = "Data berhasil dihapus.";
            $message_type = 'success';
        } else {
            $message = "Gagal menghapus data: " . $stmt_del->error;
            $message_type = 'danger';
            error_log("DB_ERROR_DELETE: " . $stmt_del->error); // Log error
        }
        $stmt_del->close();
    } else {
        $message = "Gagal menyiapkan statement DELETE: " . $koneksi->error;
        $message_type = 'danger';
        error_log("DB_ERROR_PREPARE_DELETE: " . $koneksi->error); // Log error
    }
}

// --- HANDLE ADD / EDIT ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? ''); // Konten dari Summernote
    $id = intval($_POST['id'] ?? 0); // ID untuk operasi EDIT

    if ($title === '' || $content === '') {
        $message = "Judul dan Konten wajib diisi.";
        $message_type = 'danger';
    } else {
        // Gunakan Prepared Statement untuk INSERT/UPDATE
        if ($id > 0) {
            // Update existing
            $sql_query = "UPDATE informasi SET title = ?, content = ? WHERE id = ?";
            $stmt = $koneksi->prepare($sql_query);
            if ($stmt) {
                $stmt->bind_param("ssi", $title, $content, $id); // 'ssi' = string, string, integer
                if ($stmt->execute()) {
                    $message = "Data berhasil diperbarui.";
                    $message_type = 'success';
                } else {
                    $message = "Gagal memperbarui data: " . $stmt->error;
                    $message_type = 'danger';
                    error_log("DB_ERROR_UPDATE: " . $stmt->error); // Log error
                }
                $stmt->close();
            } else {
                $message = "Gagal menyiapkan statement UPDATE: " . $koneksi->error;
                $message_type = 'danger';
                error_log("DB_ERROR_PREPARE_UPDATE: " . $koneksi->error); // Log error
            }
        } else {
            // Insert new
            $sql_query = "INSERT INTO informasi (title, content) VALUES (?, ?)";
            $stmt = $koneksi->prepare($sql_query);
            if ($stmt) {
                $stmt->bind_param("ss", $title, $content); // 'ss' = string, string
                if ($stmt->execute()) {
                    $message = "Data berhasil ditambahkan.";
                    $message_type = 'success';
                } else {
                    $message = "Gagal menambahkan data: " . $stmt->error;
                    $message_type = 'danger';
                    error_log("DB_ERROR_INSERT: " . $stmt->error); // Log error
                }
                $stmt->close();
            } else {
                $message = "Gagal menyiapkan statement INSERT: " . $koneksi->error;
                $message_type = 'danger';
                error_log("DB_ERROR_PREPARE_INSERT: " . $koneksi->error); // Log error
            }
        }
    }
    // Redirect setelah POST untuk mencegah re-submission form
    // Simpan pesan ke session agar tetap tampil setelah redirect
    $_SESSION['temp_message'] = $message;
    $_SESSION['temp_message_type'] = $message_type;
    header('Location: index.php');
    exit();
}

// Ambil pesan dari session jika ada (setelah redirect POST)
if (isset($_SESSION['temp_message'])) {
    $message = $_SESSION['temp_message'];
    $message_type = $_SESSION['temp_message_type'];
    unset($_SESSION['temp_message']);
    unset($_SESSION['temp_message_type']);
}


// Ambil data semua informasi untuk ditampilkan
$informasi_list = [];
$query_select_all = "SELECT id, title, content, created_at FROM informasi ORDER BY created_at DESC";
$result_all_info = mysqli_query($koneksi, $query_select_all);
if ($result_all_info) {
    while ($row = mysqli_fetch_assoc($result_all_info)) {
        $informasi_list[] = $row;
    }
    mysqli_free_result($result_all_info);
} else {
    error_log("DB_ERROR: Failed to fetch informasi list: " . mysqli_error($koneksi));
    $message = "Gagal memuat daftar informasi: " . mysqli_error($koneksi);
    $message_type = 'danger';
}

// SERTAKAN DASHBOARD HEADER SETELAH SEMUA LOGIKA PHP SELESAI
require_once '../dashboard_header.php';
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

        <div class="card mb-4 text-black" style="background-color: #ddd;">
            <div class="card-header bg-info">Tambah / Edit Informasi</div>
            <div class="card-body">
                <form method="POST" action="index.php" id="informasiForm">
                    <input type="hidden" name="id" id="id" value="">
                    <div class="mb-3">
                        <label for="title" class="form-label">Judul</label>
                        <input type="text" class="form-control" name="title" id="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="material_content" class="form-label">Konten</label>
                        <textarea class="form-control" name="content" id="material_content" rows="4" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <button type="button" class="btn btn-secondary" id="btnCancel">Batal</button>
                </form>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-primary">
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
                                <td><?=($info['title']) ?></td>
                                <td><?= $info['content'] ?></td> <td><?= ($info['created_at']) ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning btn-edit" data-id="<?= htmlspecialchars($info['id']) ?>"
                                        data-title="<?= htmlspecialchars($info['title'], ENT_QUOTES) ?>"
                                        data-content="<?= htmlspecialchars($info['content'], ENT_QUOTES) ?>">Edit</button>
                                    <a href="index.php?delete=<?= htmlspecialchars($info['id']) ?>" class="btn btn-sm btn-danger"
                                        onclick="return confirm('Hapus data ini?')">Hapus</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>

<script>
$(document).ready(function() {
    // Inisialisasi Summernote
    $('#material_content').summernote({
        placeholder: 'Isi konten informasi di sini...',
        tabsize: 2,
        height: 200, // Ketinggian Summernote
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'underline', 'clear']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link', 'picture', 'video']], // Memungkinkan insert link, gambar, video
            ['view', ['fullscreen', 'codeview', 'help']]
        ]
        // Tambahkan callbacks jika Anda memerlukan fitur upload gambar/video dari perangkat
        // onImageUpload: function(files) { /* ... */ },
        // onMediaDelete: function(target) { /* ... */ }
    });

    // Script untuk isi form edit (pastikan ini di bawah form dan elemen ada)
    document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.getAttribute('data-id');
            const title = btn.getAttribute('data-title');
            const content = btn.getAttribute('data-content'); // Konten dari attribute data-content

            document.getElementById('id').value = id;
            document.getElementById('title').value = title;
            
            // PENTING: Gunakan Summernote API untuk mengisi konten
            // htmlspecialchars_decode diperlukan jika data-content di-encode
            $('#material_content').summernote('code', content); 
            
            window.scrollTo({ top: 0, behavior: 'smooth' }); // Gulir ke atas form
        });
    });

    document.getElementById('btnCancel').addEventListener('click', () => {
        document.getElementById('informasiForm').reset(); // Reset form elemen biasa
        document.getElementById('id').value = ''; // Kosongkan hidden ID
        // PENTING: Kosongkan juga Summernote menggunakan API-nya
        $('#material_content').summernote('code', ''); 
    });
});
</script>

<?php
require_once '../dashboard_footer.php'; // Sertakan footer dashboard

if (isset($koneksi) && is_object($koneksi) && $koneksi->ping()) {
    $koneksi->close();
}
ob_end_flush(); // Akhiri output buffering
?>