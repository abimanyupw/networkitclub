<?php
// dashboard/store/edit.php

// Aktifkan error reporting penuh untuk debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../../includes/inc_koneksi.php';

// --- Variabel untuk Mengontrol Script di Footer ---
// Kita set ini ke FALSE agar script yang hanya untuk materi tidak berjalan di halaman ini.
$enable_material_scripts = false;
// --- Akhir Kontrol Script ---

// Memuat header dashboard (ini mungkin mengandung JS/CSS yang mengganggu)
// Pastikan path ini benar
require_once '../dashboard_header.php';

// Redirect jika user belum login atau role tidak diizinkan
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'developer', 'teknisi'])) {
    header("Location: ../../login.php");
    exit();
}

$page_title = "Edit Barang Store";
$error = '';
$item_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Redirect jika ID barang tidak valid (0 atau tidak ada)
if ($item_id === 0) {
    header("Location: index.php");
    exit();
}

// --- Mulai Pengambilan Data ---
$item_query = mysqli_query($koneksi, "SELECT * FROM store_items WHERE id = $item_id");
if (!$item_query) {
    $error .= " Kesalahan database saat mengambil data barang: " . mysqli_error($koneksi);
    $item = null;
} else {
    $item = mysqli_fetch_assoc($item_query);
}

if (!$item) {
    $error .= " Barang dengan ID {$item_id} tidak ditemukan di database.";
    header("Location: index.php");
    exit();
}

$item_badges_query = mysqli_query($koneksi, "SELECT badge_id, badge_count_required FROM store_item_badge_rules WHERE store_item_id = $item_id");
$item_badges = [];
if ($item_badges_query) {
    while ($row = mysqli_fetch_assoc($item_badges_query)) {
        $item_badges[] = $row;
    }
} else {
    $error .= " Kesalahan database saat mengambil aturan badge: " . mysqli_error($koneksi);
}

$badges_result = mysqli_query($koneksi, "SELECT id, name FROM badges ORDER BY name");
$badges_data = [];
if ($badges_result) {
    while ($badge_row = mysqli_fetch_assoc($badges_result)) {
        $badges_data[] = $badge_row;
    }
} else {
    $error .= " Kesalahan database saat mengambil daftar badge: " . mysqli_error($koneksi);
}
// --- Akhir Pengambilan Data ---


// --- Proses Form Saat Disubmit (Metode POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($koneksi, $_POST['name'] ?? '');
    $description = mysqli_real_escape_string($koneksi, $_POST['description'] ?? '');
    $stock = (int)($_POST['stock'] ?? 0);

    $image_url = $item['image_url'];
    $upload_success = true;

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK && !empty($_FILES['image']['name'])) {
        $target_dir = "../../uploads/";
        $image_name = basename($_FILES['image']['name']);
        $target_file = $target_dir . $image_name;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            if (!empty($item['image_url']) && file_exists($target_dir . $item['image_url']) && $item['image_url'] !== $image_name) {
                unlink($target_dir . $item['image_url']);
            }
            $image_url = $image_name;
        } else {
            $upload_success = false;
            $error .= " Gagal mengunggah gambar baru. Kode error: " . $_FILES['image']['error'];
            $image_url = $item['image_url'];
        }
    }

    if ($upload_success) {
        $query = "UPDATE store_items SET name = '$name', description = '$description', image_url = '$image_url', stock = $stock WHERE id = $item_id";

        if (mysqli_query($koneksi, $query)) {
            $delete_rules_query = "DELETE FROM store_item_badge_rules WHERE store_item_id = $item_id";
            if (!mysqli_query($koneksi, $delete_rules_query)) {
                $error .= " Gagal menghapus aturan badge lama: " . mysqli_error($koneksi);
            }

            if (isset($_POST['badge_ids']) && is_array($_POST['badge_ids']) &&
                isset($_POST['badge_counts']) && is_array($_POST['badge_counts'])) {

                foreach ($_POST['badge_ids'] as $index => $badge_id) {
                    $badge_id = (int)($badge_id ?? 0);
                    $count_required = (int)($_POST['badge_counts'][$index] ?? 0);

                    if ($badge_id > 0 && $count_required > 0) {
                        $insert_rule_query = "INSERT INTO store_item_badge_rules (store_item_id, badge_id, badge_count_required) VALUES ($item_id, $badge_id, $count_required)";
                        if (!mysqli_query($koneksi, $insert_rule_query)) {
                            $error .= " Gagal menambahkan aturan badge: " . mysqli_error($koneksi);
                        }
                    }
                }
            }

            if (empty($error)) {
                header("Location: index.php");
                exit();
            }
        } else {
            $error .= " Gagal mengupdate data barang: " . mysqli_error($koneksi);
        }
    }
}
?>

<div class="content-wrapper mb-5">
    <section class="content-header">
        <h1><?= $page_title ?></h1>
        <ol class="breadcrumb gap-2">
            <li><a href="../../index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="index.php">Store</a></li>
        </ol>
    </section>

    <div class="container mt-4">
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Nama Barang</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($item['name'] ?? '') ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Deskripsi</label>
                <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($item['description'] ?? '') ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Gambar</label>
                <input type="file" name="image" class="form-control">
                <?php if (!empty($item['image_url'])): ?>
                    <small class="form-text text-muted">Gambar saat ini: <a href="../../uploads/<?= htmlspecialchars($item['image_url']) ?>" target="_blank"><?= htmlspecialchars($item['image_url']) ?></a></small>
                <?php endif; ?>
            </div>

            <div id="badge-section">
                <label class="form-label">Syarat Badge</label>
                <?php if (!empty($item_badges)): ?>
                    <?php foreach ($item_badges as $key => $rule): ?>
                        <div class="row g-2 mb-2 badge-rule-group">
                            <div class="col-md-6">
                                <select name="badge_ids[]" class="form-control" required>
                                    <option value="">-- Pilih Badge --</option>
                                    <?php
                                    foreach ($badges_data as $badge):
                                        $selected = ((string)$badge['id'] === (string)($rule['badge_id'] ?? '')) ? 'selected' : '';
                                        echo "<option value=\"" . htmlspecialchars($badge['id']) . "\" {$selected}>" . htmlspecialchars($badge['name']) . "</option>";
                                    endforeach;
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <input type="number" name="badge_counts[]" class="form-control" min="1" placeholder="Jumlah badge" value="<?= htmlspecialchars($rule['badge_count_required'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-danger remove-badge"><i class="fa fa-trash"></i></button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="row g-2 mb-2 badge-rule-group">
                        <div class="col-md-6">
                            <select name="badge_ids[]" class="form-control" required>
                                <option value="">-- Pilih Badge --</option>
                                <?php
                                foreach ($badges_data as $badge):
                                    echo "<option value=\"" . htmlspecialchars($badge['id']) . "\">" . htmlspecialchars($badge['name']) . "</option>";
                                endforeach;
                                ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input type="number" name="badge_counts[]" class="form-control" min="1" placeholder="Jumlah badge" required>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-danger remove-badge"><i class="fa fa-trash"></i></button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <button type="button" class="btn btn-secondary mb-3" id="add-badge-btn"><i class="fa fa-plus"></i> Tambah Syarat Badge</button>

            <div class="mb-3">
                <label class="form-label">Stok</label>
                <input type="number" name="stock" class="form-control" min="0" value="<?= htmlspecialchars($item['stock'] ?? '') ?>" required>
            </div>

            <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Simpan Perubahan</button>
            <a href="index.php" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        // --- JavaScript Pemaksa Pengisian Form (Workaround Agresif) ---
        function setInputValue(name, value) {
            const input = document.querySelector(`[name="${name}"]`);
            if (input && input.value !== value) {
                input.value = value;
                console.log(`[DEBUG_JS] Set ${name} to: ${value}`);
            }
        }

        function setTextareaValue(name, value) {
            const textarea = document.querySelector(`[name="${name}"]`);
            if (textarea && textarea.value !== value) {
                textarea.value = value;
                console.log(`[DEBUG_JS] Set ${name} to: ${value}`);
            }
        }

        function setSelectedOption(name, value) {
            const select = document.querySelector(`[name="${name}"]`);
            if (select) {
                let found = false;
                for (let i = 0; i < select.options.length; i++) {
                    if (select.options[i].value === value) {
                        select.selectedIndex = i;
                        found = true;
                        console.log(`[DEBUG_JS] Selected ${name} to: ${value}`);
                        break;
                    }
                }
                if (!found && select.value !== value) {
                    console.warn(`[DEBUG_JS] Option with value "${value}" not found for "${name}" (current: "${select.value}")`);
                }
            }
        }

        console.log("[DEBUG_JS] Memulai pemaksaan pengisian form...");

        setInputValue('name', '<?= htmlspecialchars($item['name'] ?? '') ?>');
        setTextareaValue('description', '<?= htmlspecialchars($item['description'] ?? '') ?>');
        setInputValue('stock', '<?= htmlspecialchars($item['stock'] ?? '') ?>');

        <?php if (!empty($item_badges)): ?>
            const badgeGroups = document.querySelectorAll('#badge-section .badge-rule-group');
            <?php foreach ($item_badges as $key => $rule): ?>
                if (badgeGroups[<?= $key ?>]) {
                    const selectElement = badgeGroups[<?= $key ?>].querySelector('select[name="badge_ids[]"]');
                    const inputElement = badgeGroups[<?= $key ?>].querySelector('input[name="badge_counts[]"]');

                    if (selectElement) {
                        setSelectedOption(`badge_ids[<?= $key ?>]`, '<?= htmlspecialchars($rule['badge_id'] ?? '') ?>');
                    }
                    if (inputElement) {
                        setInputValue(`badge_counts[<?= $key ?>]`, '<?= htmlspecialchars($rule['badge_count_required'] ?? '') ?>');
                    }
                }
            <?php endforeach; ?>
        <?php endif; ?>

        console.log("[DEBUG_JS] Pemaksaan pengisian form selesai.");

        // --- JavaScript Asli untuk Menambah/Menghapus Badge Rules ---

        function addBadgeRule() {
            const section = document.getElementById('badge-section');
            const original = section.querySelector('.badge-rule-group:last-child');
            const clone = original.cloneNode(true);

            clone.querySelectorAll('select, input').forEach(el => {
                el.value = '';
                if (el.tagName === 'SELECT') {
                    const defaultOption = el.querySelector('option[value=""]');
                    if (defaultOption) {
                        defaultOption.selected = true;
                    }
                }
            });

            section.appendChild(clone);
        }

        const addBadgeBtn = document.getElementById('add-badge-btn');
        if (addBadgeBtn) {
            addBadgeBtn.addEventListener('click', addBadgeRule);
        }

        document.addEventListener('click', function (e) {
            if (e.target.closest('.remove-badge')) {
                const group = e.target.closest('.badge-rule-group');
                const allGroups = document.querySelectorAll('.badge-rule-group');

                if (allGroups.length > 1) {
                    group.remove();
                } else {
                    group.querySelectorAll('select, input').forEach(el => {
                        el.value = '';
                        if (el.tagName === 'SELECT') {
                            const defaultOption = el.querySelector('option[value=""]');
                            if (defaultOption) {
                                defaultOption.selected = true;
                            }
                        }
                    });
                }
            }
        });
    });
</script>

<?php require_once '../dashboard_footer.php'; ?>