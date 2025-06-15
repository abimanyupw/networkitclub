<?php
// Pastikan ob_start() ada di baris paling atas untuk mencegah masalah "headers already sent"
ob_start();
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../includes/inc_koneksi.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'developer', 'teknisi'])) {
    $_SESSION['error_message'] = "Anda tidak memiliki akses untuk mengedit materi.";
    header('Location: ../../login.php');
    exit();
}

$page_title = "Edit Materi";
$error_message = '';
$success_message = '';
$material = null;

$enable_material_scripts = true;

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

$id_from_request = 0;
// Prioritaskan ID dari POST jika ada (setelah formulir disubmit)
if (isset($_POST['material_id_hidden_from_form'])) {
    $id_from_request = intval($_POST['material_id_hidden_from_form']);
} elseif (isset($_GET['id'])) { // Jika tidak ada di POST, ambil dari GET (saat pertama kali membuka halaman)
    $id_from_request = intval($_GET['id']);
}

// Ambil data materi dari database
if ($id_from_request > 0) {
    $sql_fetch = "SELECT id, class_id, category_id, title, content, file_url, material_type FROM materials WHERE id = ?";
    $stmt_fetch = $koneksi->prepare($sql_fetch);
    if ($stmt_fetch) {
        $stmt_fetch->bind_param("i", $id_from_request);
        $stmt_fetch->execute();
        $result_fetch = $stmt_fetch->get_result();
        if ($result_fetch->num_rows > 0) {
            $material = $result_fetch->fetch_assoc();
            // Berikan nilai default kosong jika kolom bisa NULL di DB
            $material['class_id'] = $material['class_id'] ?? '';
            $material['category_id'] = $material['category_id'] ?? '';
            $material['content'] = $material['content'] ?? '';
            $material['file_url'] = $material['file_url'] ?? '';
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
    // Hanya redirect jika tidak ada ID sama sekali saat GET atau POST
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $_SESSION['error_message'] = "ID materi tidak valid.";
        header('Location: index.php');
        exit();
    }
    // Jika ini POST tapi ID tidak valid, biarkan form tampil dengan error
}

// --- Bagian POST untuk memproses pembaruan materi ---
// Pastikan $material sudah terisi dari GET/sebelumnya agar kita tahu ID yang valid
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $material) { 
    $id_to_update = intval($_POST['material_id_hidden_from_form']);

    $title = trim($_POST['title']);
    $content = $_POST['content']; // Ambil langsung dari POST, JANGAN real_escape_string manual
    $category_id = trim($_POST['category_id']);
    $class_id = trim($_POST['class_id']);
    $material_type = trim($_POST['material_type']);
    
    $file_url = $material['file_url']; // Ambil URL lama sebagai default jika tidak ada upload baru

    // Proses File Upload PDF
    if ($material_type === 'pdf') {
        if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
            $pdf_file = $_FILES['pdf_file'];
            $upload_dir = '../../uploads/materi_pdfs/'; // Sesuaikan path ini
            $upload_dir_absolute = realpath($upload_dir) . DIRECTORY_SEPARATOR;

            if (!is_dir($upload_dir_absolute)) {
                mkdir($upload_dir_absolute, 0775, true);
            }

            $allowed_mime_types = ['application/pdf'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $pdf_file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mime_type, $allowed_mime_types)) {
                $error_message = 'Tipe file tidak diizinkan. Hanya PDF yang diizinkan.';
            } else {
                $max_file_size = 20 * 1024 * 1024; // 20 MB
                if ($pdf_file['size'] > $max_file_size) {
                    $error_message = 'Ukuran file PDF terlalu besar. Maksimal 20MB.';
                } else {
                    $file_extension = pathinfo($pdf_file['name'], PATHINFO_EXTENSION);
                    $new_file_name = uniqid('pdf_', true) . '.' . $file_extension;
                    $target_file_path = $upload_dir_absolute . $new_file_name;

                    if (move_uploaded_file($pdf_file['tmp_name'], $target_file_path)) {
                        $file_url = '/networkitclub/uploads/materi_pdfs/' . $new_file_name; // SESUAIKAN PUBLIC URL PATH
                    } else {
                        $error_message = 'Gagal memindahkan file PDF. Periksa izin folder atau php.ini.';
                    }
                }
            }
        } else if ($material_type === 'pdf' && $_FILES['pdf_file']['error'] === UPLOAD_ERR_NO_FILE) {
            // Jika tipe PDF dipilih tapi tidak ada file baru diunggah, pertahankan URL lama ($file_url sudah default)
            // Ini adalah perilaku yang diinginkan untuk edit: tidak wajib upload PDF baru
        } else if ($material_type === 'pdf' && $_FILES['pdf_file']['error'] !== UPLOAD_ERR_NO_FILE) {
            $error_message = "Gagal mengunggah file PDF. Kode error: " . $_FILES['pdf_file']['error'];
        }
    } else { // Jika tipe materi bukan 'pdf' (mungkin 'video', 'image', 'other')
        $file_url = trim($_POST['file_url'] ?? ''); // Ambil dari input file_url
        if ($material_type !== 'text' && empty($file_url)) {
            $error_message = "URL File tidak boleh kosong jika tipe materi bukan Teks.";
        }
    }

    // Validasi akhir sebelum update
    if (empty($title) || empty($material_type) || !empty($error_message)) {
        // Error sudah disetel di atas
    } else {
        $class_id_for_db = (!empty($class_id) && (int)$class_id > 0) ? (int)$class_id : NULL;
        $category_id_for_db = (!empty($category_id) && (int)$category_id > 0) ? (int)$category_id : NULL;
        $content_for_db = ($content === '') ? NULL : $content;
        $file_url_for_db = ($file_url === '') ? NULL : $file_url;
        
        // Buat variabel placeholder untuk NULL agar bisa diteruskan sebagai referensi
        $null_class_id = null;
        $null_category_id = null;
        $null_content = null;
        $null_file_url = null;

        $sql_update = "UPDATE materials SET class_id = ?, category_id = ?, title = ?, content = ?, file_url = ?, material_type = ?, updated_at = NOW() WHERE id = ?";
        $stmt_update = $koneksi->prepare($sql_update);
        if ($stmt_update) {
            $bind_types_arr = [];
            $bind_params_arr = [];

            if ($class_id_for_db === NULL) { $bind_types_arr[] = "s"; $bind_params_arr[] = &$null_class_id; } else { $bind_types_arr[] = "i"; $bind_params_arr[] = &$class_id_for_db; }
            if ($category_id_for_db === NULL) { $bind_types_arr[] = "s"; $bind_params_arr[] = &$null_category_id; } else { $bind_types_arr[] = "i"; $bind_params_arr[] = &$category_id_for_db; }
            $bind_types_arr[] = "s"; $bind_params_arr[] = &$title;
            if ($content_for_db === NULL) { $bind_types_arr[] = "s"; $bind_params_arr[] = &$null_content; } else { $bind_types_arr[] = "s"; $bind_params_arr[] = &$content_for_db; }
            if ($file_url_for_db === NULL) { $bind_types_arr[] = "s"; $bind_params_arr[] = &$null_file_url; } else { $bind_types_arr[] = "s"; $bind_params_arr[] = &$file_url_for_db; }
            $bind_types_arr[] = "s"; $bind_params_arr[] = &$material_type;
            $bind_types_arr[] = "i"; $bind_params_arr[] = &$id_to_update;

            $bind_types_string = implode('', $bind_types_arr);
            array_unshift($bind_params_arr, $bind_types_string);

            call_user_func_array([$stmt_update, 'bind_param'], $bind_params_arr);

            if ($stmt_update->execute()) {
                $_SESSION['success_message'] = "Materi '" . htmlspecialchars($title) . "' berhasil diperbarui.";
                header('Location: index.php');
                exit();
            } else {
                $error_message = "Gagal memperbarui materi: " . $stmt_update->error;
                error_log("DB_UPDATE_ERROR: " . $stmt_update->error);
            };
            $stmt_update->close();
        } else {
            $error_message = "Error preparing update statement: " . $koneksi->error;
            error_log("DB_PREPARE_ERROR: " . $koneksi->error);
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
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                <?php echo htmlspecialchars($_SESSION['success_message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                <?php echo htmlspecialchars($error_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                <?php echo htmlspecialchars($_SESSION['error_message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Form Edit Materi</h3>
            </div>
            <div class="box-body">
                <?php if ($material): ?>
                <form action="" method="POST" enctype="multipart/form-data"> <input type="hidden" name="material_id_hidden_from_form" value="<?= htmlspecialchars($material['id']) ?>">
                    <div class="form-group mb-3">
                        <label for="title">Judul Materi:</label>
                        <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($material['title']); ?>" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="class_id">Kelas:</label>
                        <select class="form-control" id="class_id" name="class_id">
                            <option value="">Pilih Kelas (Opsional)</option>
                            <?php foreach ($classes as $cls): ?>
                                <option value="<?php echo htmlspecialchars($cls['id']); ?>" <?php echo ((string)$material['class_id'] === (string)$cls['id']) ? 'selected' : ''; ?>>
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
                                <option value="<?php echo htmlspecialchars($cat['id']); ?>" <?php echo ((string)$material['category_id'] === (string)$cat['id']) ? 'selected' : ''; ?>>
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
                            <option value="pdf" <?php echo ($material['material_type'] == 'pdf') ? 'selected' : ''; ?>>PDF File</option> <option value="image" <?php echo ($material['material_type'] == 'image') ? 'selected' : ''; ?>>Gambar URL</option>
                            <option value="other" <?php echo ($material['material_type'] == 'other') ? 'selected' : ''; ?>>Lainnya (URL)</option>
                        </select>
                    </div>

                    <div class="form-group mb-3" id="pdf_file_section" style="display: none;">
                        <label for="pdf_file">Pilih File PDF:</label>
                        <input type="file" class="form-control" id="pdf_file" name="pdf_file" accept="application/pdf">
                        <?php if (!empty($material['file_url']) && $material['material_type'] === 'pdf'): ?>
                            <small class="form-text text-muted">File PDF saat ini: <a href="<?php echo htmlspecialchars($material['file_url']); ?>" target="_blank">Lihat PDF</a>. Unggah file baru untuk mengganti.</small>
                        <?php endif; ?>
                    </div>

                    <div class="form-group mb-3" id="material_content_section">
                        <label for="material_content">Konten Materi (Teks/HTML):</label>
                        <textarea id="material_content" name="content" class="form-control" rows="10"></textarea>
                    </div>

                    <div class="form-group mb-3" id="file_url_section" style="display: none;"> <label for="file_url">URL File Materi (untuk tipe Video/Gambar/Lainnya):</label>
                        <input type="url" class="form-control" id="file_url" name="file_url" value="<?php echo htmlspecialchars($material['file_url']); ?>" placeholder="Contoh: http://example.com/video.mp4">
                    </div>

                    <div class="form-group d-flex justify-content-end mt-4">
                        <a href="index.php" class="btn btn-secondary me-2">Batal</a>
                        <button type="submit" class="btn btn-primary">Perbarui Materi</button>
                    </div>
                </form>
                <?php else: ?>
                    <div class="alert alert-warning" role="alert">Materi tidak dapat dimuat atau tidak ditemukan.</div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>

<?php
// Script jQuery dan Summernote harus dimuat di dashboard_footer.php atau di sini.
// Saya berasumsi mereka dimuat di dashboard_footer.php.
?>
<script>
$(document).ready(function () {
    function toggleMaterialSections() {
        var materialType = $('#material_type').val();

        // Sembunyikan semua default
        $('#material_content_section').hide();
        $('#file_url_section').hide();
        $('#pdf_file_section').hide();

        // Tampilkan sesuai pilihan
        if (materialType === 'text') {
            $('#material_content_section').show();
            $('#file_url').val('');
            $('#pdf_file').val('');
        } else if (materialType === 'pdf') {
            $('#pdf_file_section').show(); // input file PDF
            $('#material_content_section').show(); // Summernote tetap (opsional)
            $('#file_url').val('');
        } else if (['video', 'image', 'other'].includes(materialType)) {
            $('#file_url_section').show();
            $('#material_content_section').show();
            $('#pdf_file').val('');
        } else {
            $('#material_content_section').hide();
            $('#file_url_section').hide();
            $('#pdf_file_section').hide();
            $('#material_content').summernote('code', '');
            $('#file_url').val('');
            $('#pdf_file').val('');
        }
    }

    function uploadFile(file, type) {
        var data = new FormData();
        data.append("file", file);

        var uploadUrl = '';
        if (type === 'image') {
            uploadUrl = 'upload_image.php';
        } else if (type === 'video') {
            uploadUrl = 'upload_video.php';
        } else {
            console.error("Tipe upload tidak dikenali: " + type);
            return;
        }

        $.ajax({
            url: uploadUrl,
            cache: false,
            contentType: false,
            processData: false,
            data: data,
            type: "POST",
            success: function (response) {
                if (response.url) {
                    if (type === 'image') {
                        $('#material_content').summernote('insertImage', response.url);
                    } else if (type === 'video') {
                        var videoNode = $('<video controls style="max-width: 100%; height: auto;"><source src="' + response.url + '" type="' + file.type + '"></video>');
                        $('#material_content').summernote('insertNode', videoNode[0]);
                    }
                } else {
                    alert('Gagal mengunggah ' + type + ': ' + (response.error || 'Tidak diketahui'));
                    console.error(response);
                }
            },
            error: function (xhr, status, error) {
                alert('Terjadi kesalahan saat upload ' + type);
                console.error(xhr.responseText);
            }
        });
    }

    $('#material_content').summernote({
        placeholder: 'Isi konten materi di sini...',
        tabsize: 2,
        height: 300,
        toolbar: [
            ['style', ['style']],
            ['fontname', ['fontname']],
            ['fontsize', ['fontsize']],
            ['font', ['bold', 'italic', 'underline', 'strikethrough', 'superscript', 'subscript', 'clear']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph', 'height']],
            ['table', ['table']],
            ['insert', ['link', 'picture', 'video', 'hr', 'myImageUpload', 'myVideoUpload']],
            ['view', ['fullscreen', 'codeview', 'help']],
            ['misc', ['undo', 'redo']]
        ],
        fontNames: ['Arial', 'Comic Sans MS', 'Courier New', 'Open Sans', 'Roboto'],
        fontSizes: ['10', '12', '14', '16', '18', '24', '36'],
        buttons: {
            myImageUpload: function () {
                var ui = $.summernote.ui;
                return ui.button({
                    contents: '<i class="fa fa-image"/>',
                    tooltip: 'Unggah Gambar',
                    click: function () {
                        var input = $('<input type="file" accept="image/*">');
                        input.on('change', function (e) {
                            if (e.target.files.length > 0) {
                                uploadFile(e.target.files[0], 'image');
                            }
                        });
                        input.trigger('click');
                    }
                }).render();
            },
            myVideoUpload: function () {
                var ui = $.summernote.ui;
                return ui.button({
                    contents: '<i class="fa fa-film"/>',
                    tooltip: 'Unggah Video',
                    click: function () {
                        var input = $('<input type="file" accept="video/*">');
                        input.on('change', function (e) {
                            if (e.target.files.length > 0) {
                                uploadFile(e.target.files[0], 'video');
                            }
                        });
                        input.trigger('click');
                    }
                }).render();
            }
        },
        callbacks: {
            onImageUpload: function (files) {
                uploadFile(files[0], 'image');
            },
            onVideoUpload: function (files) {
                uploadFile(files[0], 'video');
            }
        }
    });

    var initialContent = <?php echo json_encode(htmlspecialchars_decode($material['content'] ?? '')); ?>;
    $('#material_content').summernote('code', initialContent);

    toggleMaterialSections();
    $('#material_type').on('change', toggleMaterialSections);
});
</script>

<?php
if (isset($koneksi) && is_object($koneksi) && $koneksi->ping()) {
 $koneksi->close();
}
// Pastikan ob_end_flush() dipanggil di akhir jika ob_start() digunakan
ob_end_flush();
include '../dashboard_footer.php';
?>