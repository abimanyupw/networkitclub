<?php
// index.php (Landing Page Utama)

require_once 'includes/inc_koneksi.php';

// --- PENGAMBILAN DATA HERO SECTION ---
$heading1 = '';
$heading2 = '';
$qr_code_path = '';

$query_hero = "SELECT heading1, heading2, qr_code_path FROM home_content WHERE id = 1";
$result_hero = mysqli_query($koneksi, $query_hero);

if ($result_hero) {
    $data_hero = mysqli_fetch_assoc($result_hero);
    if ($data_hero) {
        $heading1 = htmlspecialchars($data_hero['heading1'] ?? '');
        $heading2 = htmlspecialchars($data_hero['heading2'] ?? '');
        $qr_code_path = htmlspecialchars($data_hero['qr_code_path'] ?? '');
    }
    mysqli_free_result($result_hero);
} else {
    error_log("Error fetching hero section data: " . mysqli_error($koneksi));
}

// --- PENGAMBILAN DATA ABOUT SECTION ---
$about_title = 'Apa Itu NIC ?'; // Default
$about_description = '';

$query_about = "SELECT about_title, about_description FROM about_section WHERE id = 1";
$result_about = mysqli_query($koneksi, $query_about);

if ($result_about) {
    $data_about = mysqli_fetch_assoc($result_about);
    if ($data_about) {
        $about_title = htmlspecialchars($data_about['about_title'] ?? $about_title);
        $about_description = htmlspecialchars($data_about['about_description'] ?? '');
    }
    mysqli_free_result($result_about);
} else {
    error_log("Error fetching about section data: " . mysqli_error($koneksi));
}

// --- PENGAMBILAN DATA SKILLS ---
$soft_skills_list = [];
$hard_skills_list = [];

$query_skills = "SELECT type, name FROM skills ORDER BY type ASC, id ASC";
$result_skills = mysqli_query($koneksi, $query_skills);

if ($result_skills) {
    while ($row_skill = mysqli_fetch_assoc($result_skills)) {
        if ($row_skill['type'] == 'soft') {
            $soft_skills_list[] = htmlspecialchars($row_skill['name']);
        } else {
            $hard_skills_list[] = htmlspecialchars($row_skill['name']);
        }
    }
    mysqli_free_result($result_skills);
} else {
    error_log("Error fetching skills data: " . mysqli_error($koneksi));
}


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

// --- PENGAMBILAN DATA CONTACT SECTION ---
$contact_heading = 'Contact'; // Default
$map_embed = '';

$query_contact = "SELECT contact_heading, map_embed FROM contact_content WHERE id = 1";
$result_contact = mysqli_query($koneksi, $query_contact);

if ($result_contact) {
    $data_contact = mysqli_fetch_assoc($result_contact);
    if ($data_contact) {
        $contact_heading = htmlspecialchars($data_contact['contact_heading'] ?? $contact_heading);
        $map_embed = $data_contact['map_embed'] ?? '';
    }
    mysqli_free_result($result_contact);
} else {
    error_log("Error fetching contact section data: " . mysqli_error($koneksi));
}





mysqli_close($koneksi); // Tutup koneksi setelah semua data diambil

include 'includes/inc_header.php';
?>

<main>
    <div class="content">
        <article id="home" class="hero">
            <section class="hero-content">
                <div class="hero-title">
                    <h1><?php echo $heading1; ?></h1>
                    <h2><?php echo $heading2; ?></h2>
                </div>
                <div class="hero-img">
                    <?php if (!empty($qr_code_path)): ?>
                        <img src="img/<?php echo $qr_code_path; ?>" alt="QR Code untuk Bergabung">
                    <?php endif; ?>
                </div>
            </section>
        </article>

        <article id="about" class="about">
            <h1>About Us</h1>
            <section class="about-container">
                <div class="about-img">
                    <img src="img/logo.png" alt="logo">
                </div>
                <div class="about-content">
                    <h1><?php echo $about_title; ?></h1>
                    <p><?php echo nl2br($about_description); ?></p>
                </div>
            </section>
            <section class="learning">
                <h1>Hal Hal yang Dipelajari</h1>
                <div class="learning-list">
                    <ul>
                        <h6>Soft Skill</h6>
                        <?php if (!empty($soft_skills_list)): ?>
                            <?php foreach ($soft_skills_list as $skill): ?>
                                <li><?php echo $skill; ?></li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li>Belum ada Soft Skill.</li>
                        <?php endif; ?>
                    </ul>
                    <ul>
                        <h6>Hard Skill</h6>
                        <?php if (!empty($hard_skills_list)): ?>
                            <?php foreach ($hard_skills_list as $skill): ?>
                                <li><?php echo $skill; ?></li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li>Belum ada Hard Skill.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </section>
        </article>

        <article id="class" class="class">
            <h1>Class</h1>
            <section class="class-card">
                <?php
                if (!empty($classes)) {
                    foreach ($classes as $row) {
                        ?>
                        <a href="dashboard/class_preview/index.php" class="class-item minimalist">
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
            </section>
        </article>

        <article id="contact" class="contact">
                <h1><?= htmlspecialchars($contact_heading) ?></h1>
                <section class="row">
                    <?= $map_embed ?>
                <form action="" id="contactform">
                    <div class="input-group">
                        <i class="fa-solid fa-user fa-xl"></i>
                        <input type="text" placeholder="nama" id="name" name="name" required>
                    </div>
                    <div class="input-group">
                        <i class="fa-solid fa-envelope fa-xl"></i>
                        <input type="email" placeholder="email" id="email" name="email" required>
                    </div>
                    <div class="input-group">
                        <i class="fa-solid fa-phone fa-xl"></i>
                        <input type="tel" placeholder="nomer hp" id="phone" name="phone" required>
                    </div>
                    <div class="input-group">
                        <i class="fa-solid fa-comment fa-xl"></i>
                        <textarea type="text" placeholder="pesan" id="pesan" name="pesan" required></textarea>
                    </div>
                    <button type="button" class="btn disabled" id="contactButton" disabled><i data-feather="send"
                            class="send"></i> Kirim Pesan</button>
                </form>
            </section>
        </article>
    </div>
</main>

<?php
include 'includes/inc_footer.php';
?>