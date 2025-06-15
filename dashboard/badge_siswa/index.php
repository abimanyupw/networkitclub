<?php
// user/badges/index.php
session_start();
require_once '../../includes/inc_koneksi.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'siswa') {
    header("Location: ../../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$page_title = "Badge Saya";

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'semua';
$filter_condition = '';

if ($filter === 'dimiliki') {
    $filter_condition = "WHERE EXISTS (SELECT 1 FROM user_badges ub WHERE ub.badge_id = b.id AND ub.user_id = $user_id)";
} elseif ($filter === 'belum') {
    $filter_condition = "WHERE NOT EXISTS (SELECT 1 FROM user_badges ub WHERE ub.badge_id = b.id AND ub.user_id = $user_id)";
}

$query = "
SELECT b.id, b.name, b.description, b.image_url,
       (SELECT COUNT(*) FROM user_badges WHERE badge_id = b.id AND user_id = $user_id) AS dimiliki
FROM badges b
$filter_condition
ORDER BY b.name
";
$result = mysqli_query($koneksi, $query);
require_once '../dashboard_header.php';
?>

<div class="content-wrapper mb-5">
    <section class="content-header">
        <h1><?= $page_title ?></h1>
        <ol class="breadcrumb gap-2">
            <li><a href="../../index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">Badge</li>
        </ol>
    </section>

    <div class="container mt-4">
        <form method="GET" class="mb-3">
            <div class="form-group row align-items-center">
                <label for="filter" class="col-sm-2 col-form-label">Tampilkan:</label>
                <div class="col-sm-4">
                    <select name="filter" id="filter" class="form-control" onchange="this.form.submit()">
                        <option value="semua" <?= $filter === 'semua' ? 'selected' : '' ?>>Semua Badge</option>
                        <option value="dimiliki" <?= $filter === 'dimiliki' ? 'selected' : '' ?>>Yang Dimiliki</option>
                        <option value="belum" <?= $filter === 'belum' ? 'selected' : '' ?>>Yang Belum Dimiliki</option>
                    </select>
                </div>
            </div>
        </form>

        <div class="row">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($badge = mysqli_fetch_assoc($result)): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card <?= $badge['dimiliki'] ? 'border-success' : 'border-secondary' ?>">
                            <div class="card-body text-center">
                                <?php if (!empty($badge['image_url'])): ?>
                                    <img src="../../uploads/<?= htmlspecialchars($badge['image_url']) ?>" alt="<?= htmlspecialchars($badge['name']) ?>" width="80" class="mb-2">
                                <?php endif; ?>
                                <h5 class="card-title mb-1"><?= htmlspecialchars($badge['name']) ?></h5>
                                <p class="card-text text-muted small"><?= htmlspecialchars($badge['description']) ?></p>
                                <?php if ($badge['dimiliki']): ?>
                                    <span class="badge bg-success">Dimiliki</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Belum Dimiliki</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">Belum ada badge tersedia.</div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../dashboard_footer.php'; ?>
