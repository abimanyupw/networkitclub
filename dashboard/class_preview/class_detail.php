<?php
// class_detail.php
session_start(); // Mulai session

include '../../includes/inc_koneksi.php'; // Koneksi database

// Mengambil ID kelas dari parameter URL (misal: ?id=1)
$class_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($class_id > 0) {
    // Query untuk mengambil detail kelas berdasarkan ID
    $sql_class = "SELECT id, name, description, image_url FROM classes WHERE id = ?"; // Ambil image_url
    $stmt_class = $koneksi->prepare($sql_class);
    $stmt_class->bind_param("i", $class_id);
    $stmt_class->execute();
    $result_class = $stmt_class->get_result();
    $class = $result_class->fetch_assoc();

    if ($class) {
        // Ambil image_url kelas untuk digunakan sebagai thumbnail default SEMUA materi
        // Fallback jika image_url kelas itu sendiri kosong
        $class_thumbnail_default_url = !empty($class['image_url']) ? htmlspecialchars($class['image_url']) : 'img/default-class-thumbnail.png';

        // --- Bagian untuk Kategori Materi (Sidebar) ---
        $sql_categories = "SELECT c.id, c.name FROM categories c
                            JOIN materials m ON c.id = m.category_id
                            WHERE m.class_id = ?
                            GROUP BY c.id, c.name
                            ORDER BY c.name ASC";
        $stmt_categories = $koneksi->prepare($sql_categories);
        $stmt_categories->bind_param("i", $class_id);
        $stmt_categories->execute();
        $result_categories = $stmt_categories->get_result();
        $categories = [];
        while ($row = $result_categories->fetch_assoc()) {
            $categories[] = $row;
        }
        $stmt_categories->close();

        // --- Bagian untuk Materi Pembelajaran ---
        $selected_category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;

        // Penting: Tambahkan 'm.id' ke SELECT untuk digunakan sebagai parameter ID materi
        $sql_materials = "SELECT m.id, m.title, m.content, m.file_url, m.material_type, c.name as category_name
                            FROM materials m
                            LEFT JOIN categories c ON m.category_id = c.id
                            WHERE m.class_id = ?";

        if ($selected_category_id > 0) {
            $sql_materials .= " AND m.category_id = ?";
        }
        $sql_materials .= " ORDER BY c.name, m.title ASC";

        $stmt_materials = $koneksi->prepare($sql_materials);

        if ($selected_category_id > 0) {
            $stmt_materials->bind_param("ii", $class_id, $selected_category_id);
        } else {
            $stmt_materials->bind_param("i", $class_id);
        }

        $stmt_materials->execute();
        $result_materials = $stmt_materials->get_result();
        $materials = [];
        while ($row = $result_materials->fetch_assoc()) {
            $materials[] = $row;
        }
        include '../dashboard_header.php';
        ?>
        
        <div class="class-detail-wrapper">
            <aside class="sidebar-material">
                <h2>Kategori Materi</h2>
                <ul>
                    <li><a href="class_detail.php?id=<?php echo $class['id']; ?>" class="<?php echo ($selected_category_id == 0) ? 'active' : ''; ?>">Semua Materi</a></li>
                    <?php if (empty($categories)): ?>
                        <li>Tidak ada kategori</li>
                    <?php else: ?>
                        <?php foreach ($categories as $category): ?>
                            <li>
                                <a href="class_detail.php?id=<?php echo $class['id']; ?>&category=<?php echo $category['id']; ?>"
                                   class="<?php echo ($selected_category_id == $category['id']) ? 'active' : ''; ?>">
                                    <?php echo $category['name']; ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </aside>
            <article class="class-content">
                <h1><?php echo $class['name']; ?></h1>
                <p class="class-description"><?php echo $class['description']; ?></p>

                <hr>

                <h2>Materi Pembelajaran</h2>
                <?php if (empty($materials)): ?>
                    <p class="no-materials">Belum ada materi untuk kelas ini.</p>
                <?php else: ?>
                    <div class="materials-list">
                        <?php foreach ($materials as $material):
                            // Deteksi kelas CSS untuk materi PDF (tetap dipertahankan jika ada CSS khusus untuk PDF)
                            $material_card_class = '';
                            if (!empty($material['file_url'])) {
                                $file_extension = strtolower(pathinfo($material['file_url'], PATHINFO_EXTENSION));
                                if ($file_extension == 'pdf') {
                                    $material_card_class = 'material-pdf-card';
                                }
                            }
                        ?>
                            <a href="material_detail.php?id=<?php echo $material['id']; ?>" class="material-item-link">
                                <div class="material-item <?php echo $material_card_class; ?>">
                                    <?php
                                    // LOGIKA THUMBNAIL BARU: Selalu gunakan image_url kelas
                                    $thumbnail_html = '
                                        <div class="material-file-thumbnail">
                                            <img src="' . $class_thumbnail_default_url . '" alt="Class Material Thumbnail" class="file-image-preview">
                                            ';
                                    // Tambahkan ikon play jika ini video (opsional, untuk visual saja)
                                    if ($material['material_type'] == 'video' && !empty($material['content'])) {
                                        $thumbnail_html .= '<i class="fas fa-play-circle play-icon"></i>';
                                    }
                                    $thumbnail_html .= '</div>';

                                    echo $thumbnail_html;
                                    ?>

                                    <h3><?php echo htmlspecialchars($material['title']); ?></h3>
                                    <p class="material-short-desc"><?php echo nl2br(substr($material['content'], 0, 100)) . (strlen($material['content']) > 100 ? '...' : ''); ?></p>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </article>
        </div>

        <?php
    } else {
        echo "<p style='text-align: center; color: var(--white); font-size: 1.8rem; padding: 50px;'>Kelas tidak ditemukan.</p>";
    }
} else {
    echo "<p style='text-align: center; color: var(--white); font-size: 1.8rem; padding: 50px;'>ID kelas tidak valid. Silakan kembali ke halaman utama.</p>";
}

$koneksi->close();
include '../dashboard_footer.php';
?>