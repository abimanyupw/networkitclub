<?php
session_start();
include '../../includes/inc_koneksi.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'developer', 'teknisi'])) {
    $_SESSION['error_message'] = "Anda tidak memiliki akses untuk mengedit materi.";
    header('Location: ../../login.php');
    exit();
}

$page_title = "Edit Materi";
$error_message = '';
$success_message = '';
$material = null;

// Fetch categories for dropdown
$categories = [];
$sql_categories = "SELECT id, name FROM categories ORDER BY name ASC";
$result_categories = $koneksi->query($sql_categories);
if ($result_categories) {
    while ($row = $result_categories->fetch_assoc()) {
        $categories[] = $row;
    }
    $result_categories->free();
} else {
    $error_message .= "Gagal mengambil data kategori: " . $koneksi->error . "<br>";
}

// Fetch classes for dropdown
$classes = [];
$sql_classes = "SELECT id, name FROM classes ORDER BY name ASC";
$result_classes = $koneksi->query($sql_classes);
if ($result_classes) {
    while ($row = $result_classes->fetch_assoc()) {
        $classes[] = $row;
    }
    $result_classes->free();
} else {
    $error_message .= "Gagal mengambil data kelas: " . $koneksi->error . "<br>";
}


if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']);

    // Fetch material data
    $sql_fetch = "SELECT id, class_id, category_id, title, content, file_url, material_type FROM materials WHERE id = ?";
    $stmt_fetch = $koneksi->prepare($sql_fetch);
    if ($stmt_fetch) {
        $stmt_fetch->bind_param("i", $id);
        $stmt_fetch->execute();
        $result_fetch = $stmt_fetch->get_result();
        if ($result_fetch->num_rows > 0) {
            $material = $result_fetch->fetch_assoc();
        } else {
            $_SESSION['error_message'] = "Materi tidak ditemukan.";
            header('Location: index.php');
            exit();
        }
        $stmt_fetch->close();
    } else {
        $error_message = "Error preparing fetch statement: " . $koneksi->error;
    }
} else {
    $_SESSION['error_message'] = "ID materi tidak valid.";
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $koneksi->real_escape_string(trim($_POST['title']));
    $content = $koneksi->real_escape_string($_POST['content']);
    $category_id = $koneksi->real_escape_string(trim($_POST['category_id']));
    $class_id = $koneksi->real_escape_string(trim($_POST['class_id']));
    $file_url = $koneksi->real_escape_string(trim($_POST['file_url']));
    $material_type = $koneksi->real_escape_string(trim($_POST['material_type']));
    // uploaded_by is not updated here, it remains the original uploader

    // Validate required fields
    if (empty($title) || empty($material_type)) {
        $error_message = "Judul dan Tipe Materi tidak boleh kosong.";
    } elseif ($material_type !== 'text' && empty($file_url)) {
        $error_message = "URL File tidak boleh kosong jika tipe materi bukan Teks.";
    } else {
        // Handle optional fields that might be empty or null
        $class_id_for_db = !empty($class_id) ? $class_id : NULL;
        $category_id_for_db = !empty($category_id) ? $category_id : NULL;
        // Content will always be passed from Summernote, even if empty.
        $file_url_for_db = ($material_type === 'text') ? NULL : $file_url; // file_url depends on material_type

        $sql_update = "UPDATE materials SET class_id = ?, category_id = ?, title = ?, content = ?, file_url = ?, material_type = ?, updated_at = NOW() WHERE id = ?";
        $stmt_update = $koneksi->prepare($sql_update);
        if ($stmt_update) {
            $stmt_update->bind_param("iissssi",
                $class_id_for_db,
                $category_id_for_db,
                $title,
                $content, // content always passed
                $file_url_for_db, // file_url depends on material_type
                $material_type,
                $id // Where ID
            );
            if ($stmt_update->execute()) {
                $_SESSION['success_message'] = "Materi '" . htmlspecialchars($title) . "' berhasil diperbarui.";
                header('Location: index.php');
                exit();
            } else {
                $error_message = "Gagal memperbarui materi: " . $stmt_update->error;
            }
            $stmt_update->close();
        } else {
            $error_message = "Error preparing update statement: " . $koneksi->error;
        }
    }
}
?>
<?php
include '../dashboard_header.php';  
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1><?php echo $page_title; ?></h1>
        <ol class="breadcrumb gap-2">
            <li><a href="../../index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="index.php">Manajemen Materi</a></li>
            <li class="active"><?php echo $page_title; ?></li>
        </ol>
    </section>

    <section class="content">
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                <?php echo htmlspecialchars($success_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                <?php echo htmlspecialchars($error_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Form Edit Materi</h3>
            </div>
            <div class="box-body">
                <?php if ($material): ?>
                <form action="" method="POST">
                    <div class="form-group mb-3">
                        <label for="title">Judul Materi:</label>
                        <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($material['title']); ?>" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="class_id">Kelas:</label>
                        <select class="form-control" id="class_id" name="class_id">
                            <option value="">Pilih Kelas (Opsional)</option>
                            <?php foreach ($classes as $cls): ?>
                                <option value="<?php echo htmlspecialchars($cls['id']); ?>" <?php echo ($material['class_id'] == $cls['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cls['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="category_id">Kategori:</label>
                        <select class="form-control" id="category_id" name="category_id">
                            <option value="">Pilih Kategori (Opsional)</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat['id']); ?>" <?php echo ($material['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="material_type">Tipe Materi:</label>
                        <select class="form-control" id="material_type" name="material_type" required>
                            <option value="">Pilih Tipe</option>
                            <option value="text" <?php echo ($material['material_type'] == 'text') ? 'selected' : ''; ?>>Teks/HTML</option>
                            <option value="video" <?php echo ($material['material_type'] == 'video') ? 'selected' : ''; ?>>Video URL</option>
                            <option value="pdf" <?php echo ($material['material_type'] == 'pdf') ? 'selected' : ''; ?>>PDF URL</option>
                            <option value="image" <?php echo ($material['material_type'] == 'image') ? 'selected' : ''; ?>>Gambar URL</option>
                            <option value="other" <?php echo ($material['material_type'] == 'other') ? 'selected' : ''; ?>>Lainnya (URL)</option>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="material_content">Konten Materi (Teks/HTML):</label>
                        <textarea id="material_content" name="content" class="form-control" rows="10"><?php echo htmlspecialchars($material['content']); ?></textarea>
                    </div>

                    <div class="form-group mb-3" id="file_url_section">
                        <label for="file_url">URL File Materi (untuk tipe selain Teks/HTML):</label>
                        <input type="url" class="form-control" id="file_url" name="file_url" value="<?php echo htmlspecialchars($material['file_url']); ?>" placeholder="Contoh: https://youtube.com/embed/...">
                    </div>

                    <div class="form-group d-flex justify-content-end mt-4">
                        <a href="index.php" class="btn btn-secondary me-2">Batal</a>
                        <button type="submit" class="btn btn-primary">Perbarui Materi</button>
                    </div>
                </form>
                <?php else: ?>
                    <div class="alert alert-warning" role="alert">Materi tidak dapat dimuat.</div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>


<?php
$koneksi->close();
include '../dashboard_footer.php';
?>