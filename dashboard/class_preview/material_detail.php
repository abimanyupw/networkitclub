<?php
// material_detail.php
// Pastikan ob_start() ada di baris paling atas untuk mencegah masalah "headers already sent"
ob_start();
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
            // Inisialisasi video_embed_url di sini agar bisa diakses di bawah
            $video_embed_url = '';
            $url_for_embedding = ''; // Variabel untuk URL yang akan di-embed

            // --- Lakukan deteksi video dari file_url jika ada ---
            // Fungsi extractVideoId harus didefinisikan atau di-include
            // Jika Anda mendefinisikannya di sini, pastikan itu di luar blok ini agar tidak didefinisikan ulang
            // setiap kali fungsi ini dipanggil
            if (!function_exists('extractVideoId')) {
                function extractVideoId($url, $platform) {
                    $id = false;
                    switch ($platform) {
                        case 'youtube':
                            $patterns = [
                                '/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com|youtu\.be)\/(?:watch\?v=|embed\/|v\/|shorts\/|e\/|live\/|playlist\?list=|user\/(?:[a-zA-Z0-9_-]+\/videos\/|channel\/[a-zA-Z0-9_-]+\/|)\/?)?([a-zA-Z0-9_-]{11})(?:\S+)?/',
                                // Tambahkan pola untuk URL yang mungkin dihasilkan Googleusercontent atau streams jika relevan
                                '/(?:youtube\.com\/(?:c|user|channel)\/[a-zA-Z0-9_-]+\/)?videos\/?(?:[a-zA-Z0-9_-]+\/)?([a-zA-Z0-9_-]{11})/',
                                '/(?:youtube\.com\/(?:c|user|channel)\/[a-zA-Z0-9_-]+\/)?live\/?([a-zA-Z0-9_-]{11})/'
                            ];
                            foreach ($patterns as $pattern) {
                                if (preg_match($pattern, $url, $matches)) {
                                    $id = $matches[1];
                                    break;
                                }
                            }
                            break;
                        case 'vimeo':
                            if (preg_match('/vimeo\.com\/(?:video\/|channels\/\w+\/|groups\/\w+\/videos\/|album\/\d+\/video\/|)\/?(\d+)/', $url, $matches)) {
                                $id = $matches[1];
                            }
                            break;
                    }
                    return $id;
                }
            }


            if (!empty($material['file_url'])) {
                $url_for_embedding = $material['file_url'];

                $youtube_id = extractVideoId($url_for_embedding, 'youtube');
                if ($youtube_id) {
                    // Perhatikan: "https://www.youtube.com/embed/" ini adalah URL proxy yang SANGAT TIDAK STANDAR
                    // URL embed YouTube standar adalah: https://www.youtube.com/embed/VIDEO_ID
                    // Jika Anda menyimpan URL YouTube asli di file_url, lebih baik gunakan itu.
                    // Saya akan menggunakannya sesuai asumsi kode Anda, tapi sarankan untuk cek kembali
                    $video_embed_url = "https://www.youtube.com/embed/" . htmlspecialchars($youtube_id); // Menggunakan embed URL standar YouTube
                } else {
                    $vimeo_id = extractVideoId($url_for_embedding, 'vimeo');
                    if ($vimeo_id) {
                        $video_embed_url = "https://player.vimeo.com/video/" . htmlspecialchars($vimeo_id);
                    }
                }
            }
            // --- Akhir deteksi video dari file_url ---

            ?>
            <div class="material-detail-wrapper">
                <div class="material-detail-content">
                    <p class="breadcrumbs">
                        <?php if (!empty($material['class_name'])): ?>
                            <a href="class_detail.php?id=<?php echo htmlspecialchars($material['class_id']); ?>"><?php echo htmlspecialchars($material['class_name']); ?></a>
                             >
                        <?php endif; ?>
                        <span><?php echo htmlspecialchars($material['title']); ?></span>
                    </p>
                    <h1><?php echo htmlspecialchars($material['title']); ?></h1>
                    <?php if (!empty($material['category_name'])): ?>
                        <p class="category-info">Kategori: <span><?php echo htmlspecialchars($material['category_name']); ?></span></p>
                    <?php endif; ?>

                    <div class="material-full-content">
                        <?php
                        // Tampilkan video dari file_url jika ada dan dikenali
                        if (!empty($video_embed_url)) {
                            ?>
                            <div class="video-embed-full responsive-iframe-container mb-4">
                                <iframe
                                    src="<?php echo $video_embed_url; ?>"
                                    frameborder="0"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen>
                                </iframe>
                            </div>
                            <?php
                        }

                        // Tampilkan konten teks/HTML dari Summernote jika ada
                        // Ini adalah bagian di mana gambar dan video yang diunggah melalui Summernote akan dirender
                        if (!empty($material['content'])) {
                            echo $material['content']; // Summernote menghasilkan HTML, tidak perlu htmlspecialchars
                        }

                        // Tampilkan link download/view untuk tipe file lainnya atau jika video tidak di-embed
                        // Hanya tampilkan jika ada file_url dan material_type BUKAN video yang sudah di-embed
                        // atau jika itu adalah URL video yang tidak bisa di-embed (misal, file video langsung)
                        if (!empty($material['file_url']) && ($material['material_type'] !== 'video' || empty($video_embed_url))) {
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
                                // Tambahan: Jika ini adalah gambar yang disimpan sebagai file_url, tampilkan langsung
                                echo '<div class="image-display mt-4 mb-4 text-center">';
                                echo '<img src="' . htmlspecialchars($material['file_url']) . '" alt="' . htmlspecialchars($material['title']) . '" style="max-width: 100%; height: auto;">';
                                echo '</div>';
                                $display_text = ''; // Kosongkan teks download jika sudah ditampilkan
                            } elseif (in_array($file_extension_full, ['mp4', 'webm', 'ogg'])) { // Jika video di-upload sebagai file, bukan embed URL eksternal
                                $icon_class = 'fa-file-video';
                                $display_text = 'Tonton/Download Video';
                                // Tampilkan player video HTML5
                                echo '<div class="video-display mt-4 mb-4 text-center">';
                                echo '<video controls style="max-width: 100%; height: auto;">';
                                echo '<source src="' . htmlspecialchars($material['file_url']) . '" type="video/' . $file_extension_full . '">';
                                echo 'Browser Anda tidak mendukung tag video.';
                                echo '</video>';
                                echo '</div>';
                                $display_text = ''; // Kosongkan teks download jika sudah ditampilkan
                            }
                            
                            // Tampilkan tombol download/view hanya jika $display_text tidak kosong
                            if (!empty($display_text)) {
                            ?>
                                <p class="mt-4 d-flex justify-content-center align-items-center">
                                    <a href="<?php echo htmlspecialchars($material['file_url']); ?>" target="_blank" class="btn btn-outline-info btn-sm d-flex justify-content-center"style="width: 12rem;">
                                        <i class="fas <?php echo $icon_class; ?>"></i> <?php echo $display_text; ?>
                                    </a>
                                </p>
                            <?php
                            }
                        } elseif (!empty($material['file_url']) && $material['material_type'] === 'video' && !empty($video_embed_url)) {
                            // Ini adalah case untuk video YouTube/Vimeo yang sudah di-embed, tapi tetap ada opsi buka di tab baru
                            ?>
                            <p class="mt-4 d-flex justify-content-center align-items-center">
                                <a href="<?php echo htmlspecialchars($material['file_url']); ?>" target="_blank" class="btn btn-outline-info btn-sm d-flex justify-content-center" style="width: 20rem;">
                                    <i class="fas fa-external-link-alt"></i> Buka Video di Tab Baru
                                </a>
                            </p>
                        <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
            <?php
        } else {
            echo "<p style='text-align: center; color: var(--white); font-size: 1.8rem; padding: 50px;'>Materi tidak ditemukan.</p>";
        }
    } else {
        error_log("Kesalahan database saat menyiapkan query material_detail: " . $koneksi->error);
        echo "<p style='text-align: center; color: var(--white); font-size: 1.8rem; padding: 50px;'>Kesalahan database saat menyiapkan query: " . htmlspecialchars($koneksi->error) . "</p>";
    }
} else {
    echo "<p style='text-align: center; color: var(--white); font-size: 1.8rem; padding: 50px;'>ID materi tidak valid.</p>";
}

$koneksi->close();
include '../dashboard_footer.php'; // Pastikan path ini benar
ob_end_flush(); // Akhiri output buffering
?>