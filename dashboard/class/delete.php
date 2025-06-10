<?php
session_start();
include '../../includes/inc_koneksi.php';

// Hanya admin dan developer yang bisa menghapus
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'developer'])) {
    $_SESSION['error_message'] = "Anda tidak memiliki akses untuk menghapus kelas.";
    header('Location: ../../login.php');
    exit();
}

$class_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($class_id === 0) {
    $_SESSION['error_message'] = "ID kelas tidak valid untuk dihapus.";
    header('Location: index.php');
    exit();
}

// Ambil informasi kelas (nama dan URL gambar)
$class_name = '';
$image_url = '';
$sql_get_class = "SELECT name, image_url FROM classes WHERE id = ?";
if ($stmt_get = $koneksi->prepare($sql_get_class)) {
    $stmt_get->bind_param("i", $class_id);
    $stmt_get->execute();
    $result_get = $stmt_get->get_result();
    if ($result_get->num_rows > 0) {
        $row = $result_get->fetch_assoc();
        $class_name = $row['name'];
        $image_url = $row['image_url'];
    }
    $stmt_get->close();
}

// Hapus data kelas dari database
$sql_delete = "DELETE FROM classes WHERE id = ?";
if ($stmt = $koneksi->prepare($sql_delete)) {
    $stmt->bind_param("i", $class_id);
    if ($stmt->execute()) {
        // Hapus file gambar jika ada dan file tersebut berada di dalam folder uploads
        if (!empty($image_url)) {
            $image_path = "../../" . $image_url;
            if (file_exists($image_path) && strpos(realpath($image_path), realpath("../../uploads/class_images/")) === 0) {
                @unlink($image_path); // hapus file dengan aman
            }
        }

        $_SESSION['success_message'] = "Kelas " . htmlspecialchars($class_name ?: 'Tidak Diketahui') . " berhasil dihapus!";
    } else {
        if ($koneksi->errno == 1451) {
            $_SESSION['error_message'] = "Gagal menghapus kelas " . htmlspecialchars($class_name ?: 'Tidak Diketahui') . ". Terdapat data terkait yang masih berhubungan dengan kelas ini.";
        } else {
            $_SESSION['error_message'] = "Gagal menghapus kelas: " . $stmt->error;
        }
    }
    $stmt->close();
} else {
    $_SESSION['error_message'] = "Gagal menyiapkan statement: " . $koneksi->error;
}

$koneksi->close();
header('Location: index.php');
exit();
