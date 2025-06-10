<?php
session_start();

include '../../includes/inc_koneksi.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'developer', 'teknisi'])) {
    $_SESSION['error_message'] = "Anda tidak memiliki akses untuk menambah materi.";
    header('Location: ../../login.php');
    exit();
}

$page_title = "Tambah Materi Baru";
$error_message = '';
$success_message = '';

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

// Fetch classes for dropdown (assuming a 'classes' table)
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $koneksi->real_escape_string(trim($_POST['title']));
    $content = $koneksi->real_escape_string($_POST['content']); // Summernote content can be large
    $category_id = $koneksi->real_escape_string(trim($_POST['category_id']));
    $class_id = $koneksi->real_escape_string(trim($_POST['class_id']));
    $file_url = $koneksi->real_escape_string(trim($_POST['file_url']));
    $material_type = $koneksi->real_escape_string(trim($_POST['material_type']));
    $uploaded_by = $_SESSION['user_id']; // Get current user's ID

    // Validate required fields
    if (empty($title) || empty($material_type)) {
        $error_message = "Judul dan Tipe Materi tidak boleh kosong.";
    } elseif ($material_type !== 'text' && empty($file_url)) {
        $error_message = "URL File tidak boleh kosong jika tipe materi bukan Teks.";
    } else {
        // Handle optional fields that might be empty or null
        $class_id = !empty($class_id) ? $class_id : NULL;
        $category_id = !empty($category_id) ? $category_id : NULL;
        // Content will always be passed from Summernote, even if empty.
        // File_url will be NULL if type is 'text', otherwise it uses the input.
        $file_url_for_db = ($material_type === 'text') ? NULL : $file_url;


        $sql = "INSERT INTO materials (class_id, category_id, title, content, file_url, material_type, uploaded_by, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $koneksi->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("iissssi",
                $class_id,
                $category_id,
                $title,
                $content, // content always passed
                $file_url_for_db, // file_url depends on material_type
                $material_type,
                $uploaded_by
            );

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Materi '" . htmlspecialchars($title) . "' berhasil ditambahkan.";
                header('Location: index.php');
                exit();
            } else {
                $error_message = "Gagal menambahkan materi: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error_message = "Error preparing statement: " . $koneksi->error;
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
                <h3 class="box-title">Form Tambah Materi</h3>
            </div>
            <div class="box-body">
                <form action="" method="POST">
                    <div class="form-group mb-3">
                        <label for="title">Judul Materi:</label>
                        <input type="text" class="form-control" id="title" name="title" value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="class_id">Kelas:</label>
                        <select class="form-control" id="class_id" name="class_id">
                            <option value="">Pilih Kelas (Opsional)</option>
                            <?php foreach ($classes as $cls): ?>
                                <option value="<?php echo htmlspecialchars($cls['id']); ?>" <?php echo (isset($_POST['class_id']) && $_POST['class_id'] == $cls['id']) ? 'selected' : ''; ?>>
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
                                <option value="<?php echo htmlspecialchars($cat['id']); ?>" <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="material_type">Tipe Materi:</label>
                        <select class="form-control" id="material_type" name="material_type" required>
                            <option value="">Pilih Tipe</option>
                            <option value="text" <?php echo (isset($_POST['material_type']) && $_POST['material_type'] == 'text') ? 'selected' : ''; ?>>Teks/HTML</option>
                            <option value="video" <?php echo (isset($_POST['material_type']) && $_POST['material_type'] == 'video') ? 'selected' : ''; ?>>Video URL</option>
                            <option value="pdf" <?php echo (isset($_POST['material_type']) && $_POST['material_type'] == 'pdf') ? 'selected' : ''; ?>>PDF URL</option>
                            <option value="image" <?php echo (isset($_POST['material_type']) && $_POST['material_type'] == 'image') ? 'selected' : ''; ?>>Gambar URL</option>
                            <option value="other" <?php echo (isset($_POST['material_type']) && $_POST['material_type'] == 'other') ? 'selected' : ''; ?>>Lainnya (URL)</option>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="material_content">Konten Materi (Teks/HTML):</label>
                        <textarea id="material_content" name="content" class="form-control" rows="10"><?php echo isset($_POST['content']) ? htmlspecialchars($_POST['content']) : ''; ?></textarea>
                    </div>

                    <div class="form-group mb-3" id="file_url_section">
                        <label for="file_url">URL File Materi (untuk tipe selain Teks/HTML):</label>
                        <input type="url" class="form-control" id="file_url" name="file_url" value="<?php echo isset($_POST['file_url']) ? htmlspecialchars($_POST['file_url']) : ''; ?>" placeholder="Contoh: https://youtube.com/embed/...">
                    </div>

                    <div class="form-group d-flex justify-content-end mt-4">
                        <a href="index.php" class="btn btn-secondary me-2 mb-2">Batal</a>
                        <button type="submit" class="btn btn-primary mb-2">Simpan Materi</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
                        </date_interval_create_from_date_string>


<?php
$koneksi->close();
include '../dashboard_footer.php';
?>