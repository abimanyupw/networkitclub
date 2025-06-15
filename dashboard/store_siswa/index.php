<?php
session_start();
require_once '../../includes/inc_koneksi.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'siswa') {
    header("Location: ../../login.php");
    exit();
}

$page_title = "Store";
$user_id = $_SESSION['user_id'];
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$badge_filter = isset($_GET['badge_filter']) ? (int)$_GET['badge_filter'] : 0;

// Ambil badge yang dimiliki user
$badge_user = [];
$badge_q = mysqli_query($koneksi, "SELECT badge_id, COUNT(*) as count FROM user_badges WHERE user_id = $user_id GROUP BY badge_id");
if ($badge_q) {
    while ($row = mysqli_fetch_assoc($badge_q)) {
        $badge_user[$row['badge_id']] = (int)$row['count'];
    }
} else {
    error_log("Error fetching user badges: " . mysqli_error($koneksi));
}

// Ambil semua barang dari store dan syarat badgenya
$query = "
SELECT si.*, sibr.badge_id, sibr.badge_count_required, b.name AS badge_name
FROM store_items si
LEFT JOIN store_item_badge_rules sibr ON si.id = sibr.store_item_id
LEFT JOIN badges b ON sibr.badge_id = b.id
ORDER BY si.name
";
$result = mysqli_query($koneksi, $query);

// Kelompokkan data store item + rules
$store_items = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $id = $row['id'];
        if (!isset($store_items[$id])) {
            $store_items[$id] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'description' => $row['description'],
                'image_url' => $row['image_url'],
                'stock' => (int)$row['stock'],
                'rules' => []
            ];
        }
        if (!empty($row['badge_id'])) {
            $store_items[$id]['rules'][] = [
                'badge_id' => (int)$row['badge_id'],
                'badge_name' => $row['badge_name'],
                'badge_count_required' => (int)$row['badge_count_required']
            ];
        }
    }
} else {
    error_log("Error fetching store items: " . mysqli_error($koneksi));
}

// Filter Logika PHP di sini
$filtered_items = $store_items; 

if (!empty($search)) {
    $filtered_items = array_filter($filtered_items, function($item) use ($search) {
        return stripos($item['name'], $search) !== false;
    });
}

if ($badge_filter > 0) {
    $filtered_items = array_filter($filtered_items, function($item) use ($badge_filter) {
        foreach ($item['rules'] as $rule) {
            if ($rule['badge_id'] == $badge_filter) {
                return true;
            }
        }
        return false;
    });
}

// Ambil semua badge untuk dropdown filter "Semua Badge"
$all_badges_for_filter_result = mysqli_query($koneksi, "SELECT id, name FROM badges ORDER BY name ASC");

// --- LOGIKA UNTUK MENAMPILKAN LINK WA.ME/ SETELAH SUKSES REDEEM ---
$show_whatsapp_link = false;
$whatsapp_link_url = '';
$whatsapp_admin_number = '6289509088396'; // Ganti dengan nomor Admin/Penerima Notifikasi
$whatsapp_message_text = '';

if (isset($_GET['success']) && $_GET['success'] == 1 && 
    isset($_GET['item_name_wa']) && isset($_GET['user_name_wa']) && isset($_GET['phone_number_wa'])) {

    $show_whatsapp_link = true;
    $item_name_wa = htmlspecialchars(urldecode($_GET['item_name_wa']));
    $user_name_wa = htmlspecialchars(urldecode($_GET['user_name_wa']));
    $phone_number_wa = htmlspecialchars(urldecode($_GET['phone_number_wa']));

    $message_template = "Halo Admin,\n\n";
    $message_template .= "Saya *{user_name}* ({phone_number}) telah menukarkan barang *{item_name}* di Store.\n";
    $message_template .= "Mohon segera diproses dan dihubungi untuk konfirmasi lebih lanjut.\n\n";
    $message_template .= "Terima kasih.";

    $whatsapp_message_text = str_replace(
        ['{user_name}', '{phone_number}', '{item_name}'],
        [$user_name_wa, $phone_number_wa, $item_name_wa],
        $message_template
    );

    // Format nomor admin untuk wa.me/
    $clean_admin_number = preg_replace('/[^0-9]/', '', $whatsapp_admin_number);
    if (substr($clean_admin_number, 0, 1) === '0') {
        $clean_admin_number = '62' . substr($clean_admin_number, 1);
    } elseif (substr($clean_admin_number, 0, 2) !== '62') {
         $clean_admin_number = '62' . $clean_admin_number;
    }
    $clean_admin_number = ltrim($clean_admin_number, '+');

    $whatsapp_link_url = "https://wa.me/" . $clean_admin_number . "?text=" . urlencode($whatsapp_message_text);
}
require_once '../dashboard_header.php';
?>

<div class="content-wrapper mb-5">
    <section class="content-header">
        <h1><?= $page_title ?></h1>
        <ol class="breadcrumb gap-2">
            <li><a href="../../index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">Store</li>
        </ol>
    </section>

    <div class="container">
        <div class="box-header with-border mb-3 d-flex justify-content-flex-start align-items-center gap-2">
            <form method="GET" class="d-flex mb-3">
                <input type="text" name="search" class="form-control me-2" placeholder="Cari nama barang..." value="<?= htmlspecialchars($search) ?>">
                <select name="badge_filter" class="form-select me-2">
                    <option value="">-- Semua Badge --</option>
                    <?php
                    if ($all_badges_for_filter_result && mysqli_num_rows($all_badges_for_filter_result) > 0) {
                        while ($badge = mysqli_fetch_assoc($all_badges_for_filter_result)) {
                    ?>
                            <option value="<?= htmlspecialchars($badge['id']) ?>" <?= ($badge_filter == $badge['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($badge['name']) ?>
                            </option>
                    <?php
                        }
                    } else {
                    ?>
                        <option value="">Tidak ada badge tersedia</option>
                    <?php
                    }
                    ?>
                </select>
                <button type="submit" class="btn btn-primary"><i class="fa fa-search me-1"></i> </button>
            </form>
        </div>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['success_message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['error_message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <?php if ($show_whatsapp_link): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <p><strong>Penting:</strong> Permintaan Anda berhasil diajukan. Mohon klik tombol di bawah untuk mengonfirmasi ke Admin via WhatsApp:</p>
                <a href="<?= $whatsapp_link_url ?>" target="_blank" class="btn btn-success"><i class="fab fa-whatsapp"></i> Konfirmasi via WhatsApp</a>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row mt-4 ">
            <?php if (empty($filtered_items)) { ?>
                <div class="alert alert-info text-center">Tidak ada barang yang cocok dengan filter.</div>
            <?php } else { ?>
                <?php foreach ($filtered_items as $item) { ?>
                    <div class="col-xl-4 mb-4">
                        <div class="card h-100" style="width: 20rem;">
                            <?php if (!empty($item['image_url'])) { ?>
                                <img src="../../uploads/<?= htmlspecialchars($item['image_url']) ?>" class="card-img-top" style="max-height: 200px; object-fit: cover;">
                            <?php } ?>
                            <div class="card-body bg-info">
                                <h5 class="card-title"><?= htmlspecialchars($item['name']) ?></h5>
                                <p class="card-text"><?= htmlspecialchars($item['description']) ?></p>
                                <p><strong>Stok:</strong> <?= (int)$item['stock'] ?></p>
                                <p><strong>Syarat Badge:</strong></p>
                                <ul class="list-unstyled ms-3">
                                    <?php
                                    $is_eligible = true;
                                    if (empty($item['rules'])) {
                                        echo '<li>Tidak ada syarat badge.</li>';
                                    } else {
                                        foreach ($item['rules'] as $rule) {
                                            $owned = $badge_user[$rule['badge_id']] ?? 0;
                                            $meets = $owned >= $rule['badge_count_required'];
                                            if (!$meets) {
                                                $is_eligible = false;
                                            }
                                    ?>
                                            <li>
                                                <?= htmlspecialchars($rule['badge_name']) ?>:
                                                <?= $owned ?>/<?= $rule['badge_count_required'] ?>
                                                <?= $meets ? '<i class="fa fa-check text-success"></i>' : '<i class="fa fa-times text-danger"></i>' ?>
                                            </li>
                                    <?php
                                        }
                                    }
                                    ?>
                                </ul>
                            </div>
                            <div class="card-footer text-end bg-white">
                                <a href="redeem_form.php?item_id=<?= $item['id'] ?>"
                                   class="btn btn-sm btn-primary"
                                   <?= ($is_eligible && $item['stock'] > 0) ? '' : 'disabled' ?>>
                                    Tukar Barang
                                </a>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            <?php } ?>
        </div>

        <div class="box mt-5">
            <div class="box-header with-border">
                <h3 class="box-title">Riwayat Penukaran Barang Anda</h3>
            </div>
            <div class="box-body">
                <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Barang</th>
                            <th>Jumlah</th>
                            <th>Tanggal Penukaran</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $history_query = "
                            SELECT 
                                rh.id, 
                                si.name AS item_name, 
                                rh.quantity, 
                                rh.created_at AS redeem_date,
                                rh.status
                            FROM 
                                redemptions rh
                            JOIN 
                                store_items si ON rh.item_id = si.id
                            WHERE 
                                rh.user_id = $user_id
                            ORDER BY 
                                rh.created_at DESC
                            LIMIT 10 
                        ";
                        $history_result = mysqli_query($koneksi, $history_query);

                        if (!$history_result) {
                            echo '<tr><td colspan="5" class="text-center text-danger">Error mengambil riwayat: ' . mysqli_error($koneksi) . '</td></tr>';
                        } elseif (mysqli_num_rows($history_result) > 0) {
                            $no_history = 1;
                            while ($history_row = mysqli_fetch_assoc($history_result)) {
                        ?>
                                <tr>
                                    <td><?= $no_history++ ?></td>
                                    <td><?= htmlspecialchars($history_row['item_name']) ?></td>
                                    <td><?= (int)($history_row['quantity'] ?? 1) ?></td>
                                    <td><?= date('d M Y H:i', strtotime($history_row['redeem_date'])) ?></td>
                                    <td><?= htmlspecialchars($history_row['status']) ?></td>
                                </tr>
                        <?php
                            }
                        } else {
                        ?>
                            <tr><td colspan="5" class="text-center">Belum ada riwayat penukaran barang.</td></tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../dashboard_footer.php'; ?>