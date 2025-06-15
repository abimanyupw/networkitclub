<?php
// dashboard/siswa/store/redeem_form.php
session_start();
require_once '../../includes/inc_koneksi.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'siswa') {
    header("Location: ../../login.php");
    exit();
}

$page_title = "Form Penukaran Barang";
$user_id = $_SESSION['user_id'];
// Mengambil item_id dari GET, bukan POST, karena tombol sudah menjadi link
$item_id = isset($_GET['item_id']) ? (int)$_GET['item_id'] : 0; 

// --- Validasi awal item_id (penting sebelum query) ---
if ($item_id < 1) {
    $_SESSION['error_message'] = "ID barang tidak valid.";
    header("Location: index.php?error=invalid_item_id"); // Redirect dengan error spesifik
    exit();
}

// Inisialisasi variabel untuk form
$full_name = '';
$email = '';
$jurusan = ''; 
$phone_number = ''; // Sekarang akan diambil dari database

// Ambil data user dari database (nama, email, jurusan, phone_number)
// PASTIKAN kolom 'jurusan' DAN 'phone_number' ada di tabel 'users' Anda
$user_q = mysqli_query($koneksi, "SELECT full_name, email, jurusan, phone_number FROM users WHERE id = $user_id");
if ($user_q && mysqli_num_rows($user_q) > 0) {
    $user_data = mysqli_fetch_assoc($user_q);
    $full_name = $user_data['full_name'];
    $email = $user_data['email'];
    $jurusan = $user_data['jurusan'] ?? ''; 
    $phone_number = $user_data['phone_number'] ?? ''; // <--- PERUBAHAN DI SINI: Ambil phone_number dari DB
} else {
    $_SESSION['error_message'] = "Data pengguna tidak ditemukan.";
    header("Location: index.php?error=user_not_found");
    exit();
}

// Ambil detail barang untuk ditampilkan di form
$item_q = mysqli_query($koneksi, "SELECT id, name, description, image_url, stock FROM store_items WHERE id = $item_id");
$item_data = mysqli_fetch_assoc($item_q);

if (!$item_data) {
    $_SESSION['error_message'] = "Barang tidak ditemukan.";
    header("Location: index.php?error=item_not_found");
    exit();
}

// Cek ketersediaan stok (Validasi penting)
if ($item_data['stock'] < 1) {
    $_SESSION['error_message'] = "Maaf, stok barang ini habis.";
    header("Location: index.php?error=out_of_stock");
    exit();
}

// Ambil syarat badge untuk validasi kelayakan
$rules = [];
$rule_result = mysqli_query($koneksi, "SELECT badge_id, badge_count_required FROM store_item_badge_rules WHERE store_item_id = $item_id");
while ($row = mysqli_fetch_assoc($rule_result)) {
    $rules[$row['badge_id']] = $row['badge_count_required'];
}

// Ambil badge yang dimiliki user
$owned_badges = [];
$user_badges_q = mysqli_query($koneksi, "SELECT badge_id, COUNT(*) as jumlah FROM user_badges WHERE user_id = $user_id GROUP BY badge_id");
while ($row = mysqli_fetch_assoc($user_badges_q)) {
    $owned_badges[$row['badge_id']] = $row['jumlah'];
}

// Cek kelayakan (eligibility) berdasarkan badge (Validasi penting)
$is_eligible = true;
foreach ($rules as $badge_id => $required) {
    if (!isset($owned_badges[$badge_id]) || $owned_badges[$badge_id] < $required) {
        $is_eligible = false;
        break;
    }
}

if (!$is_eligible) {
    $_SESSION['error_message'] = "Anda tidak memenuhi syarat badge untuk menukar barang ini.";
    header("Location: index.php?error=not_eligible");
    exit();
}

// Cek duplikasi permintaan yang masih pending (VALIDASI UTAMA DUPLIKASI DI SINI)
$cek_duplikat = mysqli_query($koneksi, "SELECT id FROM redemptions WHERE user_id = $user_id AND item_id = $item_id AND status = 'pending'");
if (mysqli_num_rows($cek_duplikat) > 0) {
    $_SESSION['error_message'] = "Anda sudah pernah meminta barang ini dan masih menunggu proses.";
    header("Location: index.php?error=already_requested"); // REDIRECT DARI SINI
    exit();
}

// Jika semua validasi di atas lolos, form akan ditampilkan
require_once '../dashboard_header.php'; // Memuat header dashboard untuk tampilan konsisten
?>

<div class="content-wrapper mb-5">
    <section class="content-header">
        <h1><?= $page_title ?></h1>
        <ol class="breadcrumb">
            <li><a href="../../index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="index.php">Store</a></li>
            <li class="active">Form Penukaran</li>
        </ol>
    </section>

    <div class="container mt-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                Konfirmasi Penukaran: <?= htmlspecialchars($item_data['name']) ?>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4 text-center">
                        <?php if (!empty($item_data['image_url'])): ?>
                            <img src="../../uploads/<?= htmlspecialchars($item_data['image_url']) ?>" class="img-fluid rounded" alt="Gambar Barang" style="max-height: 150px; object-fit: contain;">
                        <?php endif; ?>
                    </div>
                    <div class="col-md-8">
                        <h5><?= htmlspecialchars($item_data['name']) ?></h5>
                        <p class="text-muted"><?= htmlspecialchars($item_data['description']) ?></p>
                        <p>Stok Tersedia: <strong><?= (int)$item_data['stock'] ?></strong></p>
                        <p>Syarat Badge:</p>
                        <ul class="list-unstyled ms-3">
                            <?php if (empty($rules)): ?>
                                <li>Tidak ada syarat badge.</li>
                            <?php else: ?>
                                <?php foreach ($rules as $badge_id => $required):
                                    $badge_name = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT name FROM badges WHERE id = $badge_id"))['name'] ?? 'Unknown Badge';
                                    $owned = $owned_badges[$badge_id] ?? 0;
                                    $meets = $owned >= $required;
                                ?>
                                    <li>
                                        <?= htmlspecialchars($badge_name) ?>:
                                        <?= $owned ?>/<?= $required ?>
                                        <?= $meets ? '<i class="fa fa-check text-success"></i>' : '<i class="fa fa-times text-danger"></i>' ?>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
                
                <hr>

                <form method="POST" action="process_redeem.php">
                    <input type="hidden" name="item_id" value="<?= $item_id ?>">
                    
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" value="<?= htmlspecialchars($full_name) ?>" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($email) ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="jurusan" class="form-label">Jurusan</label>
                        <input type="text" class="form-control" id="jurusan" name="jurusan" value="<?= htmlspecialchars($jurusan) ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="phone_number" class="form-label">Nomor HP / WhatsApp</label>
                        <input type="text" class="form-control" id="phone_number" name="phone_number" value="<?= htmlspecialchars($phone_number) ?>" placeholder="Cth: 081234567890" required>
                        <div class="form-text">Mohon pastikan nomor HP aktif agar kami dapat menghubungi Anda terkait penukaran ini.</div>
                    </div>

                    <button type="submit" class="btn btn-success"><i class="fa fa-check-circle"></i> Konfirmasi Penukaran</button>
                    <a href="index.php" class="btn btn-secondary">Batal</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../dashboard_footer.php'; ?>