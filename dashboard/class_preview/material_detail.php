<?php
// material_detail.php
session_start();
include '../../includes/inc_koneksi.php'; // Pastikan path ini benar
include '../dashboard_header.php'; // Pastikan path ini benar

$material_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($material_id > 0) {
    // Query untuk mengambil detail materi
    $sql_material = "SELECT m.id, m.title, m.content, m.file_url, m.material_type, 
                            c.name as category_name, cl.name as class_name, cl.id as class_id
                     FROM materials m
                     LEFT JOIN categories c ON m.category_id = c.id
                     LEFT JOIN classes cl ON m.class_id = cl.id
                     WHERE m.id = ?";
    $stmt_material = $koneksi->prepare($sql_material);

    if ($stmt_material) {
        $stmt_material->bind_param("i", $material_id);
        $stmt_material->execute();
        $result_material = $stmt_material->get_result();
        $material = $result_material->fetch_assoc();
        $stmt_material->close();

        if ($material) {
            ?>
            <div class="material-detail-wrapper">
                <div class="material-detail-content">
                    <p class="breadcrumbs">
                        <?php if (!empty($material['class_name'])): ?>
                            <a href="class_detail.php?id=<?php echo htmlspecialchars($material['class_id']); ?>"><?php echo htmlspecialchars($material['class_name']); ?></a>
                             &gt; 
                        <?php endif; ?>
                        <span><?php echo htmlspecialchars($material['title']); ?></span>
                    </p>
                    <h1><?php echo htmlspecialchars($material['title']); ?></h1>
                    <?php if (!empty($material['category_name'])): ?>
                        <p class="category-info">Kategori: <span><?php echo htmlspecialchars($material['category_name']); ?></span></p>
                    <?php endif; ?>

                    <div class="material-full-content">
                        <?php
                        // Logika tampilan berdasarkan material_type
                        if ($material['material_type'] == 'text' && !empty($material['content'])) {
                            // Tampilkan konten teks/HTML dari Summernote
                            echo $material['content']; // Summernote menghasilkan HTML, jadi tidak perlu htmlspecialchars
                        } elseif ($material['material_type'] == 'video' && !empty($material['file_url'])) {
                            // Tampilkan video embed dari file_url
                            // Deteksi platform video (YouTube/Vimeo) dan buat URL embed yang sesuai
                            $video_embed_url = '';
                            $url = $material['file_url'];

                            // Contoh deteksi YouTube
                            if (strpos($url, 'youtube.com/watch?v=') !== false) {
                                $video_id = explode('v=', $url);
                                $video_id = end($video_id);
                                $video_id = explode('&', $video_id)[0]; // Handle extra parameters
                                $video_embed_url = "https://www.youtube.com/embed/" . htmlspecialchars($video_id);
                            } 
                            // Contoh deteksi YouTube Short
                            else if (strpos($url, 'youtu.be/') !== false) {
                                $video_id = explode('youtu.be/', $url);
                                $video_id = end($video_id);
                                $video_id = explode('?', $video_id)[0]; // Handle extra parameters
                                $video_embed_url = "https://www.youtube.com/embed/" . htmlspecialchars($video_id);
                            }
                            // Contoh deteksi Vimeo
                            else if (strpos($url, 'vimeo.com/') !== false) {
                                $video_id = explode('vimeo.com/', $url);
                                $video_id = end($video_id);
                                $video_embed_url = "https://player.vimeo.com/video/" . htmlspecialchars($video_id);
                            }
                            // Anda bisa menambahkan deteksi untuk platform lain jika diperlukan

                            if (!empty($video_embed_url)) {
                                ?>
                                <div class="video-embed-full responsive-iframe-container">
                                    <iframe
                                        src="<?php echo $video_embed_url; ?>"
                                        frameborder="0"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                        allowfullscreen>
                                    </iframe>
                                </div>
                                <?php
                            } else {
                                echo "<p>Tautan video tidak dapat ditampilkan atau format tidak didukung.</p>";
                                echo "<p><a href=\"" . htmlspecialchars($url) . "\" target=\"_blank\">Buka video di tab baru</a></p>";
                            }
                        } 
                        // Tampilkan link download/view untuk tipe file lainnya
                        ?>
                    </div>

                    <?php if (!empty($material['file_url'])): // Tampilkan link file_url jika ada, terlepas dari tipe content yang di atas ?>
                        <?php
                        // Deteksi tipe file untuk ikon download
                        $icon_class = 'fa-download';
                        $display_text = 'Download File';
                        $file_extension_full = strtolower(pathinfo($material['file_url'], PATHINFO_EXTENSION));

                        if ($file_extension_full == 'pdf') {
                            $icon_class = 'fa-file-pdf';
                            $display_text = 'Lihat/Download PDF';
                        } elseif (in_array($file_extension_full, ['doc', 'docx'])) {
                            $icon_class = 'fa-file-word';
                            $display_text = 'Download Dokumen Word';
                        } elseif (in_array($file_extension_full, ['ppt', 'pptx'])) {
                            $icon_class = 'fa-file-powerpoint';
                            $display_text = 'Download Presentasi PowerPoint';
                        } elseif (in_array($file_extension_full, ['xls', 'xlsx'])) {
                            $icon_class = 'fa-file-excel';
                            $display_text = 'Download Spreadsheet Excel';
                        } elseif (in_array($file_extension_full, ['zip', 'rar', '7z', 'tar', 'gz'])) {
                            $icon_class = 'fa-file-archive';
                            $display_text = 'Download Arsip';
                        } elseif (in_array($file_extension_full, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'])) {
                            $icon_class = 'fa-file-image';
                            $display_text = 'Lihat Gambar';
                        } elseif (in_array($material['material_type'], ['video']) && !empty($video_embed_url)) {
                            // Jika sudah di-embed di atas, mungkin tidak perlu link download lagi
                            // Kecuali Anda ingin memberikan opsi download terpisah
                            $display_text = 'Buka Video Eksternal';
                            $icon_class = 'fa-video';
                        }
                        ?>
                        <?php if ($material['material_type'] !== 'video' || empty($video_embed_url)): // Hindari dua kali link video jika sudah di-embed ?>
                            <a href="<?php echo htmlspecialchars($material['file_url']); ?>" target="_blank" class="download-link-full">
                                <i class="fas <?php echo $icon_class; ?>"></i> <?php echo $display_text; ?>
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
            </div>
            </div>
            </div>
            <?php
        } else {
            echo "<p style='text-align: center; color: var(--white); font-size: 1.8rem; padding: 50px;'>Materi tidak ditemukan.</p>";
        }
    } else {
        echo "<p style='text-align: center; color: var(--white); font-size: 1.8rem; padding: 50px;'>Kesalahan database saat menyiapkan query: " . htmlspecialchars($koneksi->error) . "</p>";
    }
} else {
    echo "<p style='text-align: center; color: var(--white); font-size: 1.8rem; padding: 50px;'>ID materi tidak valid.</p>";
}

$koneksi->close();
include '../dashboard_footer.php'; // Pastikan path ini benar
?>