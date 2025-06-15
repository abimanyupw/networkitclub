<?php
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

// Ambil dari $_ENV (lebih andal di Windows)
$host = $_ENV['DB_HOST'];
$user = $_ENV['DB_USER'];
$pw   = $_ENV['DB_PASSWORD'];
$db   = $_ENV['DB_NAME'];

$koneksi = mysqli_connect($host, $user, $pw, $db);
if (!$koneksi) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}
?>
