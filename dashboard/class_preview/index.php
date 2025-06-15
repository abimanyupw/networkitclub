 <?php
 session_start();

require_once '../../includes/inc_koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}

$page_title = "Class Preview";

// --- PENGAMBILAN DATA CLASS SECTION ---
$classes = [];
$query_classes = "SELECT id, name, description, image_url FROM classes ORDER BY name ASC";
$result_classes = mysqli_query($koneksi, $query_classes);

if ($result_classes) {
    while ($row = mysqli_fetch_assoc($result_classes)) {
        $classes[] = $row;
    }
    mysqli_free_result($result_classes);
} else {
    error_log("Error fetching classes data: " . mysqli_error($koneksi));
}
require_once '../dashboard_header.php';

?>

<div class="content-wrapper mb-5" style="min-width: 100%;">
    <section class="content-header">
        <h1><?php echo $page_title; ?></h1>
        <ol class="breadcrumb gap-2">
            <li><a href="../../index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active"><?php echo $page_title; ?></li>
        </ol>
    </section>
 
 <div class="class">
            <h1>Daftar Kelas</h1>
            <div class="class-card">
                <?php
                if (!empty($classes)) {
                    foreach ($classes as $row) {
                        ?>
                        <a href="class_detail.php?id=<?php echo htmlspecialchars($row['id']); ?>" class="class-item minimalist">
                            <div class="card-image-wrapper">
                                <img src="<?php echo htmlspecialchars($row['image_url']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?> Class">
                            </div>
                            <div class="card-content">
                                <h1><?php echo htmlspecialchars($row['name']); ?></h1>
                                <p><?php echo htmlspecialchars($row['description']); ?></p>
                            </div>
                        </a>
                        <?php
                    }
                } else {
                    echo "<p style='text-align: center; color: var(--white); font-size: 1.5rem;'>Belum ada kelas yang tersedia.</p>";
                }
                ?>
            </div>
        </div>
</div>

<?php
$koneksi->close();
include '../dashboard_footer.php';
?>