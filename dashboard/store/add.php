<?php
session_start();
require_once '../../includes/inc_koneksi.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'developer', 'teknisi'])) {
    header("Location: ../../login.php");
    exit();
}

$page_title = "Tambah Barang Store";
$error = '';
$badges = mysqli_query($koneksi, "SELECT id, name FROM badges ORDER BY name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($koneksi, $_POST['name']);
    $description = mysqli_real_escape_string($koneksi, $_POST['description']);
    $stock = (int)$_POST['stock'];

    $image_url = '';
    if ($_FILES['image']['name']) {
        $target_dir = "../../uploads/";
        $image_url = basename($_FILES['image']['name']);
        $target_file = $target_dir . $image_url;
        move_uploaded_file($_FILES['image']['tmp_name'], $target_file);
    }

    $query = "INSERT INTO store_items (name, description, image_url, stock) VALUES ('$name', '$description', '$image_url', $stock)";
    if (mysqli_query($koneksi, $query)) {
        $item_id = mysqli_insert_id($koneksi);

        if (!empty($_POST['badge_ids']) && is_array($_POST['badge_ids'])) {
            foreach ($_POST['badge_ids'] as $index => $badge_id) {
                $badge_id = (int)$badge_id;
                $count_required = (int)$_POST['badge_counts'][$index];
                if ($badge_id > 0 && $count_required > 0) {
                    mysqli_query($koneksi, "INSERT INTO store_item_badge_rules (store_item_id, badge_id, badge_count_required) VALUES ($item_id, $badge_id, $count_required)");
                }
            }
        }

        header("Location: index.php");
        exit();
    } else {
        $error = "Gagal menambahkan barang.";
    }
}

require_once '../dashboard_header.php';
?>

<div class="content-wrapper mb-5">
    <section class="content-header">
        <h1><?= $page_title ?></h1>
        <ol class="breadcrumb gap-2">
            <li><a href="../../index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="index.php">Store</a></li>
            <li class="active">Tambah</li>
        </ol>
    </section>

    <div class="container mt-4">
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Nama Barang</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Deskripsi</label>
                <textarea name="description" class="form-control" rows="3"></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Gambar</label>
                <input type="file" name="image" class="form-control">
            </div>

            <div id="badge-section">
                <label class="form-label">Syarat Badge</label>
                <div class="row g-2 mb-2 badge-rule-group">
                    <div class="col-md-6">
                        <select name="badge_ids[]" class="form-control" required>
                            <option value="">-- Pilih Badge --</option>
                            <?php mysqli_data_seek($badges, 0); while ($badge = mysqli_fetch_assoc($badges)): ?>
                                <option value="<?= $badge['id'] ?>"><?= htmlspecialchars($badge['name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="number" name="badge_counts[]" class="form-control" min="1" placeholder="Jumlah badge" required>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger remove-badge"><i class="fa fa-trash"></i></button>
                    </div>
                </div>
            </div>

            <button type="button" class="btn btn-secondary mb-3" id="add-badge-btn"><i class="fa fa-plus"></i> Tambah Syarat Badge</button>

            <div class="mb-3">
                <label class="form-label">Stok</label>
                <input type="number" name="stock" class="form-control" min="0" required>
            </div>

            <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Simpan</button>
            <a href="index.php" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</div>

<script>
    document.getElementById('add-badge-btn').addEventListener('click', function () {
        const section = document.querySelector('#badge-section');
        const original = section.querySelector('.badge-rule-group');
        const clone = original.cloneNode(true);

        clone.querySelectorAll('select, input').forEach(el => {
            el.value = '';
        });

        section.appendChild(clone);
    });

    document.addEventListener('click', function (e) {
        if (e.target.closest('.remove-badge')) {
            const group = e.target.closest('.badge-rule-group');
            const all = document.querySelectorAll('.badge-rule-group');
            if (all.length > 1) group.remove();
        }
    });
</script>

<?php require_once '../dashboard_footer.php'; ?>
