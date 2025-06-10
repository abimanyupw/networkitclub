<?php
// dashboard/about/index.php (atau dashboard/manage_about/index.php)

// PASTIKAN TIDAK ADA SPASI, BARIS BARU, ATAU KARAKTER LAIN SEBELUM TAG <?php INI
session_start();

// --- PENTING: LOGIKA OTENTIKASI DAN REDIRECT HARUS DI SINI, DI PALING ATAS ---
// Ini memastikan tidak ada HTML yang terkirim sebelum redirect dieksekusi.
if (!isset($_SESSION['user_id']) || (!in_array($_SESSION['role'], ['developer', 'admin']))) {
    header('Location: ../../login.php'); // Sesuaikan path login.php jika berbeda
    exit(); // Hentikan eksekusi skrip setelah redirect
}

require_once '../../includes/inc_koneksi.php';

// --- Variabel Dashboard (sebelum HTML dimulai) ---
// Variabel-variabel ini digunakan di bagian HTML header dan sidebar.
$page_title = 'Kelola Halaman About';
$logged_in_username = $_SESSION['full_name'] ?? 'Guest';
$logged_in_role = $_SESSION['role'] ?? 'Guest';

// LOGIKA PENENTUAN MENU AKTIF (dipindahkan ke sini agar variabelnya tersedia)
$current_full_uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$dashboard_segment_name = 'dashboard';
$pos = strpos($current_full_uri_path, '/' . $dashboard_segment_name . '/');

$path_to_compare = '';
if ($pos !== false) {
    $path_to_compare = substr($current_full_uri_path, $pos + strlen('/' . $dashboard_segment_name . '/'));
} else {
    $path_to_compare = ltrim($current_full_uri_path, '/');
    $parts = explode('/', $path_to_compare);
    $dashboard_index = array_search($dashboard_segment_name, $parts);
    if ($dashboard_index !== false) {
        $path_to_compare = implode('/', array_slice($parts, $dashboard_index + 1));
    }
}
$path_to_compare = trim($path_to_compare, '/');
if (empty($path_to_compare) && (strpos($current_full_uri_path, '/index.php') !== false || substr($current_full_uri_path, -1) == '/')) {
    $path_to_compare = 'index.php';
}

// DEFINISI MENU_ITEMS
$menu_items = [];
$current_role = $_SESSION['role'] ?? 'guest';

if ($current_role == 'developer') {
    $menu_items = [
        ['Dashboard', '../developer/index.php', 'fa-solid fa-table-cells-large'],
        ['Kelola Data', '#', 'fa-solid fa-folder', [
            ['Pengguna', '../users/index.php', 'fa-solid fa-users'],
            ['Kategori Materi', '../kategori/index.php', 'fa-solid fa-tags'],
            ['Materi', '../materi/index.php', 'fa-solid fa-file-lines']
        ]],
        ['Kelola Halaman', '#', 'fa-solid fa-pager', [
            ['Home', '../home/index.php', 'fa-solid fa-house'],
            ['About', '../about/index.php', 'fa-solid fa-circle-info'], // Sesuaikan path jika folder manage_about
            ['Class', '../class/index.php', 'fa-solid fa-chalkboard-user'],
            ['Contact', '../contact/index.php', 'fa-solid fa-envelope']
        ]]
    ];
} elseif ($current_role == 'admin') {
    $menu_items = [
        ['Dashboard', '../admin/index.php', 'fa-solid fa-table-cells-large'],
        ['Kelola Data', '#', 'fa-solid fa-folder', [
            ['Pengguna', '../users/index.php', 'fa-solid fa-users'],
            ['Kategori Materi', '../kategori/index.php', 'fa-solid fa-tags'],
            ['Materi', '../materi/index.php', 'fa-solid fa-file-lines']
        ]],
        ['Kelola Halaman', '#', 'fa-solid fa-pager', [
            ['Home', '../home/index.php', 'fa-solid fa-house'],
            ['About', '../about/index.php', 'fa-solid fa-circle-info'], // Sesuaikan path jika folder manage_about
            ['Class', '../class/index.php', 'fa-solid fa-chalkboard-user'],
            ['Contact', '../contact/index.php', 'fa-solid fa-envelope']
        ]]
    ];
} elseif ($current_role == 'teknisi') {
    $menu_items = [
        ['Dashboard', '../teknisi/index.php', 'fa-solid fa-table-cells-large'],
        ['Kelas', '../teknisi/manage_classes.php', 'fa-solid fa-book'],
        ['Materi', '../teknisi/manage_materials.php', 'fa-solid fa-file-lines'],
        ['Edit Profil', '../teknisi/edit_profile.php', 'fa-solid fa-user-pen'],
        ['Progress', '../teknisi/manage_progress.php', 'fa-solid fa-chart-line']
    ];
} elseif ($current_role == 'siswa') {
    $menu_items = [
        ['Dashboard', '../siswa/index.php', 'fa-solid fa-table-cells-large'],
        ['Kelas yang Diikuti', '../siswa/my_classes.php', 'fa-solid fa-book'],
        ['Progress Belajar', '../siswa/progress.php', 'fa-solid fa-chart-line'],
        ['Edit Profil', '../siswa/edit_profile.php', 'fa-solid fa-user-pen']
    ];
}

// --- Inisialisasi variabel About Section ---
$about_content = [
    'about_title' => '',
    'about_description' => ''
];

// --- Inisialisasi variabel Skills ---
$soft_skills_data = [];
$hard_skills_data = [];

// --- PENGAMBILAN DATA ABOUT SECTION DARI DATABASE (MySQLi) ---
$query_about = "SELECT about_title, about_description FROM about_section WHERE id = 1";
$result_about = mysqli_query($koneksi, $query_about);

if ($result_about) {
    $data_about = mysqli_fetch_assoc($result_about);
    if ($data_about) {
        $about_content['about_title'] = $data_about['about_title'];
        $about_content['about_description'] = $data_about['about_description'];
    }
    mysqli_free_result($result_about);
} else {
    $_SESSION['message_about'] = 'Error saat mengambil data About Section: ' . mysqli_error($koneksi);
    $_SESSION['message_type_about'] = 'danger';
    error_log("Error fetching about section data: " . mysqli_error($koneksi));
}

// --- PENGAMBILAN DATA SKILLS DARI DATABASE (MySQLi) ---
$query_skills = "SELECT id, type, name FROM skills ORDER BY type ASC, id ASC";
$result_skills = mysqli_query($koneksi, $query_skills);

if ($result_skills) {
    while ($row_skill = mysqli_fetch_assoc($result_skills)) {
        if ($row_skill['type'] == 'soft') {
            $soft_skills_data[] = $row_skill;
        } else {
            $hard_skills_data[] = $row_skill;
        }
    }
    mysqli_free_result($result_skills);
} else {
    $_SESSION['message_skills'] = 'Error saat mengambil data Skills: ' . mysqli_error($koneksi);
    $_SESSION['message_type_skills'] = 'danger';
    error_log("Error fetching skills data: " . mysqli_error($koneksi));
}

// Mengambil pesan dari session (setelah redirect dari operasi POST)
$message = $_SESSION['message'] ?? '';
$message_type = $_SESSION['message_type'] ?? '';
unset($_SESSION['message']);
unset($_SESSION['message_type']);

$message_skill_add = $_SESSION['message_skill_add'] ?? '';
$message_type_skill_add = $_SESSION['message_type_skill_add'] ?? '';
unset($_SESSION['message_skill_add']);
unset($_SESSION['message_type_skill_add']);

$message_skill_edit = $_SESSION['message_skill_edit'] ?? '';
$message_type_skill_edit = $_SESSION['message_type_skill_edit'] ?? '';
unset($_SESSION['message_skill_edit']);
unset($_SESSION['message_type_skill_edit']);

$message_skill_delete = $_SESSION['message_skill_delete'] ?? '';
$message_type_skill_delete = $_SESSION['message_type_skill_delete'] ?? '';
unset($_SESSION['message_skill_delete']);
unset($_SESSION['message_type_skill_delete']);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- PENANGANAN SUBMIT FORM ABOUT SECTION ---
    if (isset($_POST['update_about_section'])) {
        $new_about_title = trim($_POST['about_title'] ?? '');
        $new_about_description = trim($_POST['about_description'] ?? '');

        if (empty($new_about_title) || empty($new_about_description)) {
            $_SESSION['message'] = 'Judul dan deskripsi About Section harus diisi.';
            $_SESSION['message_type'] = 'danger';
        } else {
            $new_about_title_escaped = mysqli_real_escape_string($koneksi, $new_about_title);
            $new_about_description_escaped = mysqli_real_escape_string($koneksi, $new_about_description);

            $update_about_query = "UPDATE about_section SET
                                        about_title = '$new_about_title_escaped',
                                        about_description = '$new_about_description_escaped'
                                      WHERE id = 1";

            if (mysqli_query($koneksi, $update_about_query)) {
                $_SESSION['message'] = 'Konten About Section berhasil diperbarui!';
                $_SESSION['message_type'] = 'success';
            } else {
                $_SESSION['message'] = 'Error saat memperbarui About Section: ' . mysqli_error($koneksi);
                $_SESSION['message_type'] = 'danger';
                error_log("Error updating about section: " . mysqli_error($koneksi));
            }
        }
        // Redirect setelah POST untuk mencegah resubmission
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }

    // --- PENANGANAN TAMBAH SKILL ---
    if (isset($_POST['add_skill'])) {
        $new_skill_name = trim($_POST['new_skill_name'] ?? '');
        $new_skill_type = trim($_POST['new_skill_type'] ?? '');

        if (empty($new_skill_name) || !in_array($new_skill_type, ['soft', 'hard'])) {
            $_SESSION['message_skill_add'] = 'Nama skill dan tipe harus diisi dengan benar.';
            $_SESSION['message_type_skill_add'] = 'danger';
        } else {
            $new_skill_name_escaped = mysqli_real_escape_string($koneksi, $new_skill_name);
            $new_skill_type_escaped = mysqli_real_escape_string($koneksi, $new_skill_type);

            $insert_skill_query = "INSERT INTO skills (type, name) VALUES ('$new_skill_type_escaped', '$new_skill_name_escaped')";
            if (mysqli_query($koneksi, $insert_skill_query)) {
                $_SESSION['message_skill_add'] = 'Skill berhasil ditambahkan!';
                $_SESSION['message_type_skill_add'] = 'success';
            } else {
                $_SESSION['message_skill_add'] = 'Error saat menambahkan skill: ' . mysqli_error($koneksi);
                $_SESSION['message_type_skill_add'] = 'danger';
                error_log("Error adding skill: " . mysqli_error($koneksi));
            }
        }
        header('Location: ' . $_SERVER['PHP_SELF']); // Redirect untuk menghindari resubmit
        exit();
    }

    // --- PENANGANAN EDIT SKILL ---
    if (isset($_POST['edit_skill'])) {
        $skill_id = intval($_POST['skill_id'] ?? 0);
        $edited_skill_name = trim($_POST['edited_skill_name'] ?? '');

        if ($skill_id <= 0 || empty($edited_skill_name)) {
            $_SESSION['message_skill_edit'] = 'ID skill atau nama skill tidak valid.';
            $_SESSION['message_type_skill_edit'] = 'danger';
        } else {
            $edited_skill_name_escaped = mysqli_real_escape_string($koneksi, $edited_skill_name);
            $update_skill_query = "UPDATE skills SET name = '$edited_skill_name_escaped' WHERE id = $skill_id";

            if (mysqli_query($koneksi, $update_skill_query)) {
                $_SESSION['message_skill_edit'] = 'Skill berhasil diperbarui!';
                $_SESSION['message_type_skill_edit'] = 'success';
            } else {
                $_SESSION['message_skill_edit'] = 'Error saat memperbarui skill: ' . mysqli_error($koneksi);
                $_SESSION['message_type_skill_edit'] = 'danger';
                error_log("Error updating skill: " . mysqli_error($koneksi));
            }
        }
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }

    // --- PENANGANAN HAPUS SKILL ---
    if (isset($_POST['delete_skill'])) {
        $skill_id_to_delete = intval($_POST['skill_id_to_delete'] ?? 0);

        if ($skill_id_to_delete <= 0) {
            $_SESSION['message_skill_delete'] = 'ID skill tidak valid untuk dihapus.';
            $_SESSION['message_type_skill_delete'] = 'danger';
        } else {
            $delete_skill_query = "DELETE FROM skills WHERE id = $skill_id_to_delete";
            if (mysqli_query($koneksi, $delete_skill_query)) {
                $_SESSION['message_skill_delete'] = 'Skill berhasil dihapus!';
                $_SESSION['message_type_skill_delete'] = 'success';
            } else {
                $_SESSION['message_skill_delete'] = 'Error saat menghapus skill: ' . mysqli_error($koneksi);
                $_SESSION['message_type_skill_delete'] = 'danger';
                error_log("Error deleting skill: " . mysqli_error($koneksi));
            }
        }
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }
}

// --- Akhir dari semua logika PHP yang mungkin melakukan redirect ---
// Setelah titik ini, aman untuk mencetak HTML.

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="icon" href="../../img/logo.png" type="image/x-icon">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

    <link rel="stylesheet" href="../../css/dashboard.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-top-dashboard">
        <div class="container-fluid">
            <button class="btn btn-outline-light d-lg-none me-2" type="button" id="sidebarToggle">
                <i class="fa-solid fa-bars"></i>
            </button>
            <a class="navbar-brand" href="../../index.php">Network IT Club</a>
        </div>
    </nav>

    <div class="dashboard-container">
        <div class="sidebar" id="sidebar">
            <div class="sidebar-profile">
                <div class="profile-avatar"><i class="fa-solid fa-user"></i></div>
                <span class="profile-role"><?php echo ucfirst(htmlspecialchars($logged_in_role)); ?></span>
                <span class="profile-name"><?php echo htmlspecialchars($logged_in_username); ?></span>
            </div>

            <h6 class="sidebar-heading">MAIN NAVIGATION</h6>
            <div class="list-group list-group-flush">
                <?php
                // Cetak menu di sini (variabel-variabel sudah didefinisikan di atas)
                foreach ($menu_items as $item) {
                    list($text, $link, $icon) = $item;
                    $has_submenu = isset($item[3]);

                    // $path_to_compare sudah didefinisikan di atas
                    $is_active = ($path_to_compare == $link);

                    if ($has_submenu) {
                        $submenu_items = $item[3];
                        $is_parent_active = false;
                        foreach ($submenu_items as $sub_item) {
                            if ($path_to_compare == $sub_item[1]) {
                                $is_parent_active = true;
                                break;
                            }
                        }
                        echo '<a href="#submenu-' . str_replace(' ', '-', strtolower($text)) . '" data-bs-toggle="collapse" class="list-group-item list-group-item-action ' . ($is_parent_active ? 'active' : '') . '" aria-expanded="' . ($is_parent_active ? 'true' : 'false') . '">';
                        echo '<i class="' . htmlspecialchars($icon) . '"></i> ' . htmlspecialchars($text);
                        echo '<i class="fa-solid fa-chevron-down ms-auto"></i>';
                        echo '</a>';
                        echo '<div class="collapse ' . ($is_parent_active ? 'show' : '') . '" id="submenu-' . str_replace(' ', '-', strtolower($text)) . '">';
                        echo '<div class="list-group ps-4">';
                        foreach ($submenu_items as $sub_item) {
                            $is_sub_active = ($path_to_compare == $sub_item[1]);
                            echo '<a href="' . htmlspecialchars($sub_item[1]) . '" class="list-group-item list-group-item-action ' . ($is_sub_active ? 'active' : '') . '">';
                            echo '<i class="' . htmlspecialchars($sub_item[2]) . '"></i> ' . htmlspecialchars($sub_item[0]);
                            echo '</a>';
                        }
                        echo '</div></div>';
                    } else {
                        echo '<a href="' . htmlspecialchars($link) . '" class="list-group-item list-group-item-action ' . ($is_active ? 'active' : '') . '">';
                        echo '<i class="' . htmlspecialchars($icon) . '"></i> ' . htmlspecialchars($text);
                        echo '</a>';
                    }
                }
                ?>
                <h6 class="sidebar-heading mt-4">SETTINGS</h6>
                <a href="../settings.php" class="list-group-item list-group-item-action <?php echo ($path_to_compare == 'settings.php') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-gear"></i> Pengaturan
                </a>
                <a href="../logout.php" class="list-group-item list-group-item-action">
                    <i class="fa-solid fa-right-from-bracket"></i> Logout
                </a>
            </div>
        </div>

        <div class="main-content">
            <div class="page-content-area">
                <div class="content-wrapper">
                    <div class="container-fluid">
                        <h1 class="h3 mb-4 text-gray-800"><?php echo $page_title; ?></h1>

                        <?php if ($message): ?>
                            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                                <?php echo htmlspecialchars($message); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <div class="card shadow mb-4 bg-dark text-white">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Edit About Section Konten</h6>
                            </div>
                            <div class="card-body">
                                <form action="index.php" method="POST">
                                    <div class="mb-3">
                                        <label for="about_title" class="form-label">Judul "Apa Itu NIC?"</label>
                                        <input type="text" class="form-control" id="about_title" name="about_title" value="<?php echo htmlspecialchars($about_content['about_title']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="about_description" class="form-label">Deskripsi NIC</label>
                                        <textarea class="form-control" id="about_description" name="about_description" rows="5" required><?php echo htmlspecialchars($about_content['about_description']); ?></textarea>
                                    </div>
                                    <button type="submit" name="update_about_section" class="btn btn-primary">Simpan Perubahan About Section</button>
                                </form>
                            </div>
                        </div>

                        <div class="card shadow mb-4 bg-dark text-white">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Kelola Soft Skills</h6>
                            </div>
                            <div class="card-body">
                                <?php if ($message_skill_add): ?>
                                    <div class="alert alert-<?php echo $message_type_skill_add; ?> alert-dismissible fade show" role="alert">
                                        <?php echo htmlspecialchars($message_skill_add); ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                <?php endif; ?>
                                <?php if ($message_skill_edit): ?>
                                    <div class="alert alert-<?php echo $message_type_skill_edit; ?> alert-dismissible fade show" role="alert">
                                        <?php echo htmlspecialchars($message_skill_edit); ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                <?php endif; ?>
                                <?php if ($message_skill_delete): ?>
                                    <div class="alert alert-<?php echo $message_type_skill_delete; ?> alert-dismissible fade show" role="alert">
                                        <?php echo htmlspecialchars($message_skill_delete); ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                <?php endif; ?>

                                <form action="index.php" method="POST" class="mb-4">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="new_skill_name" placeholder="Nama Soft Skill Baru" required>
                                        <input type="hidden" name="new_skill_type" value="soft">
                                        <button type="submit" name="add_skill" class="btn btn-success">Tambah Soft Skill</button>
                                    </div>
                                </form>

                                <ul class="list-group">
                                    <?php if (!empty($soft_skills_data)): ?>
                                        <?php foreach ($soft_skills_data as $skill): ?>
                                            <li class="list-group-item d-flex justify-content-between align-items-center bg-secondary text-white">
                                                <span id="skill_name_<?php echo $skill['id']; ?>"><?php echo htmlspecialchars($skill['name']); ?></span>
                                                <div>
                                                    <button type="button" class="btn btn-sm btn-info me-2" data-bs-toggle="modal" data-bs-target="#editSkillModal"
                                                            data-id="<?php echo $skill['id']; ?>" data-name="<?php echo htmlspecialchars($skill['name']); ?>">
                                                        Edit
                                                    </button>
                                                    <form action="index.php" method="POST" class="d-inline">
                                                        <input type="hidden" name="skill_id_to_delete" value="<?php echo $skill['id']; ?>">
                                                        <button type="submit" name="delete_skill" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus skill ini?');">
                                                            Hapus
                                                        </button>
                                                    </form>
                                                </div>
                                            </li>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <li class="list-group-item bg-secondary text-white">Belum ada Soft Skill yang ditambahkan.</li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>

                        <div class="card shadow mb-4 bg-dark text-white">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Kelola Hard Skills</h6>
                            </div>
                            <div class="card-body">
                                <form action="index.php" method="POST" class="mb-4">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="new_skill_name" placeholder="Nama Hard Skill Baru" required>
                                        <input type="hidden" name="new_skill_type" value="hard">
                                        <button type="submit" name="add_skill" class="btn btn-success">Tambah Hard Skill</button>
                                    </div>
                                </form>

                                <ul class="list-group">
                                    <?php if (!empty($hard_skills_data)): ?>
                                        <?php foreach ($hard_skills_data as $skill): ?>
                                            <li class="list-group-item d-flex justify-content-between align-items-center bg-secondary text-white">
                                                <span id="skill_name_<?php echo $skill['id']; ?>"><?php echo htmlspecialchars($skill['name']); ?></span>
                                                <div>
                                                    <button type="button" class="btn btn-sm btn-info me-2" data-bs-toggle="modal" data-bs-target="#editSkillModal"
                                                            data-id="<?php echo $skill['id']; ?>" data-name="<?php echo htmlspecialchars($skill['name']); ?>">
                                                        Edit
                                                    </button>
                                                    <form action="index.php" method="POST" class="d-inline">
                                                        <input type="hidden" name="skill_id_to_delete" value="<?php echo $skill['id']; ?>">
                                                        <button type="submit" name="delete_skill" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus skill ini?');">
                                                            Hapus
                                                        </button>
                                                    </form>
                                                </div>
                                            </li>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <li class="list-group-item bg-secondary text-white">Belum ada Hard Skill yang ditambahkan.</li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editSkillModal" tabindex="-1" aria-labelledby="editSkillModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content bg-dark text-white">
                <div class="modal-header">
                    <h5 class="modal-title" id="editSkillModalLabel">Edit Skill</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="index.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="skill_id" id="edit_skill_id">
                        <div class="mb-3">
                            <label for="edited_skill_name" class="form-label">Nama Skill</label>
                            <input type="text" class="form-control" id="edited_skill_name" name="edited_skill_name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="edit_skill" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        var editSkillModal = document.getElementById('editSkillModal');
        editSkillModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget; // Button that triggered the modal
            var skillId = button.getAttribute('data-id'); // Extract info from data-id attribute
            var skillName = button.getAttribute('data-name'); // Extract info from data-name attribute

            var modalIdInput = editSkillModal.querySelector('#edit_skill_id');
            var modalNameInput = editSkillModal.querySelector('#edited_skill_name');

            modalIdInput.value = skillId;
            modalNameInput.value = skillName;
        });
    </script>

<?php
// FOOTER DASHBOARD
require_once '../dashboard_footer.php';
?>