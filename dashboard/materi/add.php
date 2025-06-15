<?php
// Pastikan ob_start() ada di baris paling atas untuk mencegah masalah "headers already sent"
ob_start();
session_start();

// Aktifkan pelaporan kesalahan PHP untuk debugging.
// Harap nonaktifkan ini di lingkungan produksi untuk keamanan.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../includes/inc_koneksi.php'; // sesuaikan path

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'developer', 'teknisi'])) {
    $_SESSION['error_message'] = "Anda tidak memiliki akses untuk menambah materi.";
    header('Location: ../../login.php');
    exit();
}

$page_title = "Tambah Materi Baru";
$error_message = '';
$success_message = '';

// --- PENTING: Aktifkan script Summernote di footer untuk halaman ini ---
$enable_material_scripts = true;
// --- AKHIR PENTING ---

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

// Inisialisasi variabel untuk mengisi ulang form jika ada error
$old_title = isset($_POST['title']) ? $_POST['title'] : '';
$old_content = isset($_POST['content']) ? $_POST['content'] : ''; // Konten Summernote
$old_category_id = isset($_POST['category_id']) ? $_POST['category_id'] : '';
$old_class_id = isset($_POST['class_id']) ? $_POST['class_id'] : '';
$old_file_url = isset($_POST['file_url']) ? $_POST['file_url'] : '';
$old_material_type = isset($_POST['material_type']) ? $_POST['material_type'] : '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']); // Trim saja, tidak perlu real_escape_string manual karena pakai prepared statements
    $content = $_POST['content'];   // Ambil langsung dari POST, JANGAN real_escape_string manual
    $category_id = trim($_POST['category_id']);
    $class_id = trim($_POST['class_id']);
    // $file_url = trim($_POST['file_url']); // Ini akan ditangani lebih dinamis berdasarkan material_type
    $material_type = trim($_POST['material_type']);
    $uploaded_by = $_SESSION['user_id']; // Get current user's ID

    $file_url = ''; // Inisialisasi untuk menyimpan URL file yang akan diunggah

    // Validasi dan Proses File Upload PDF
    if ($material_type === 'pdf') {
        if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
            $pdf_file = $_FILES['pdf_file'];
            $upload_dir = '../../uploads/materi_pdfs/'; // Sesuaikan path ini
            $upload_dir_absolute = realpath($upload_dir) . DIRECTORY_SEPARATOR;

            if (!is_dir($upload_dir_absolute)) {
                mkdir($upload_dir_absolute, 0775, true); // Buat direktori secara rekursif
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
        } else {
            // Jika tipe PDF dipilih tapi tidak ada file diunggah atau ada error upload
            if ($material_type === 'pdf' && $_FILES['pdf_file']['error'] === UPLOAD_ERR_NO_FILE) {
                $error_message = "File PDF wajib diunggah untuk tipe Materi PDF.";
            } else if ($material_type === 'pdf' && $_FILES['pdf_file']['error'] !== UPLOAD_ERR_NO_FILE) {
                 $error_message = "Gagal mengunggah file PDF. Kode error: " . $_FILES['pdf_file']['error'];
            }
        }
    } else { // Jika tipe materi bukan 'pdf' (mungkin 'video', 'image', 'other')
        $file_url = trim($_POST['file_url'] ?? ''); // Ambil dari input file_url jika ada
        if ($material_type !== 'text' && empty($file_url)) {
            $error_message = "URL File tidak boleh kosong jika tipe materi bukan Teks.";
        }
    }


    // Validate required fields (cek ulang error_message di sini)
    if (empty($title) || empty($material_type) || !empty($error_message)) {
        // Error sudah disetel di atas, tidak perlu set ulang
    } else {
        // Handle optional fields that might be empty or null
        $class_id_for_db = (!empty($class_id) && (int)$class_id > 0) ? (int)$class_id : NULL;
        $category_id_for_db = (!empty($category_id) && (int)$category_id > 0) ? (int)$category_id : NULL;

        $content_for_db = ($content === '') ? NULL : $content;
        $file_url_for_db = ($file_url === '') ? NULL : $file_url;
        
        // Buat variabel placeholder untuk NULL agar bisa diteruskan sebagai referensi
        $null_class_id = null;
        $null_category_id = null;
        $null_content = null;
        $null_file_url = null;

        $sql = "INSERT INTO materials (class_id, category_id, title, content, file_url, material_type, uploaded_by, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $koneksi->prepare($sql);
        if ($stmt) {
            $bind_types_arr = [];
            $bind_params_arr = [];

            // Urutan binding harus sesuai dengan urutan '?' di query SQL
            // 1. class_id
            if ($class_id_for_db === NULL) { $bind_types_arr[] = "s"; $bind_params_arr[] = &$null_class_id; } else { $bind_types_arr[] = "i"; $bind_params_arr[] = &$class_id_for_db; }
            // 2. category_id
            if ($category_id_for_db === NULL) { $bind_types_arr[] = "s"; $bind_params_arr[] = &$null_category_id; } else { $bind_types_arr[] = "i"; $bind_params_arr[] = &$category_id_for_db; }
            // 3. title
            $bind_types_arr[] = "s"; $bind_params_arr[] = &$title;
            // 4. content
            if ($content_for_db === NULL) { $bind_types_arr[] = "s"; $bind_params_arr[] = &$null_content; } else { $bind_types_arr[] = "s"; $bind_params_arr[] = &$content_for_db; }
            // 5. file_url
            if ($file_url_for_db === NULL) { $bind_types_arr[] = "s"; $bind_params_arr[] = &$null_file_url; } else { $bind_types_arr[] = "s"; $bind_params_arr[] = &$file_url_for_db; }
            // 6. material_type
            $bind_types_arr[] = "s"; $bind_params_arr[] = &$material_type;
            // 7. uploaded_by
            $bind_types_arr[] = "i"; $bind_params_arr[] = &$uploaded_by;

            $bind_types_string = implode('', $bind_types_arr);

            // Menambahkan string tipe ke awal array parameter untuk call_user_func_array
            array_unshift($bind_params_arr, $bind_types_string);

            // Memanggil bind_param secara dinamis
            call_user_func_array([$stmt, 'bind_param'], $bind_params_arr);

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Materi '" . htmlspecialchars($title) . "' berhasil ditambahkan.";
                header('Location: index.php');
                exit();
            } else {
                $error_message = "Gagal menambahkan materi: " . $stmt->error;
                error_log("DB_INSERT_ERROR: " . $stmt->error); // Log error
            }
            $stmt->close();
        } else {
            $error_message = "Error preparing statement: " . $koneksi->error;
            error_log("DB_PREPARE_ERROR: " . $koneksi->error); // Log error
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
            <?php unset($_SESSION['success_message']); ?>
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
                <form action="" method="POST" enctype="multipart/form-data"> <div class="form-group mb-3">
                        <label for="title">Judul Materi:</label>
                        <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($old_title); ?>" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="class_id">Kelas:</label>
                        <select class="form-control" id="class_id" name="class_id">
                            <option value="">Pilih Kelas (Opsional)</option>
                            <?php foreach ($classes as $cls): ?>
                                <option value="<?php echo htmlspecialchars($cls['id']); ?>" <?php echo ((string)$old_class_id === (string)$cls['id']) ? 'selected' : ''; ?>>
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
                                <option value="<?php echo htmlspecialchars($cat['id']); ?>" <?php echo ((string)$old_category_id === (string)$cat['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="material_type">Tipe Materi:</label>
                        <select class="form-control" id="material_type" name="material_type" required>
                            <option value="">Pilih Tipe</option>
                            <option value="text" <?php echo ((string)$old_material_type === 'text') ? 'selected' : ''; ?>>Teks/HTML</option>
                            <option value="video" <?php echo ((string)$old_material_type === 'video') ? 'selected' : ''; ?>>Video URL</option>
                            <option value="pdf" <?php echo ((string)$old_material_type === 'pdf') ? 'selected' : ''; ?>>PDF File</option> <option value="image" <?php echo ((string)$old_material_type === 'image') ? 'selected' : ''; ?>>Gambar URL</option>
                            <option value="other" <?php echo ((string)$old_material_type === 'other') ? 'selected' : ''; ?>>Lainnya (URL)</option>
                        </select>
                    </div>

                    <div class="form-group mb-3" id="pdf_file_section" style="display: none;">
                        <label for="pdf_file">Pilih File PDF:</label>
                        <input type="file" class="form-control" id="pdf_file" name="pdf_file" accept="application/pdf">
                        <?php if (!empty($old_file_url) && $old_material_type === 'pdf'): ?>
                            <small class="form-text text-muted">File PDF saat ini: <a href="<?php echo htmlspecialchars($old_file_url); ?>" target="_blank">Lihat PDF</a>. Unggah file baru untuk mengganti.</small>
                        <?php endif; ?>
                    </div>

                    <div class="form-group mb-3" id="material_content_section">
                        <label for="material_content">Konten Materi (Teks/HTML):</label>
                        <textarea id="material_content" name="content" class="form-control" rows="10" style="background-color: white;"></textarea>
                    </div>

                    <div class="form-group mb-3" id="file_url_section" style="display: none;"> <label for="file_url">URL File Materi (untuk tipe Video/Gambar/Lainnya):</label>
                        <input type="url" class="form-control" id="file_url" name="file_url" value="<?php echo htmlspecialchars($old_file_url); ?>" placeholder="Contoh: http://example.com/video.mp4">
                    </div>

                    <div class="form-group d-flex justify-content-end mt-4">
                        <a href="index.php" class="btn btn-secondary me-2 mb-2">Batal</a>
                        <button type="submit" class="btn btn-primary mb-2">Simpan Materi</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>

<?php
// Script jQuery dan Summernote harus dimuat di dashboard_footer.php atau di sini.
// Saya berasumsi mereka dimuat di dashboard_footer.php.
?>
<script>
$(document).ready(function() {
    // Fungsi untuk menampilkan/menyembunyikan section berdasarkan tipe materi
    function toggleMaterialSections() {
        var materialType = $('#material_type').val();
        
        // Sembunyikan semua secara default
        $('#material_content_section').hide();
        $('#file_url_section').hide();
        $('#pdf_file_section').hide(); // Tambahkan ini

        // Tampilkan yang sesuai
        if (materialType === 'text') {
            $('#material_content_section').show();
            $('#file_url').val(''); // Kosongkan URL jika beralih ke teks
            $('#pdf_file').val(''); // Kosongkan input file PDF
        } else if (materialType === 'pdf') { // Perlakuan khusus untuk PDF File
            $('#pdf_file_section').show(); // Tampilkan input file PDF
            $('#material_content_section').show(); // Summernote tetap tampil
            $('#file_url').val(''); // Kosongkan URL jika beralih ke PDF File
        } else if (materialType === 'video' || materialType === 'image' || materialType === 'other') {
            $('#file_url_section').show(); // Tampilkan input URL
            $('#material_content_section').show(); // Summernote tetap tampil
            $('#pdf_file').val(''); // Kosongkan input file PDF
        } else { // Jika tidak ada tipe yang dipilih
            $('#material_content_section').hide();
            $('#file_url_section').hide();
            $('#pdf_file_section').hide();
            $('#material_content').summernote('code', '');
            $('#file_url').val('');
            $('#pdf_file').val('');
        }
    }

    // *** FUNGSI UNTUK UPLOAD FILE (GAMBAR & VIDEO) ***
    function uploadFile(file, type) {
        var data = new FormData();
        data.append("file", file); // 'file' adalah nama yang akan diterima $_FILES['file'] di PHP

        var uploadUrl = '';
        // Sesuaikan path ke script upload Anda. Asumsi: mereka di folder yang sama.
        if (type === 'image') {
            uploadUrl = 'upload_image.php'; 
        } else if (type === 'video') {
            uploadUrl = 'upload_video.php'; 
        } else {
            console.error("Tipe upload tidak dikenal: " + type);
            return;
        }

        $.ajax({
            url: uploadUrl,
            cache: false,
            contentType: false,
            processData: false,
            data: data,
            type: "POST",
            success: function(response) {
                // Respons diharapkan adalah JSON: { "url": "path/to/file.ext" } atau { "error": "Pesan error" }
                if (response.url) {
                    if (type === 'image') {
                        $('#material_content').summernote('insertImage', response.url);
                    } else if (type === 'video') {
                        // Summernote akan menyisipkan tag <video> secara otomatis
                        $('#material_content').summernote('createVideoNode', response.url); 
                        /*
                        // Atau jika butuh lebih banyak atribut pada tag <video> (misal lebar responsif):
                        var videoNode = $('<video controls style="max-width: 100%; height: auto;"><source src="' + response.url + '" type="' + file.type + '"></video>');
                        $('#material_content').summernote('insertNode', videoNode[0]);
                        */
                    }
                } else if (response.error) {
                    alert('Gagal mengunggah ' + type + ': ' + response.error);
                    console.error('Summernote upload error for ' + type + ':', response.error);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Terjadi kesalahan saat mengunggah ' + type + '. Cek konsol browser untuk detail.');
                console.error('Summernote AJAX error:', textStatus, errorThrown, jqXHR.responseText);
            }
        });
    }

    // Inisialisasi Summernote
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
            // *** PASTIKAN 'myImageUpload' dan 'myVideoUpload' ADA DI KELOMPOK 'insert' ***
            ['insert', ['link', 'picture', 'video', 'hr', 'myImageUpload', 'myVideoUpload']], 
            ['view', ['fullscreen', 'codeview', 'help']],
            ['misc', ['undo', 'redo', 'print', 'help']]
        ],
        fontNames: ['Arial', 'Arial Black', 'Comic Sans MS', 'Courier New', 'Merriweather', 'Open Sans', 'Roboto', 'Times New Roman'],
        fontSizes: ['8', '9', '10', '11', '12', '14', '16', '18', '24', '36', '48'],
        lineHeights: ['0.8', '1.0', '1.2', '1.4', '1.5', '1.6', '1.8', '2.0', '3.0'],
        
        // *** DEFINISIKAN TOMBOL KUSTOM DI SINI (DI DALAM OBJEK KONFIGURASI SUMMERNOTE) ***
        buttons: {
            // Tombol Kustom untuk Unggah Gambar
            myImageUpload: function () {
                var ui = $.summernote.ui;
                var button = ui.button({
                    contents: '<i class="fa fa-image"/>', // Ikon untuk tombol, pastikan Font Awesome dimuat
                    tooltip: 'Unggah Gambar',
                    click: function () {
                        var fileInput = $('<input type="file" accept="image/*">');
                        fileInput.on('change', function (event) {
                            if (event.target.files.length > 0) {
                                uploadFile(event.target.files[0], 'image');
                            }
                        });
                        fileInput.click();
                    }
                });
                return button.render();
            },
            // Tombol Kustom untuk Unggah Video
            myVideoUpload: function () {
                var ui = $.summernote.ui;
                var button = ui.button({
                    contents: '<i class="fa fa-film"/>', // Ikon untuk tombol, pastikan Font Awesome dimuat
                    tooltip: 'Unggah Video',
                    click: function () {
                        var fileInput = $('<input type="file" accept="video/*">'); // accept="video/*" memfilter tipe file
                        fileInput.on('change', function (event) {
                            if (event.target.files.length > 0) {
                                uploadFile(event.target.files[0], 'video');
                            }
                        });
                        fileInput.click();
                    }
                });
                return button.render();
            }
        },
        // *** AKHIR DEFINISI BUTTONS KUSTOM ***

        // *** CALLBACKS SUMMERNOTE BAWAAN (opsional jika menggunakan tombol kustom) ***
        callbacks: {
            onImageUpload: function(files) {
                // Summernote default 'picture' button's upload option will call this.
                // Jika Anda hanya ingin tombol kustom, Anda bisa menghapus ini.
                uploadFile(files[0], 'image'); 
            },
            onVideoUpload: function(files) {
                // Summernote default 'video' button's upload option will call this.
                // Jika Anda hanya ingin tombol kustom, Anda bisa menghapus ini.
                uploadFile(files[0], 'video'); 
            }
            // Opsional: onMediaDelete jika ingin menghapus file dari server saat dihapus dari editor
            // onMediaDelete: function(target) {
            //     console.log('Menghapus media:', target[0].src);
            // }
        }
    });

    // PENTING: Isi konten Summernote setelah inisialisasi
    var initialContent = <?php echo json_encode(htmlspecialchars_decode($old_content)); ?>; 
    $('#material_content').summernote('code', initialContent);

    // Panggil fungsi saat halaman dimuat
    toggleMaterialSections();

    // Panggil fungsi saat tipe materi berubah
    $('#material_type').change(function() {
        toggleMaterialSections();
    });
});
</script>

<?php
if (isset($koneksi) && is_object($koneksi) && $koneksi->ping()) {
    $koneksi->close();
}
include '../dashboard_footer.php'; // Sertakan footer dashboard

// Pastikan ob_end_flush() dipanggil di akhir jika ob_start() digunakan
ob_end_flush();
?>