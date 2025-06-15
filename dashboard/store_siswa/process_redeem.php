<?php
// dashboard/siswa/store/process_redeem.php
session_start();
require_once '../../includes/inc_koneksi.php'; // Pastikan path ini benar

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'siswa') {
    header("Location: ../../login.php");
    exit();
}

// Pastikan request adalah POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error_message'] = "Metode permintaan tidak valid.";
    header("Location: index.php?error=invalid_request");
    exit();
}

// Ambil data dari sesi dan POST
$user_id = $_SESSION['user_id'];
$item_id = isset($_POST['item_id']) ? (int)$_POST['item_id'] : 0;
$phone_number = trim($_POST['phone_number'] ?? ''); // Nomor HP yang diisi user dari form
$full_name = trim($_POST['full_name'] ?? ''); // Nama Lengkap dari form (readonly)
$item_name_from_form = ''; // Akan diambil dari database untuk pesan WhatsApp

// Validasi awal input yang diterima
if ($item_id < 1 || empty($phone_number)) {
    $_SESSION['error_message'] = "Data tidak lengkap atau tidak valid. Nomor HP harus diisi.";
    header("Location: index.php?error=data_invalid");
    exit();
}

// Mulai transaksi database untuk memastikan konsistensi data
mysqli_begin_transaction($koneksi);
$success = true; // Flag untuk melacak keberhasilan seluruh alur transaksi
$error_message_redeem = ''; // Pesan error spesifik jika terjadi kesalahan dalam proses redeem

try {
    // 1. Ambil data item dan kunci barisnya (FOR UPDATE) untuk mencegah race condition
    $item_q = mysqli_query($koneksi, "SELECT id, name, stock FROM store_items WHERE id = $item_id FOR UPDATE");
    $item = mysqli_fetch_assoc($item_q);

    if (!$item || $item['stock'] < 1) {
        $success = false;
        $error_message_redeem = "Maaf, stok barang ini habis atau tidak ditemukan.";
    } else {
        $item_name_from_form = $item['name']; // Simpan nama barang untuk digunakan dalam pesan WhatsApp
    }

    // 2. Validasi ulang syarat badge (penting untuk keamanan)
    if ($success) {
        $rules = [];
        $rule_result = mysqli_query($koneksi, "SELECT badge_id, badge_count_required FROM store_item_badge_rules WHERE store_item_id = $item_id");
        while ($row = mysqli_fetch_assoc($rule_result)) {
            $rules[$row['badge_id']] = $row['badge_count_required'];
        }

        $owned_badges = [];
        $user_badges_q = mysqli_query($koneksi, "SELECT badge_id, COUNT(*) as jumlah FROM user_badges WHERE user_id = $user_id GROUP BY badge_id");
        while ($row = mysqli_fetch_assoc($user_badges_q)) {
            $owned_badges[$row['badge_id']] = $row['jumlah'];
        }

        foreach ($rules as $badge_id => $required) {
            if (!isset($owned_badges[$badge_id]) || $owned_badges[$badge_id] < $required) {
                $success = false;
                $error_message_redeem = "Anda tidak lagi memenuhi syarat badge untuk menukar barang ini.";
                break;
            }
        }
    }

    // 3. Cek duplikasi permintaan yang masih berstatus 'pending'
    if ($success) {
        $cek_duplikat = mysqli_query($koneksi, "SELECT id FROM redemptions WHERE user_id = $user_id AND item_id = $item_id AND status = 'pending' FOR UPDATE");
        if (mysqli_num_rows($cek_duplikat) > 0) {
            $success = false;
            $error_message_redeem = "Anda sudah pernah meminta barang ini dan masih menunggu proses.";
        }
    }

    // 4. (DIHILANGKAN) Update nomor HP pengguna di tabel `users` -- Sesuai permintaan Anda untuk tidak menambah kolom 'phone_number' di tabel `users`

    // 5. Kurangi stok barang di tabel `store_items`
    if ($success) {
        $update_stock = $koneksi->prepare("UPDATE store_items SET stock = stock - 1 WHERE id = ? AND stock > 0");
        if (!$update_stock) {
            error_log("Failed to prepare UPDATE stock statement: " . $koneksi->error);
            $success = false;
            $error_message_redeem = "Terjadi kesalahan sistem saat menyiapkan pengurangan stok.";
        } else {
            $update_stock->bind_param("i", $item_id);
            if (!$update_stock->execute() || $koneksi->affected_rows === 0) {
                $success = false;
                $error_message_redeem = "Gagal mengurangi stok barang atau stok habis (mungkin sudah ditukarkan).";
            }
            $update_stock->close();
        }
    }

    // 6. Simpan data penukaran ke tabel `redemptions`
    if ($success) {
        $insert_redeem = $koneksi->prepare("INSERT INTO redemptions (user_id, item_id, quantity, status) VALUES (?, ?, ?, ?)");
        $quantity = 1; 
        $status_redeem = 'pending'; 

        if (!$insert_redeem) {
            error_log("Failed to prepare INSERT statement for redemptions: " . $koneksi->error);
            $success = false;
            $error_message_redeem = "Terjadi kesalahan sistem saat menyiapkan permintaan penukaran.";
        } else {
            $insert_redeem->bind_param("iiis", $user_id, $item_id, $quantity, $status_redeem); 
            if (!$insert_redeem->execute()) {
                $success = false;
                $error_message_redeem = "Gagal menyimpan permintaan penukaran: " . $insert_redeem->error;
            }
            $insert_redeem->close();
        }
    }

    // --- DIHAPUS: Blok Kirim Notifikasi WhatsApp (cURL API) ---
    // Sekarang akan diganti dengan link wa.me/ di halaman sukses

    // Commit atau Rollback transaksi database
    if ($success) {
        mysqli_commit($koneksi); // Komit semua perubahan ke database jika berhasil
        
        // Redirect ke halaman index dengan pesan sukses DAN data untuk membuat link WA
        // Gunakan urlencode untuk memastikan data aman di URL
        $redirect_params = [
            'success' => 1,
            'item_name_wa' => urlencode($item_name_from_form),
            'user_name_wa' => urlencode($full_name),
            'phone_number_wa' => urlencode($phone_number)
        ];
        $_SESSION['success_message'] = "Permintaan penukaran barang berhasil diajukan! Silakan klik link di bawah untuk konfirmasi WhatsApp.";
        header("Location: index.php?" . http_build_query($redirect_params));
        exit();
    } else {
        mysqli_rollback($koneksi); // Rollback semua perubahan jika ada kesalahan
        $_SESSION['error_message'] = $error_message_redeem ?: "Terjadi kesalahan yang tidak diketahui saat penukaran.";
        header("Location: index.php?error=redeem_fail");
        exit();
    }

} catch (Exception $e) {
    mysqli_rollback($koneksi);
    $_SESSION['error_message'] = "Terjadi kesalahan tak terduga: " . $e->getMessage();
    header("Location: index.php?error=exception");
    exit();
} finally {
    if ($koneksi) {
        $koneksi->close();
    }
}
?>