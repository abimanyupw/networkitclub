-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 11, 2025 at 12:54 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `nic_club`
--

-- --------------------------------------------------------

--
-- Table structure for table `about_section`
--

CREATE TABLE `about_section` (
  `id` int(11) NOT NULL,
  `about_title` varchar(255) NOT NULL DEFAULT 'Apa Itu NIC ?',
  `about_description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `about_section`
--

INSERT INTO `about_section` (`id`, `about_title`, `about_description`) VALUES
(1, 'Apa Itu NIC ?', 'Network It Club atau bisa disebut NIC adalah extrakurikuler yang berfokus pada bidang IT,Kegiatan dalam NIC biasanya mencakup pelatihan, simulasi jaringan menggunakan software atau bahkan membangun infrastruktur jaringan sederhana.Extrakurikuler ini sangat relevan untuk siswa yang memilih jurusan TKJ(Teknik Komputer & Jaringan).');

-- --------------------------------------------------------

--
-- Table structure for table `assignments`
--

CREATE TABLE `assignments` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `kelas_id` int(11) DEFAULT NULL,
  `deadline` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assignments`
--

INSERT INTO `assignments` (`id`, `title`, `description`, `kelas_id`, `deadline`, `created_by`, `created_at`) VALUES
(1, 'topologi jaringan ', 'cisco packet traces', 3, '2025-06-11 07:58:00', 12, '2025-06-10 00:58:55');

-- --------------------------------------------------------

--
-- Table structure for table `assignment_submissions`
--

CREATE TABLE `assignment_submissions` (
  `id` int(11) NOT NULL,
  `assignment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assignment_submissions`
--

INSERT INTO `assignment_submissions` (`id`, `assignment_id`, `user_id`, `file_path`, `submitted_at`, `status`, `reviewed_by`, `reviewed_at`) VALUES
(1, 1, 15, '1749517421_Kian Graham.pdf', '2025-06-10 01:03:41', 'approved', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `badges`
--

CREATE TABLE `badges` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `badges`
--

INSERT INTO `badges` (`id`, `name`, `description`, `image_url`, `created_at`) VALUES
(1, 'Cyberops', 'coba1', NULL, '2025-06-09 10:32:18'),
(2, 'itnsa', 'itnsa', 'badge_6846ba6a2da6b_logo.png', '2025-06-09 10:41:46');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`) VALUES
(1, 'Mikrotik', 'Mikrotik adalah sistem operasi jaringan (RouterOS) yang dikembangkan oleh perusahaan MikroTik. Sistem operasi ini memungkinkan komputer untuk berfungsi sebagai router, firewall, hotspot, dan berbagai fungsi jaringan lainnya. Mikrotik juga menghasilkan perangkat keras router, switch, dan sistem nirkabel. '),
(2, 'Linux Server', 'Linux server adalah server yang menggunakan sistem operasi Linux, yang bersifat open source dan gratis. Server ini sering digunakan untuk berbagai keperluan seperti web hosting, database, email, dan layanan jaringan lainnya, karena keandalan, stabilitas, dan kemampuannya untuk dikustomisasi. '),
(5, 'Binary Exploitation', 'Binary Exploitation'),
(7, 'Cisco Packet Tracer', 'Cisco Packet Tracer adalah perangkat lunak simulasi jaringan yang dikembangkan oleh Cisco Systems, yang digunakan untuk mensimulasikan berbagai perangkat jaringan seperti router, switch, server, dan komputer.');

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`id`, `name`, `description`, `image_url`, `created_by`, `created_at`) VALUES
(1, 'Cyber Security', 'Pelajari dasar-dasar keamanan siber, pertahanan jaringan, dan etika hacker untuk melindungi data.', 'img/cyber-logo.png', NULL, '2025-05-25 03:37:32'),
(2, 'Information Network Cabling', 'Bangun fondasi jaringan yang kokoh dengan instalasi dan manajemen kabel yang presisi.', 'img/cabling-logo.png', NULL, '2025-05-25 03:38:57'),
(3, 'ITNSA (IT Network System Administration)', 'Menguasai administrasi sistem dan jaringan, konfigurasi server, serta manajemen infrastruktur IT.', 'img/itnsa-logo.png', NULL, '2025-05-25 03:38:57');

-- --------------------------------------------------------

--
-- Table structure for table `contact_content`
--

CREATE TABLE `contact_content` (
  `id` int(11) NOT NULL,
  `map_embed` text DEFAULT NULL,
  `contact_heading` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_content`
--

INSERT INTO `contact_content` (`id`, `map_embed`, `contact_heading`) VALUES
(1, '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3955.370214627742!2d112.55732877500236!3d-7.534539492478707!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e7874e7db70311d%3A0xa3bd0bb3dcefe901!2sSMK%20Negeri%201%20Pungging!5e0!3m2!1sid!2sid!4v1748568077824!5m2!1sid!2sid allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade\" class=\"map\"></iframe>', 'Contact'),
(2, '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3955.370214627742!2d112.55732877500236!3d-7.534539492478707!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e7874e7db70311d%3A0xa3bd0bb3dcefe901!2sSMK%20Negeri%201%20Pungging!5e0!3m2!1sid!2sid!4v1748568077824!5m2!1sid!2sid allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade\"></iframe>', 'Contact');

-- --------------------------------------------------------

--
-- Table structure for table `home_content`
--

CREATE TABLE `home_content` (
  `id` int(11) NOT NULL,
  `heading1` varchar(255) NOT NULL,
  `heading2` varchar(255) NOT NULL,
  `qr_code_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `home_content`
--

INSERT INTO `home_content` (`id`, `heading1`, `heading2`, `qr_code_path`) VALUES
(1, 'Mari Tingkatkan Skill IT Bersama Kami', 'Scan Untuk Bergabung Dalam Jaringan', 'qr_6837e4c58914a.png');

-- --------------------------------------------------------

--
-- Table structure for table `informasi`
--

CREATE TABLE `informasi` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `informasi`
--

INSERT INTO `informasi` (`id`, `title`, `content`, `created_at`) VALUES
(3, 'Besok libur', 'Karena malas', '2025-06-10 09:40:23');

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `attempt_time` datetime NOT NULL,
  `username_attempted` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `materials`
--

CREATE TABLE `materials` (
  `id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `content` text DEFAULT NULL,
  `file_url` varchar(255) DEFAULT NULL,
  `material_type` enum('text','video','pdf','image','other') DEFAULT 'text',
  `uploaded_by` int(11) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `materials`
--

INSERT INTO `materials` (`id`, `class_id`, `category_id`, `title`, `content`, `file_url`, `material_type`, `uploaded_by`, `uploaded_at`, `updated_at`) VALUES
(3, 3, 1, 'Materi pdf', NULL, 'files/Surat Lamaran.pdf', 'pdf', NULL, '2025-05-26 22:49:29', '2025-05-29 06:26:28'),
(4, 1, 5, 'Python', 'Python', NULL, 'text', 9, '2025-05-27 11:49:31', '2025-05-29 06:26:28');
INSERT INTO `materials` (`id`, `class_id`, `category_id`, `title`, `content`, `file_url`, `material_type`, `uploaded_by`, `uploaded_at`, `updated_at`) VALUES
(9, 3, 2, 'Konfigurasi DHCP SERVER', '<p><span style=\\\"background-color: rgb(255, 255, 255);\\\" rgb(255,=\\\"\\\" 255,=\\\"\\\" 255);\\\\\\\"=\\\"\\\" 255);\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\">konfigurasi dhcp server&nbsp;</span></p><div class=\\\"\\\\&quot;\\\\\\\\&quot;WaaZC\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;font-family:\\\\&quot;\\\" &quot;google=\\\"\\\\&quot;\\\\&quot;\\\" sans&quot;,=\\\"\\\\&quot;\\\\&quot;\\\" arial,=\\\"\\\\&quot;\\\\&quot;\\\" sans-serif;=\\\"\\\\&quot;\\\\&quot;\\\" font-size:=\\\"\\\\&quot;\\\\&quot;\\\" 18px;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><div class=\\\"\\\\&quot;\\\\\\\\&quot;rPeykc\\\\\\\\&quot;\\\\&quot;\\\" data-hveid=\\\"\\\\&quot;\\\\\\\\&quot;CCYQAQ\\\\\\\\&quot;\\\\&quot;\\\" data-ved=\\\"\\\\&quot;\\\\\\\\&quot;2ahUKEwiZhfb_rceNAxUP4DgGHUlfDBwQo_EKegQIJhAB\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;margin:\\\\&quot;\\\" 0px=\\\"\\\\&quot;\\\\&quot;\\\" 20px;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><span style=\\\"background-color: rgb(255, 255, 255);\\\" rgb(255,=\\\"\\\" 255,=\\\"\\\" 255);\\\\\\\"=\\\"\\\" 255);\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><span data-huuid=\\\"\\\\&quot;\\\\\\\\&quot;2901253832168685605\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;\\\\\\\\&quot;\\\\&quot;\\\">Konfigurasi DHCP server&nbsp;<mark class=\\\"\\\\&quot;\\\\\\\\&quot;QVRyCf\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;background-image:\\\\&quot;\\\" none;=\\\"\\\\&quot;\\\\&quot;\\\" background-position:=\\\"\\\\&quot;\\\\&quot;\\\" 0%=\\\"\\\\&quot;\\\\&quot;\\\" 0%;=\\\"\\\\&quot;\\\\&quot;\\\" background-size:=\\\"\\\\&quot;\\\\&quot;\\\" auto;=\\\"\\\\&quot;\\\\&quot;\\\" background-repeat:=\\\"\\\\&quot;\\\\&quot;\\\" repeat;=\\\"\\\\&quot;\\\\&quot;\\\" background-attachment:=\\\"\\\\&quot;\\\\&quot;\\\" scroll;=\\\"\\\\&quot;\\\\&quot;\\\" background-origin:=\\\"\\\\&quot;\\\\&quot;\\\" padding-box;=\\\"\\\\&quot;\\\\&quot;\\\" background-clip:=\\\"\\\\&quot;\\\\&quot;\\\" border-box;=\\\"\\\\&quot;\\\\&quot;\\\" border-radius:=\\\"\\\\&quot;\\\\&quot;\\\" 4px;=\\\"\\\\&quot;\\\\&quot;\\\" padding:=\\\"\\\\&quot;\\\\&quot;\\\" 0px=\\\"\\\\&quot;\\\\&quot;\\\" 2px;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\">melibatkan pengaturan dan penyesuaian server agar dapat mendistribusikan alamat IP dan informasi jaringan lainnya secara otomatis kepada klien</mark>.&nbsp;</span><span data-huuid=\\\"\\\\&quot;\\\\\\\\&quot;2901253832168688306\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;\\\\\\\\&quot;\\\\&quot;\\\">Ini mencakup penentuan rentang IP, konfigurasi DNS, gateway, dan pengaturan lain yang sesuai dengan kebutuhan jaringan.<span jscontroller=\\\"\\\\&quot;\\\\\\\\&quot;JHnpme\\\\\\\\&quot;\\\\&quot;\\\" class=\\\"\\\\&quot;\\\\\\\\&quot;pjBG2e\\\\\\\\&quot;\\\\&quot;\\\" data-cid=\\\"\\\\&quot;\\\\\\\\&quot;b8dc597b-7131-4d62-824e-9454c13da6f3\\\\\\\\&quot;\\\\&quot;\\\" jsaction=\\\"\\\\&quot;\\\\\\\\&quot;rcuQ6b:npT2md\\\\\\\\&quot;\\\\&quot;\\\"><span class=\\\"\\\\&quot;\\\\\\\\&quot;UV3uM\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;text-wrap-mode:\\\\&quot;\\\" nowrap;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\">&nbsp;<div class=\\\"\\\\&quot;\\\\\\\\&quot;NPrrbc\\\\\\\\&quot;\\\\&quot;\\\" data-cid=\\\"\\\\&quot;\\\\\\\\&quot;b8dc597b-7131-4d62-824e-9454c13da6f3\\\\\\\\&quot;\\\\&quot;\\\" data-uuids=\\\"\\\\&quot;\\\\\\\\&quot;2901253832168685605,2901253832168688306\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;margin-right:\\\\&quot;\\\" 6px;=\\\"\\\\&quot;\\\\&quot;\\\" display:=\\\"\\\\&quot;\\\\&quot;\\\" inline-flex;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><div jsname=\\\"\\\\&quot;\\\\\\\\&quot;HtgYJd\\\\\\\\&quot;\\\\&quot;\\\" class=\\\"\\\\&quot;\\\\\\\\&quot;BMebGe\\\\&quot;\\\" btku5b=\\\"\\\\&quot;\\\\&quot;\\\" fcrzyc=\\\"\\\\&quot;\\\\&quot;\\\" lwdv0e=\\\"\\\\&quot;\\\\&quot;\\\" fr7zsc=\\\"\\\\&quot;\\\\&quot;\\\" ojeuxf\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\" aria-label=\\\"\\\\&quot;\\\\\\\\&quot;Lihat\\\\&quot;\\\" link=\\\"\\\\&quot;\\\\&quot;\\\" terkait\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\" role=\\\"\\\\&quot;\\\\\\\\&quot;button\\\\\\\\&quot;\\\\&quot;\\\" tabindex=\\\"\\\\&quot;\\\\\\\\&quot;0\\\\\\\\&quot;\\\\&quot;\\\" jsaction=\\\"\\\\&quot;\\\\\\\\&quot;KjsqPd\\\\\\\\&quot;\\\\&quot;\\\" data-hveid=\\\"\\\\&quot;\\\\\\\\&quot;CCUQAQ\\\\\\\\&quot;\\\\&quot;\\\" data-ved=\\\"\\\\&quot;\\\\\\\\&quot;2ahUKEwiZhfb_rceNAxUP4DgGHUlfDBwQ3fYKegQIJRAB\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;vertical-align:\\\\&quot;\\\" middle;=\\\"\\\\&quot;\\\\&quot;\\\" outline:=\\\"\\\\&quot;\\\\&quot;\\\" 0px;=\\\"\\\\&quot;\\\\&quot;\\\" -webkit-tap-highlight-color:=\\\"\\\\&quot;\\\\&quot;\\\" transparent;=\\\"\\\\&quot;\\\\&quot;\\\" display:=\\\"\\\\&quot;\\\\&quot;\\\" inline-flex=\\\"\\\\&quot;\\\\&quot;\\\" !important;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><div class=\\\"\\\\&quot;\\\\\\\\&quot;niO4u\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;display:\\\\&quot;\\\" flex;=\\\"\\\\&quot;\\\\&quot;\\\" justify-content:=\\\"\\\\&quot;\\\\&quot;\\\" center;=\\\"\\\\&quot;\\\\&quot;\\\" position:=\\\"\\\\&quot;\\\\&quot;\\\" relative;=\\\"\\\\&quot;\\\\&quot;\\\" align-items:=\\\"\\\\&quot;\\\\&quot;\\\" stretch;=\\\"\\\\&quot;\\\\&quot;\\\" width:=\\\"\\\\&quot;\\\\&quot;\\\" 28px;=\\\"\\\\&quot;\\\\&quot;\\\" border-radius:=\\\"\\\\&quot;\\\\&quot;\\\" 9999px;=\\\"\\\\&quot;\\\\&quot;\\\" margin:=\\\"\\\\&quot;\\\\&quot;\\\" 0px=\\\"\\\\&quot;\\\\&quot;\\\" auto;=\\\"\\\\&quot;\\\\&quot;\\\" outline:=\\\"\\\\&quot;\\\\&quot;\\\" transparent=\\\"\\\\&quot;\\\\&quot;\\\" solid=\\\"\\\\&quot;\\\\&quot;\\\" 1px;=\\\"\\\\&quot;\\\\&quot;\\\" outline-offset:=\\\"\\\\&quot;\\\\&quot;\\\" -1px;=\\\"\\\\&quot;\\\\&quot;\\\" height:=\\\"\\\\&quot;\\\\&quot;\\\" 20px;=\\\"\\\\&quot;\\\\&quot;\\\" min-height:=\\\"\\\\&quot;\\\\&quot;\\\" 20px;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><div class=\\\"\\\\&quot;\\\\\\\\&quot;kHtcsd\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;display:\\\\&quot;\\\" flex;=\\\"\\\\&quot;\\\\&quot;\\\" align-items:=\\\"\\\\&quot;\\\\&quot;\\\" center;=\\\"\\\\&quot;\\\\&quot;\\\" justify-content:=\\\"\\\\&quot;\\\\&quot;\\\" width:=\\\"\\\\&quot;\\\\&quot;\\\" 28px;=\\\"\\\\&quot;\\\\&quot;\\\" border-radius:=\\\"\\\\&quot;\\\\&quot;\\\" 9999px;=\\\"\\\\&quot;\\\\&quot;\\\" height:=\\\"\\\\&quot;\\\\&quot;\\\" 20px;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><span class=\\\"\\\\&quot;\\\\\\\\&quot;d3o3Ad\\\\&quot;\\\" gjdc8e=\\\"\\\\&quot;\\\\&quot;\\\" hkv2pe\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;display:\\\\&quot;\\\" flex;=\\\"\\\\&quot;\\\\&quot;\\\" align-items:=\\\"\\\\&quot;\\\\&quot;\\\" center;=\\\"\\\\&quot;\\\\&quot;\\\" margin:=\\\"\\\\&quot;\\\\&quot;\\\" 0px;=\\\"\\\\&quot;\\\\&quot;\\\" background-image:=\\\"\\\\&quot;\\\\&quot;\\\" unset=\\\"\\\\&quot;\\\\&quot;\\\" !important;=\\\"\\\\&quot;\\\\&quot;\\\" background-position:=\\\"\\\\&quot;\\\\&quot;\\\" background-size:=\\\"\\\\&quot;\\\\&quot;\\\" background-repeat:=\\\"\\\\&quot;\\\\&quot;\\\" background-attachment:=\\\"\\\\&quot;\\\\&quot;\\\" background-origin:=\\\"\\\\&quot;\\\\&quot;\\\" background-clip:=\\\"\\\\&quot;\\\\&quot;\\\" !important;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><span class=\\\"\\\\&quot;\\\\\\\\&quot;iPjmzb\\\\&quot;\\\" sorfoc=\\\"\\\\&quot;\\\\&quot;\\\" gngsdf\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;display:\\\\&quot;\\\" flex;=\\\"\\\\&quot;\\\\&quot;\\\" height:=\\\"\\\\&quot;\\\\&quot;\\\" unset;=\\\"\\\\&quot;\\\\&quot;\\\" rotate:=\\\"\\\\&quot;\\\\&quot;\\\" 135deg;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><span class=\\\"\\\\&quot;\\\\\\\\&quot;z1asCe\\\\&quot;\\\" sb7k4e\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;display:\\\\&quot;\\\" inline-block;=\\\"\\\\&quot;\\\\&quot;\\\" fill:=\\\"\\\\&quot;\\\\&quot;\\\" currentcolor;=\\\"\\\\&quot;\\\\&quot;\\\" height:=\\\"\\\\&quot;\\\\&quot;\\\" 18px;=\\\"\\\\&quot;\\\\&quot;\\\" line-height:=\\\"\\\\&quot;\\\\&quot;\\\" position:=\\\"\\\\&quot;\\\\&quot;\\\" relative;=\\\"\\\\&quot;\\\\&quot;\\\" width:=\\\"\\\\&quot;\\\\&quot;\\\" 18px;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><svg focusable=\\\"\\\\&quot;\\\\\\\\&quot;false\\\\\\\\&quot;\\\\&quot;\\\" xmlns=\\\"\\\\&quot;\\\\\\\\&quot;http://www.w3.org/2000/svg\\\\\\\\&quot;\\\\&quot;\\\" viewBox=\\\"\\\\&quot;\\\\\\\\&quot;0\\\\&quot;\\\" 0=\\\"\\\\&quot;\\\\&quot;\\\" 24=\\\"\\\\&quot;\\\\&quot;\\\" 24\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><path d=\\\"\\\\&quot;\\\\\\\\&quot;M3.9\\\\&quot;\\\" 12c0-1.71=\\\"\\\\&quot;\\\\&quot;\\\" 1.39-3.1=\\\"\\\\&quot;\\\\&quot;\\\" 3.1-3.1h4v7h7c-2.76=\\\"\\\\&quot;\\\\&quot;\\\" 0-5=\\\"\\\\&quot;\\\\&quot;\\\" 2.24-5=\\\"\\\\&quot;\\\\&quot;\\\" 5s2.24=\\\"\\\\&quot;\\\\&quot;\\\" 5=\\\"\\\\&quot;\\\\&quot;\\\" 5h4v-1.9h7c-1.71=\\\"\\\\&quot;\\\\&quot;\\\" 0-3.1-1.39-3.1-3.1zm8=\\\"\\\\&quot;\\\\&quot;\\\" 13h8v-2h8v2zm9-6h-4v1.9h4c1.71=\\\"\\\\&quot;\\\\&quot;\\\" 0=\\\"\\\\&quot;\\\\&quot;\\\" 3.1=\\\"\\\\&quot;\\\\&quot;\\\" 1.39=\\\"\\\\&quot;\\\\&quot;\\\" 3.1s-1.39=\\\"\\\\&quot;\\\\&quot;\\\" 3.1-3.1=\\\"\\\\&quot;\\\\&quot;\\\" 3.1h-4v17h4c2.76=\\\"\\\\&quot;\\\\&quot;\\\" 5-2.24=\\\"\\\\&quot;\\\\&quot;\\\" 5-5s-2.24-5-5-5z\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"></path></svg></span></span></span></div></div></div></div></span></span></span></span></div></div><div class=\\\"\\\\&quot;\\\\\\\\&quot;WaaZC\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;font-family:\\\\&quot;\\\" &quot;google=\\\"\\\\&quot;\\\\&quot;\\\" sans&quot;,=\\\"\\\\&quot;\\\\&quot;\\\" arial,=\\\"\\\\&quot;\\\\&quot;\\\" sans-serif;=\\\"\\\\&quot;\\\\&quot;\\\" font-size:=\\\"\\\\&quot;\\\\&quot;\\\" 18px;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><div class=\\\"\\\\&quot;\\\\\\\\&quot;rPeykc\\\\&quot;\\\" pypitc\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\" data-hveid=\\\"\\\\&quot;\\\\\\\\&quot;CCkQAQ\\\\\\\\&quot;\\\\&quot;\\\" data-ved=\\\"\\\\&quot;\\\\\\\\&quot;2ahUKEwiZhfb_rceNAxUP4DgGHUlfDBwQo_EKegQIKRAB\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;margin:\\\\&quot;\\\" 20px=\\\"\\\\&quot;\\\\&quot;\\\" 0px=\\\"\\\\&quot;\\\\&quot;\\\" 10px;=\\\"\\\\&quot;\\\\&quot;\\\" letter-spacing:=\\\"\\\\&quot;\\\\&quot;\\\" 0px;=\\\"\\\\&quot;\\\\&quot;\\\" line-height:=\\\"\\\\&quot;\\\\&quot;\\\" 26px;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><span data-huuid=\\\"\\\\&quot;\\\\\\\\&quot;2901253832168689612\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"background-color: rgb(255, 255, 255);\\\" rgb(255,=\\\"\\\" 255,=\\\"\\\" 255);\\\\\\\"=\\\"\\\" 255);\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\">Langkah-langkah Konfigurasi DHCP Server (Umum):</span></div></div><div class=\\\"\\\\&quot;\\\\\\\\&quot;WaaZC\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;font-family:\\\\&quot;\\\" &quot;google=\\\"\\\\&quot;\\\\&quot;\\\" sans&quot;,=\\\"\\\\&quot;\\\\&quot;\\\" arial,=\\\"\\\\&quot;\\\\&quot;\\\" sans-serif;=\\\"\\\\&quot;\\\\&quot;\\\" font-size:=\\\"\\\\&quot;\\\\&quot;\\\" 18px;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><ol jscontroller=\\\"\\\\&quot;\\\\\\\\&quot;M2ABbc\\\\\\\\&quot;\\\\&quot;\\\" jsaction=\\\"\\\\&quot;\\\\\\\\&quot;jZtoLb:SaHfyb\\\\\\\\&quot;\\\\&quot;\\\" data-hveid=\\\"\\\\&quot;\\\\\\\\&quot;CDQQAQ\\\\\\\\&quot;\\\\&quot;\\\" data-ved=\\\"\\\\&quot;\\\\\\\\&quot;2ahUKEwiZhfb_rceNAxUP4DgGHUlfDBwQnPYKegQINBAB\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;margin:\\\\&quot;\\\" 10px=\\\"\\\\&quot;\\\\&quot;\\\" 0px=\\\"\\\\&quot;\\\\&quot;\\\" 20px;=\\\"\\\\&quot;\\\\&quot;\\\" padding:=\\\"\\\\&quot;\\\\&quot;\\\" 24px;=\\\"\\\\&quot;\\\\&quot;\\\" font-size:=\\\"\\\\&quot;\\\\&quot;\\\" 16px;=\\\"\\\\&quot;\\\\&quot;\\\" line-height:=\\\"\\\\&quot;\\\\&quot;\\\" 22px;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><li class=\\\"\\\\&quot;\\\\\\\\&quot;K3KsMc\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;margin:\\\\&quot;\\\" 0px=\\\"\\\\&quot;\\\\&quot;\\\" 8px;=\\\"\\\\&quot;\\\\&quot;\\\" padding:=\\\"\\\\&quot;\\\\&quot;\\\" 4px;=\\\"\\\\&quot;\\\\&quot;\\\" list-style-position:=\\\"\\\\&quot;\\\\&quot;\\\" inherit;=\\\"\\\\&quot;\\\\&quot;\\\" list-style-image:=\\\"\\\\&quot;\\\\&quot;\\\" list-style-type:=\\\"\\\\&quot;\\\\&quot;\\\" none;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><div class=\\\"\\\\&quot;\\\\\\\\&quot;zMgcWd\\\\&quot;\\\" dskvsb\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\" data-il=\\\"\\\\&quot;\\\\\\\\&quot;\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;padding-bottom:\\\\&quot;\\\" 0px;=\\\"\\\\&quot;\\\\&quot;\\\" padding-top:=\\\"\\\\&quot;\\\\&quot;\\\" border-bottom:=\\\"\\\\&quot;\\\\&quot;\\\" none;=\\\"\\\\&quot;\\\\&quot;\\\" margin-left:=\\\"\\\\&quot;\\\\&quot;\\\" -28px;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><div data-crb-p=\\\"\\\\&quot;\\\\\\\\&quot;\\\\\\\\&quot;\\\\&quot;\\\"><div class=\\\"\\\\&quot;\\\\\\\\&quot;xFTqob\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;flex:\\\\&quot;\\\" 1=\\\"\\\\&quot;\\\\&quot;\\\" 0%;=\\\"\\\\&quot;\\\\&quot;\\\" min-width:=\\\"\\\\&quot;\\\\&quot;\\\" 0px;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><div class=\\\"\\\\&quot;\\\\\\\\&quot;Gur8Ad\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;line-height:\\\\&quot;\\\" 22px;=\\\"\\\\&quot;\\\\&quot;\\\" overflow:=\\\"\\\\&quot;\\\\&quot;\\\" hidden;=\\\"\\\\&quot;\\\\&quot;\\\" padding-bottom:=\\\"\\\\&quot;\\\\&quot;\\\" 4px;=\\\"\\\\&quot;\\\\&quot;\\\" transition:=\\\"\\\\&quot;\\\\&quot;\\\" transform=\\\"\\\\&quot;\\\\&quot;\\\" 200ms=\\\"\\\\&quot;\\\\&quot;\\\" cubic-bezier(0.2,=\\\"\\\\&quot;\\\\&quot;\\\" 0,=\\\"\\\\&quot;\\\\&quot;\\\" 1);\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><span style=\\\"background-color: rgb(255, 255, 255);\\\" rgb(255,=\\\"\\\" 255,=\\\"\\\" 255);\\\\\\\"=\\\"\\\" 255);\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><strong>1.&nbsp;</strong><span data-huuid=\\\"\\\\&quot;\\\\\\\\&quot;2901253832168689523\\\\\\\\&quot;\\\\&quot;\\\"><strong>Install DHCP Server:</strong></span></span></div><div class=\\\"\\\\&quot;\\\\\\\\&quot;vM0jzc\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;letter-spacing:\\\\&quot;\\\" 0.1px;=\\\"\\\\&quot;\\\\&quot;\\\" line-height:=\\\"\\\\&quot;\\\\&quot;\\\" 22px;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><ul jscontroller=\\\"\\\\&quot;\\\\\\\\&quot;M2ABbc\\\\\\\\&quot;\\\\&quot;\\\" jsaction=\\\"\\\\&quot;\\\\\\\\&quot;jZtoLb:SaHfyb\\\\\\\\&quot;\\\\&quot;\\\" data-hveid=\\\"\\\\&quot;\\\\\\\\&quot;CDIQAQ\\\\\\\\&quot;\\\\&quot;\\\" data-ved=\\\"\\\\&quot;\\\\\\\\&quot;2ahUKEwiZhfb_rceNAxUP4DgGHUlfDBwQm_YKegQIMhAB\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;margin:\\\\&quot;\\\" 10px=\\\"\\\\&quot;\\\\&quot;\\\" 0px=\\\"\\\\&quot;\\\\&quot;\\\" 20px;=\\\"\\\\&quot;\\\\&quot;\\\" padding:=\\\"\\\\&quot;\\\\&quot;\\\" 24px;=\\\"\\\\&quot;\\\\&quot;\\\" line-height:=\\\"\\\\&quot;\\\\&quot;\\\" 22px;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><li style=\\\"\\\\&quot;\\\\\\\\&quot;margin:\\\\&quot;\\\" 0px=\\\"\\\\&quot;\\\\&quot;\\\" 8px;=\\\"\\\\&quot;\\\\&quot;\\\" padding:=\\\"\\\\&quot;\\\\&quot;\\\" 4px;=\\\"\\\\&quot;\\\\&quot;\\\" list-style-position:=\\\"\\\\&quot;\\\\&quot;\\\" inherit;=\\\"\\\\&quot;\\\\&quot;\\\" list-style-image:=\\\"\\\\&quot;\\\\&quot;\\\" list-style-type:=\\\"\\\\&quot;\\\\&quot;\\\" disc;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><span data-huuid=\\\"\\\\&quot;\\\\\\\\&quot;2901253832168689434\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"background-color: rgb(255, 255, 255);\\\" rgb(255,=\\\"\\\" 255,=\\\"\\\" 255);\\\\\\\"=\\\"\\\" 255);\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\">Di sistem operasi seperti Windows Server, Anda dapat menginstal peran DHCP Server melalui Server Manager.<span jscontroller=\\\"\\\\&quot;\\\\\\\\&quot;JHnpme\\\\\\\\&quot;\\\\&quot;\\\" class=\\\"\\\\&quot;\\\\\\\\&quot;pjBG2e\\\\\\\\&quot;\\\\&quot;\\\" data-cid=\\\"\\\\&quot;\\\\\\\\&quot;5daa253c-ed1e-4f3c-aadb-50ffde887b86\\\\\\\\&quot;\\\\&quot;\\\" jsaction=\\\"\\\\&quot;\\\\\\\\&quot;rcuQ6b:npT2md\\\\\\\\&quot;\\\\&quot;\\\"><span class=\\\"\\\\&quot;\\\\\\\\&quot;UV3uM\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;text-wrap-mode:\\\\&quot;\\\" nowrap;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\">&nbsp;<div class=\\\"\\\\&quot;\\\\\\\\&quot;NPrrbc\\\\\\\\&quot;\\\\&quot;\\\" data-cid=\\\"\\\\&quot;\\\\\\\\&quot;5daa253c-ed1e-4f3c-aadb-50ffde887b86\\\\\\\\&quot;\\\\&quot;\\\" data-uuids=\\\"\\\\&quot;\\\\\\\\&quot;2901253832168689434\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;margin-right:\\\\&quot;\\\" 6px;=\\\"\\\\&quot;\\\\&quot;\\\" display:=\\\"\\\\&quot;\\\\&quot;\\\" inline-flex;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><div jsname=\\\"\\\\&quot;\\\\\\\\&quot;HtgYJd\\\\\\\\&quot;\\\\&quot;\\\" class=\\\"\\\\&quot;\\\\\\\\&quot;BMebGe\\\\&quot;\\\" btku5b=\\\"\\\\&quot;\\\\&quot;\\\" fcrzyc=\\\"\\\\&quot;\\\\&quot;\\\" lwdv0e=\\\"\\\\&quot;\\\\&quot;\\\" fr7zsc=\\\"\\\\&quot;\\\\&quot;\\\" ojeuxf\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\" aria-label=\\\"\\\\&quot;\\\\\\\\&quot;Lihat\\\\&quot;\\\" link=\\\"\\\\&quot;\\\\&quot;\\\" terkait\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\" role=\\\"\\\\&quot;\\\\\\\\&quot;button\\\\\\\\&quot;\\\\&quot;\\\" tabindex=\\\"\\\\&quot;\\\\\\\\&quot;0\\\\\\\\&quot;\\\\&quot;\\\" jsaction=\\\"\\\\&quot;\\\\\\\\&quot;KjsqPd\\\\\\\\&quot;\\\\&quot;\\\" data-hveid=\\\"\\\\&quot;\\\\\\\\&quot;CC8QAQ\\\\\\\\&quot;\\\\&quot;\\\" data-ved=\\\"\\\\&quot;\\\\\\\\&quot;2ahUKEwiZhfb_rceNAxUP4DgGHUlfDBwQ3fYKegQILxAB\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;vertical-align:\\\\&quot;\\\" middle;=\\\"\\\\&quot;\\\\&quot;\\\" outline:=\\\"\\\\&quot;\\\\&quot;\\\" 0px;=\\\"\\\\&quot;\\\\&quot;\\\" -webkit-tap-highlight-color:=\\\"\\\\&quot;\\\\&quot;\\\" transparent;=\\\"\\\\&quot;\\\\&quot;\\\" display:=\\\"\\\\&quot;\\\\&quot;\\\" inline-flex=\\\"\\\\&quot;\\\\&quot;\\\" !important;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><div class=\\\"\\\\&quot;\\\\\\\\&quot;niO4u\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;display:\\\\&quot;\\\" flex;=\\\"\\\\&quot;\\\\&quot;\\\" justify-content:=\\\"\\\\&quot;\\\\&quot;\\\" center;=\\\"\\\\&quot;\\\\&quot;\\\" position:=\\\"\\\\&quot;\\\\&quot;\\\" relative;=\\\"\\\\&quot;\\\\&quot;\\\" align-items:=\\\"\\\\&quot;\\\\&quot;\\\" stretch;=\\\"\\\\&quot;\\\\&quot;\\\" width:=\\\"\\\\&quot;\\\\&quot;\\\" 28px;=\\\"\\\\&quot;\\\\&quot;\\\" border-radius:=\\\"\\\\&quot;\\\\&quot;\\\" 9999px;=\\\"\\\\&quot;\\\\&quot;\\\" margin:=\\\"\\\\&quot;\\\\&quot;\\\" 0px=\\\"\\\\&quot;\\\\&quot;\\\" auto;=\\\"\\\\&quot;\\\\&quot;\\\" outline:=\\\"\\\\&quot;\\\\&quot;\\\" transparent=\\\"\\\\&quot;\\\\&quot;\\\" solid=\\\"\\\\&quot;\\\\&quot;\\\" 1px;=\\\"\\\\&quot;\\\\&quot;\\\" outline-offset:=\\\"\\\\&quot;\\\\&quot;\\\" -1px;=\\\"\\\\&quot;\\\\&quot;\\\" height:=\\\"\\\\&quot;\\\\&quot;\\\" 20px;=\\\"\\\\&quot;\\\\&quot;\\\" min-height:=\\\"\\\\&quot;\\\\&quot;\\\" 20px;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><div class=\\\"\\\\&quot;\\\\\\\\&quot;kHtcsd\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;display:\\\\&quot;\\\" flex;=\\\"\\\\&quot;\\\\&quot;\\\" align-items:=\\\"\\\\&quot;\\\\&quot;\\\" center;=\\\"\\\\&quot;\\\\&quot;\\\" justify-content:=\\\"\\\\&quot;\\\\&quot;\\\" width:=\\\"\\\\&quot;\\\\&quot;\\\" 28px;=\\\"\\\\&quot;\\\\&quot;\\\" border-radius:=\\\"\\\\&quot;\\\\&quot;\\\" 9999px;=\\\"\\\\&quot;\\\\&quot;\\\" height:=\\\"\\\\&quot;\\\\&quot;\\\" 20px;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><span class=\\\"\\\\&quot;\\\\\\\\&quot;d3o3Ad\\\\&quot;\\\" gjdc8e=\\\"\\\\&quot;\\\\&quot;\\\" hkv2pe\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;display:\\\\&quot;\\\" flex;=\\\"\\\\&quot;\\\\&quot;\\\" align-items:=\\\"\\\\&quot;\\\\&quot;\\\" center;=\\\"\\\\&quot;\\\\&quot;\\\" margin:=\\\"\\\\&quot;\\\\&quot;\\\" 0px;=\\\"\\\\&quot;\\\\&quot;\\\" background-image:=\\\"\\\\&quot;\\\\&quot;\\\" unset=\\\"\\\\&quot;\\\\&quot;\\\" !important;=\\\"\\\\&quot;\\\\&quot;\\\" background-position:=\\\"\\\\&quot;\\\\&quot;\\\" background-size:=\\\"\\\\&quot;\\\\&quot;\\\" background-repeat:=\\\"\\\\&quot;\\\\&quot;\\\" background-attachment:=\\\"\\\\&quot;\\\\&quot;\\\" background-origin:=\\\"\\\\&quot;\\\\&quot;\\\" background-clip:=\\\"\\\\&quot;\\\\&quot;\\\" !important;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><span class=\\\"\\\\&quot;\\\\\\\\&quot;iPjmzb\\\\&quot;\\\" sorfoc=\\\"\\\\&quot;\\\\&quot;\\\" gngsdf\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;display:\\\\&quot;\\\" flex;=\\\"\\\\&quot;\\\\&quot;\\\" height:=\\\"\\\\&quot;\\\\&quot;\\\" unset;=\\\"\\\\&quot;\\\\&quot;\\\" rotate:=\\\"\\\\&quot;\\\\&quot;\\\" 135deg;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><span class=\\\"\\\\&quot;\\\\\\\\&quot;z1asCe\\\\&quot;\\\" sb7k4e\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;display:\\\\&quot;\\\" inline-block;=\\\"\\\\&quot;\\\\&quot;\\\" fill:=\\\"\\\\&quot;\\\\&quot;\\\" currentcolor;=\\\"\\\\&quot;\\\\&quot;\\\" height:=\\\"\\\\&quot;\\\\&quot;\\\" 18px;=\\\"\\\\&quot;\\\\&quot;\\\" line-height:=\\\"\\\\&quot;\\\\&quot;\\\" position:=\\\"\\\\&quot;\\\\&quot;\\\" relative;=\\\"\\\\&quot;\\\\&quot;\\\" width:=\\\"\\\\&quot;\\\\&quot;\\\" 18px;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><svg focusable=\\\"\\\\&quot;\\\\\\\\&quot;false\\\\\\\\&quot;\\\\&quot;\\\" xmlns=\\\"\\\\&quot;\\\\\\\\&quot;http://www.w3.org/2000/svg\\\\\\\\&quot;\\\\&quot;\\\" viewBox=\\\"\\\\&quot;\\\\\\\\&quot;0\\\\&quot;\\\" 0=\\\"\\\\&quot;\\\\&quot;\\\" 24=\\\"\\\\&quot;\\\\&quot;\\\" 24\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><path d=\\\"\\\\&quot;\\\\\\\\&quot;M3.9\\\\&quot;\\\" 12c0-1.71=\\\"\\\\&quot;\\\\&quot;\\\" 1.39-3.1=\\\"\\\\&quot;\\\\&quot;\\\" 3.1-3.1h4v7h7c-2.76=\\\"\\\\&quot;\\\\&quot;\\\" 0-5=\\\"\\\\&quot;\\\\&quot;\\\" 2.24-5=\\\"\\\\&quot;\\\\&quot;\\\" 5s2.24=\\\"\\\\&quot;\\\\&quot;\\\" 5=\\\"\\\\&quot;\\\\&quot;\\\" 5h4v-1.9h7c-1.71=\\\"\\\\&quot;\\\\&quot;\\\" 0-3.1-1.39-3.1-3.1zm8=\\\"\\\\&quot;\\\\&quot;\\\" 13h8v-2h8v2zm9-6h-4v1.9h4c1.71=\\\"\\\\&quot;\\\\&quot;\\\" 0=\\\"\\\\&quot;\\\\&quot;\\\" 3.1=\\\"\\\\&quot;\\\\&quot;\\\" 1.39=\\\"\\\\&quot;\\\\&quot;\\\" 3.1s-1.39=\\\"\\\\&quot;\\\\&quot;\\\" 3.1-3.1=\\\"\\\\&quot;\\\\&quot;\\\" 3.1h-4v17h4c2.76=\\\"\\\\&quot;\\\\&quot;\\\" 5-2.24=\\\"\\\\&quot;\\\\&quot;\\\" 5-5s-2.24-5-5-5z\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"></path></svg></span></span></span></div></div></div></div></span></span></span></li><li style=\\\"\\\\&quot;\\\\\\\\&quot;margin:\\\\&quot;\\\" 0px;=\\\"\\\\&quot;\\\\&quot;\\\" padding:=\\\"\\\\&quot;\\\\&quot;\\\" 0px=\\\"\\\\&quot;\\\\&quot;\\\" 4px;=\\\"\\\\&quot;\\\\&quot;\\\" list-style-position:=\\\"\\\\&quot;\\\\&quot;\\\" inherit;=\\\"\\\\&quot;\\\\&quot;\\\" list-style-image:=\\\"\\\\&quot;\\\\&quot;\\\" list-style-type:=\\\"\\\\&quot;\\\\&quot;\\\" disc;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><span data-huuid=\\\"\\\\&quot;\\\\\\\\&quot;2901253832168686644\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"background-color: rgb(255, 255, 255);\\\" rgb(255,=\\\"\\\" 255,=\\\"\\\" 255);\\\\\\\"=\\\"\\\" 255);\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\">Di sistem operasi Linux (seperti Debian atau CentOS), Anda dapat menginstal paket DHCP server seperti&nbsp;<code class=\\\"\\\\&quot;\\\\\\\\&quot;mv6bHd\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;background-image:\\\\&quot;\\\" none;=\\\"\\\\&quot;\\\\&quot;\\\" background-position:=\\\"\\\\&quot;\\\\&quot;\\\" 0%=\\\"\\\\&quot;\\\\&quot;\\\" 0%;=\\\"\\\\&quot;\\\\&quot;\\\" background-size:=\\\"\\\\&quot;\\\\&quot;\\\" auto;=\\\"\\\\&quot;\\\\&quot;\\\" background-repeat:=\\\"\\\\&quot;\\\\&quot;\\\" repeat;=\\\"\\\\&quot;\\\\&quot;\\\" background-attachment:=\\\"\\\\&quot;\\\\&quot;\\\" scroll;=\\\"\\\\&quot;\\\\&quot;\\\" background-origin:=\\\"\\\\&quot;\\\\&quot;\\\" padding-box;=\\\"\\\\&quot;\\\\&quot;\\\" background-clip:=\\\"\\\\&quot;\\\\&quot;\\\" border-box;=\\\"\\\\&quot;\\\\&quot;\\\" border-radius:=\\\"\\\\&quot;\\\\&quot;\\\" 4px;=\\\"\\\\&quot;\\\\&quot;\\\" font-family:=\\\"\\\\&quot;\\\\&quot;\\\" monospace;=\\\"\\\\&quot;\\\\&quot;\\\" padding:=\\\"\\\\&quot;\\\\&quot;\\\" 0px=\\\"\\\\&quot;\\\\&quot;\\\" 4px;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\">isc-dhcp-server</code>.<span jscontroller=\\\"\\\\&quot;\\\\\\\\&quot;JHnpme\\\\\\\\&quot;\\\\&quot;\\\" class=\\\"\\\\&quot;\\\\\\\\&quot;pjBG2e\\\\\\\\&quot;\\\\&quot;\\\" data-cid=\\\"\\\\&quot;\\\\\\\\&quot;40450b36-2e73-4413-8486-a935ee9defee\\\\\\\\&quot;\\\\&quot;\\\" jsaction=\\\"\\\\&quot;\\\\\\\\&quot;rcuQ6b:npT2md\\\\\\\\&quot;\\\\&quot;\\\"><span class=\\\"\\\\&quot;\\\\\\\\&quot;UV3uM\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;text-wrap-mode:\\\\&quot;\\\" nowrap;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\">&nbsp;<div class=\\\"\\\\&quot;\\\\\\\\&quot;NPrrbc\\\\\\\\&quot;\\\\&quot;\\\" data-cid=\\\"\\\\&quot;\\\\\\\\&quot;40450b36-2e73-4413-8486-a935ee9defee\\\\\\\\&quot;\\\\&quot;\\\" data-uuids=\\\"\\\\&quot;\\\\\\\\&quot;2901253832168686644\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;margin-right:\\\\&quot;\\\" 6px;=\\\"\\\\&quot;\\\\&quot;\\\" display:=\\\"\\\\&quot;\\\\&quot;\\\" inline-flex;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><div jsname=\\\"\\\\&quot;\\\\\\\\&quot;HtgYJd\\\\\\\\&quot;\\\\&quot;\\\" class=\\\"\\\\&quot;\\\\\\\\&quot;BMebGe\\\\&quot;\\\" btku5b=\\\"\\\\&quot;\\\\&quot;\\\" fcrzyc=\\\"\\\\&quot;\\\\&quot;\\\" lwdv0e=\\\"\\\\&quot;\\\\&quot;\\\" fr7zsc=\\\"\\\\&quot;\\\\&quot;\\\" ojeuxf\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\" aria-label=\\\"\\\\&quot;\\\\\\\\&quot;Lihat\\\\&quot;\\\" link=\\\"\\\\&quot;\\\\&quot;\\\" terkait\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\" role=\\\"\\\\&quot;\\\\\\\\&quot;button\\\\\\\\&quot;\\\\&quot;\\\" tabindex=\\\"\\\\&quot;\\\\\\\\&quot;0\\\\\\\\&quot;\\\\&quot;\\\" jsaction=\\\"\\\\&quot;\\\\\\\\&quot;KjsqPd\\\\\\\\&quot;\\\\&quot;\\\" data-hveid=\\\"\\\\&quot;\\\\\\\\&quot;CC4QAQ\\\\\\\\&quot;\\\\&quot;\\\" data-ved=\\\"\\\\&quot;\\\\\\\\&quot;2ahUKEwiZhfb_rceNAxUP4DgGHUlfDBwQ3fYKegQILhAB\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;vertical-align:\\\\&quot;\\\" middle;=\\\"\\\\&quot;\\\\&quot;\\\" outline:=\\\"\\\\&quot;\\\\&quot;\\\" 0px;=\\\"\\\\&quot;\\\\&quot;\\\" -webkit-tap-highlight-color:=\\\"\\\\&quot;\\\\&quot;\\\" transparent;=\\\"\\\\&quot;\\\\&quot;\\\" display:=\\\"\\\\&quot;\\\\&quot;\\\" inline-flex=\\\"\\\\&quot;\\\\&quot;\\\" !important;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><div class=\\\"\\\\&quot;\\\\\\\\&quot;niO4u\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;display:\\\\&quot;\\\" flex;=\\\"\\\\&quot;\\\\&quot;\\\" justify-content:=\\\"\\\\&quot;\\\\&quot;\\\" center;=\\\"\\\\&quot;\\\\&quot;\\\" position:=\\\"\\\\&quot;\\\\&quot;\\\" relative;=\\\"\\\\&quot;\\\\&quot;\\\" align-items:=\\\"\\\\&quot;\\\\&quot;\\\" stretch;=\\\"\\\\&quot;\\\\&quot;\\\" width:=\\\"\\\\&quot;\\\\&quot;\\\" 28px;=\\\"\\\\&quot;\\\\&quot;\\\" border-radius:=\\\"\\\\&quot;\\\\&quot;\\\" 9999px;=\\\"\\\\&quot;\\\\&quot;\\\" margin:=\\\"\\\\&quot;\\\\&quot;\\\" 0px=\\\"\\\\&quot;\\\\&quot;\\\" auto;=\\\"\\\\&quot;\\\\&quot;\\\" outline:=\\\"\\\\&quot;\\\\&quot;\\\" transparent=\\\"\\\\&quot;\\\\&quot;\\\" solid=\\\"\\\\&quot;\\\\&quot;\\\" 1px;=\\\"\\\\&quot;\\\\&quot;\\\" outline-offset:=\\\"\\\\&quot;\\\\&quot;\\\" -1px;=\\\"\\\\&quot;\\\\&quot;\\\" height:=\\\"\\\\&quot;\\\\&quot;\\\" 20px;=\\\"\\\\&quot;\\\\&quot;\\\" min-height:=\\\"\\\\&quot;\\\\&quot;\\\" 20px;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><div class=\\\"\\\\&quot;\\\\\\\\&quot;kHtcsd\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;display:\\\\&quot;\\\" flex;=\\\"\\\\&quot;\\\\&quot;\\\" align-items:=\\\"\\\\&quot;\\\\&quot;\\\" center;=\\\"\\\\&quot;\\\\&quot;\\\" justify-content:=\\\"\\\\&quot;\\\\&quot;\\\" width:=\\\"\\\\&quot;\\\\&quot;\\\" 28px;=\\\"\\\\&quot;\\\\&quot;\\\" border-radius:=\\\"\\\\&quot;\\\\&quot;\\\" 9999px;=\\\"\\\\&quot;\\\\&quot;\\\" height:=\\\"\\\\&quot;\\\\&quot;\\\" 20px;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><span class=\\\"\\\\&quot;\\\\\\\\&quot;d3o3Ad\\\\&quot;\\\" gjdc8e=\\\"\\\\&quot;\\\\&quot;\\\" hkv2pe\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;display:\\\\&quot;\\\" flex;=\\\"\\\\&quot;\\\\&quot;\\\" align-items:=\\\"\\\\&quot;\\\\&quot;\\\" center;=\\\"\\\\&quot;\\\\&quot;\\\" margin:=\\\"\\\\&quot;\\\\&quot;\\\" 0px;=\\\"\\\\&quot;\\\\&quot;\\\" background-image:=\\\"\\\\&quot;\\\\&quot;\\\" unset=\\\"\\\\&quot;\\\\&quot;\\\" !important;=\\\"\\\\&quot;\\\\&quot;\\\" background-position:=\\\"\\\\&quot;\\\\&quot;\\\" background-size:=\\\"\\\\&quot;\\\\&quot;\\\" background-repeat:=\\\"\\\\&quot;\\\\&quot;\\\" background-attachment:=\\\"\\\\&quot;\\\\&quot;\\\" background-origin:=\\\"\\\\&quot;\\\\&quot;\\\" background-clip:=\\\"\\\\&quot;\\\\&quot;\\\" !important;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><span class=\\\"\\\\&quot;\\\\\\\\&quot;iPjmzb\\\\&quot;\\\" sorfoc=\\\"\\\\&quot;\\\\&quot;\\\" gngsdf\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;display:\\\\&quot;\\\" flex;=\\\"\\\\&quot;\\\\&quot;\\\" height:=\\\"\\\\&quot;\\\\&quot;\\\" unset;=\\\"\\\\&quot;\\\\&quot;\\\" rotate:=\\\"\\\\&quot;\\\\&quot;\\\" 135deg;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><span class=\\\"\\\\&quot;\\\\\\\\&quot;z1asCe\\\\&quot;\\\" sb7k4e\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;display:\\\\&quot;\\\" inline-block;=\\\"\\\\&quot;\\\\&quot;\\\" fill:=\\\"\\\\&quot;\\\\&quot;\\\" currentcolor;=\\\"\\\\&quot;\\\\&quot;\\\" height:=\\\"\\\\&quot;\\\\&quot;\\\" 18px;=\\\"\\\\&quot;\\\\&quot;\\\" line-height:=\\\"\\\\&quot;\\\\&quot;\\\" position:=\\\"\\\\&quot;\\\\&quot;\\\" relative;=\\\"\\\\&quot;\\\\&quot;\\\" width:=\\\"\\\\&quot;\\\\&quot;\\\" 18px;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><svg focusable=\\\"\\\\&quot;\\\\\\\\&quot;false\\\\\\\\&quot;\\\\&quot;\\\" xmlns=\\\"\\\\&quot;\\\\\\\\&quot;http://www.w3.org/2000/svg\\\\\\\\&quot;\\\\&quot;\\\" viewBox=\\\"\\\\&quot;\\\\\\\\&quot;0\\\\&quot;\\\" 0=\\\"\\\\&quot;\\\\&quot;\\\" 24=\\\"\\\\&quot;\\\\&quot;\\\" 24\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><path d=\\\"\\\\&quot;\\\\\\\\&quot;M3.9\\\\&quot;\\\" 12c0-1.71=\\\"\\\\&quot;\\\\&quot;\\\" 1.39-3.1=\\\"\\\\&quot;\\\\&quot;\\\" 3.1-3.1h4v7h7c-2.76=\\\"\\\\&quot;\\\\&quot;\\\" 0-5=\\\"\\\\&quot;\\\\&quot;\\\" 2.24-5=\\\"\\\\&quot;\\\\&quot;\\\" 5s2.24=\\\"\\\\&quot;\\\\&quot;\\\" 5=\\\"\\\\&quot;\\\\&quot;\\\" 5h4v-1.9h7c-1.71=\\\"\\\\&quot;\\\\&quot;\\\" 0-3.1-1.39-3.1-3.1zm8=\\\"\\\\&quot;\\\\&quot;\\\" 13h8v-2h8v2zm9-6h-4v1.9h4c1.71=\\\"\\\\&quot;\\\\&quot;\\\" 0=\\\"\\\\&quot;\\\\&quot;\\\" 3.1=\\\"\\\\&quot;\\\\&quot;\\\" 1.39=\\\"\\\\&quot;\\\\&quot;\\\" 3.1s-1.39=\\\"\\\\&quot;\\\\&quot;\\\" 3.1-3.1=\\\"\\\\&quot;\\\\&quot;\\\" 3.1h-4v17h4c2.76=\\\"\\\\&quot;\\\\&quot;\\\" 5-2.24=\\\"\\\\&quot;\\\\&quot;\\\" 5-5s-2.24-5-5-5z\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"></path></svg></span></span></span></div></div></div></div></span></span></span></li></ul></div></div></div></div></li><div class=\\\"\\\\&quot;\\\\\\\\&quot;bsmXxe\\\\\\\\&quot;\\\\&quot;\\\" id=\\\"\\\\&quot;\\\\\\\\&quot;wqE3aJmcDI_A4-EPyb6x4AE__58\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;display:\\\\&quot;\\\" contents;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><li class=\\\"\\\\&quot;\\\\\\\\&quot;K3KsMc\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;margin:\\\\&quot;\\\" 0px=\\\"\\\\&quot;\\\\&quot;\\\" 8px;=\\\"\\\\&quot;\\\\&quot;\\\" padding:=\\\"\\\\&quot;\\\\&quot;\\\" 4px;=\\\"\\\\&quot;\\\\&quot;\\\" list-style-position:=\\\"\\\\&quot;\\\\&quot;\\\" inherit;=\\\"\\\\&quot;\\\\&quot;\\\" list-style-image:=\\\"\\\\&quot;\\\\&quot;\\\" list-style-type:=\\\"\\\\&quot;\\\\&quot;\\\" none;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><div class=\\\"\\\\&quot;\\\\\\\\&quot;zMgcWd\\\\&quot;\\\" dskvsb\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\" data-il=\\\"\\\\&quot;\\\\\\\\&quot;\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;padding-bottom:\\\\&quot;\\\" 0px;=\\\"\\\\&quot;\\\\&quot;\\\" padding-top:=\\\"\\\\&quot;\\\\&quot;\\\" border-bottom:=\\\"\\\\&quot;\\\\&quot;\\\" none;=\\\"\\\\&quot;\\\\&quot;\\\" margin-left:=\\\"\\\\&quot;\\\\&quot;\\\" -28px;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><div data-crb-p=\\\"\\\\&quot;\\\\\\\\&quot;\\\\\\\\&quot;\\\\&quot;\\\"><div class=\\\"\\\\&quot;\\\\\\\\&quot;xFTqob\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;flex:\\\\&quot;\\\" 1=\\\"\\\\&quot;\\\\&quot;\\\" 0%;=\\\"\\\\&quot;\\\\&quot;\\\" min-width:=\\\"\\\\&quot;\\\\&quot;\\\" 0px;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><div class=\\\"\\\\&quot;\\\\\\\\&quot;Gur8Ad\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;line-height:\\\\&quot;\\\" 22px;=\\\"\\\\&quot;\\\\&quot;\\\" overflow:=\\\"\\\\&quot;\\\\&quot;\\\" hidden;=\\\"\\\\&quot;\\\\&quot;\\\" padding-bottom:=\\\"\\\\&quot;\\\\&quot;\\\" 4px;=\\\"\\\\&quot;\\\\&quot;\\\" transition:=\\\"\\\\&quot;\\\\&quot;\\\" transform=\\\"\\\\&quot;\\\\&quot;\\\" 200ms=\\\"\\\\&quot;\\\\&quot;\\\" cubic-bezier(0.2,=\\\"\\\\&quot;\\\\&quot;\\\" 0,=\\\"\\\\&quot;\\\\&quot;\\\" 1);\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><span style=\\\"background-color: rgb(255, 255, 255);\\\" rgb(255,=\\\"\\\" 255,=\\\"\\\" 255);\\\\\\\"=\\\"\\\" 255);\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><strong>2.&nbsp;</strong><span data-huuid=\\\"\\\\&quot;\\\\\\\\&quot;2901253832168687950\\\\\\\\&quot;\\\\&quot;\\\"><strong>Menyediakan Rentang IP:</strong></span></span></div><div class=\\\"\\\\&quot;\\\\\\\\&quot;vM0jzc\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;letter-spacing:\\\\&quot;\\\" 0.1px;=\\\"\\\\&quot;\\\\&quot;\\\" line-height:=\\\"\\\\&quot;\\\\&quot;\\\" 22px;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><div class=\\\"\\\\&quot;\\\\\\\\&quot;bsmXxe\\\\\\\\&quot;\\\\&quot;\\\" id=\\\"\\\\&quot;\\\\\\\\&quot;wqE3aJmcDI_A4-EPyb6x4AE__62\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;display:\\\\&quot;\\\" contents;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><ul jscontroller=\\\"\\\\&quot;\\\\\\\\&quot;M2ABbc\\\\\\\\&quot;\\\\&quot;\\\" jsaction=\\\"\\\\&quot;\\\\\\\\&quot;jZtoLb:SaHfyb\\\\\\\\&quot;\\\\&quot;\\\" data-hveid=\\\"\\\\&quot;\\\\\\\\&quot;CM4BEAE\\\\\\\\&quot;\\\\&quot;\\\" data-ved=\\\"\\\\&quot;\\\\\\\\&quot;2ahUKEwiZhfb_rceNAxUP4DgGHUlfDBwQm_YKegUIzgEQAQ\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;margin:\\\\&quot;\\\" 10px=\\\"\\\\&quot;\\\\&quot;\\\" 0px=\\\"\\\\&quot;\\\\&quot;\\\" 20px;=\\\"\\\\&quot;\\\\&quot;\\\" padding:=\\\"\\\\&quot;\\\\&quot;\\\" 24px;=\\\"\\\\&quot;\\\\&quot;\\\" line-height:=\\\"\\\\&quot;\\\\&quot;\\\" 22px;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><div class=\\\"\\\\&quot;\\\\\\\\&quot;bsmXxe\\\\\\\\&quot;\\\\&quot;\\\" id=\\\"\\\\&quot;\\\\\\\\&quot;wqE3aJmcDI_A4-EPyb6x4AE__65\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;display:\\\\&quot;\\\" contents;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><li style=\\\"\\\\&quot;\\\\\\\\&quot;margin:\\\\&quot;\\\" 0px=\\\"\\\\&quot;\\\\&quot;\\\" 8px;=\\\"\\\\&quot;\\\\&quot;\\\" padding:=\\\"\\\\&quot;\\\\&quot;\\\" 4px;=\\\"\\\\&quot;\\\\&quot;\\\" list-style-position:=\\\"\\\\&quot;\\\\&quot;\\\" inherit;=\\\"\\\\&quot;\\\\&quot;\\\" list-style-image:=\\\"\\\\&quot;\\\\&quot;\\\" list-style-type:=\\\"\\\\&quot;\\\\&quot;\\\" disc;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><span data-huuid=\\\"\\\\&quot;\\\\\\\\&quot;2901253832168687861\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"background-color: rgb(255, 255, 255);\\\" rgb(255,=\\\"\\\" 255,=\\\"\\\" 255);\\\\\\\"=\\\"\\\" 255);\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\">Tentukan rentang alamat IP yang akan dialokasikan kepada klien.<span jscontroller=\\\"\\\\&quot;\\\\\\\\&quot;JHnpme\\\\\\\\&quot;\\\\&quot;\\\" class=\\\"\\\\&quot;\\\\\\\\&quot;pjBG2e\\\\\\\\&quot;\\\\&quot;\\\" data-cid=\\\"\\\\&quot;\\\\\\\\&quot;219b72ec-5335-4234-9e39-76e50f4cc52d\\\\\\\\&quot;\\\\&quot;\\\" jsaction=\\\"\\\\&quot;\\\\\\\\&quot;rcuQ6b:npT2md\\\\\\\\&quot;\\\\&quot;\\\"><span class=\\\"\\\\&quot;\\\\\\\\&quot;UV3uM\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;text-wrap-mode:\\\\&quot;\\\" nowrap;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\">&nbsp;<div class=\\\"\\\\&quot;\\\\\\\\&quot;NPrrbc\\\\\\\\&quot;\\\\&quot;\\\" data-cid=\\\"\\\\&quot;\\\\\\\\&quot;219b72ec-5335-4234-9e39-76e50f4cc52d\\\\\\\\&quot;\\\\&quot;\\\" data-uuids=\\\"\\\\&quot;\\\\\\\\&quot;2901253832168687861\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;margin-right:\\\\&quot;\\\" 6px;=\\\"\\\\&quot;\\\\&quot;\\\" display:=\\\"\\\\&quot;\\\\&quot;\\\" inline-flex;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><div jsname=\\\"\\\\&quot;\\\\\\\\&quot;HtgYJd\\\\\\\\&quot;\\\\&quot;\\\" class=\\\"\\\\&quot;\\\\\\\\&quot;BMebGe\\\\&quot;\\\" btku5b=\\\"\\\\&quot;\\\\&quot;\\\" fcrzyc=\\\"\\\\&quot;\\\\&quot;\\\" lwdv0e=\\\"\\\\&quot;\\\\&quot;\\\" fr7zsc=\\\"\\\\&quot;\\\\&quot;\\\" ojeuxf\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\" aria-label=\\\"\\\\&quot;\\\\\\\\&quot;Lihat\\\\&quot;\\\" link=\\\"\\\\&quot;\\\\&quot;\\\" terkait\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\" role=\\\"\\\\&quot;\\\\\\\\&quot;button\\\\\\\\&quot;\\\\&quot;\\\" tabindex=\\\"\\\\&quot;\\\\\\\\&quot;0\\\\\\\\&quot;\\\\&quot;\\\" jsaction=\\\"\\\\&quot;\\\\\\\\&quot;KjsqPd\\\\\\\\&quot;\\\\&quot;\\\" data-hveid=\\\"\\\\&quot;\\\\\\\\&quot;CPUBEAE\\\\\\\\&quot;\\\\&quot;\\\" data-ved=\\\"\\\\&quot;\\\\\\\\&quot;2ahUKEwiZhfb_rceNAxUP4DgGHUlfDBwQ3fYKegUI9QEQAQ\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;vertical-align:\\\\&quot;\\\" middle;=\\\"\\\\&quot;\\\\&quot;\\\" outline:=\\\"\\\\&quot;\\\\&quot;\\\" 0px;=\\\"\\\\&quot;\\\\&quot;\\\" -webkit-tap-highlight-color:=\\\"\\\\&quot;\\\\&quot;\\\" transparent;=\\\"\\\\&quot;\\\\&quot;\\\" display:=\\\"\\\\&quot;\\\\&quot;\\\" inline-flex=\\\"\\\\&quot;\\\\&quot;\\\" !important;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><div class=\\\"\\\\&quot;\\\\\\\\&quot;niO4u\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;display:\\\\&quot;\\\" flex;=\\\"\\\\&quot;\\\\&quot;\\\" justify-content:=\\\"\\\\&quot;\\\\&quot;\\\" center;=\\\"\\\\&quot;\\\\&quot;\\\" position:=\\\"\\\\&quot;\\\\&quot;\\\" relative;=\\\"\\\\&quot;\\\\&quot;\\\" align-items:=\\\"\\\\&quot;\\\\&quot;\\\" stretch;=\\\"\\\\&quot;\\\\&quot;\\\" width:=\\\"\\\\&quot;\\\\&quot;\\\" 28px;=\\\"\\\\&quot;\\\\&quot;\\\" border-radius:=\\\"\\\\&quot;\\\\&quot;\\\" 9999px;=\\\"\\\\&quot;\\\\&quot;\\\" margin:=\\\"\\\\&quot;\\\\&quot;\\\" 0px=\\\"\\\\&quot;\\\\&quot;\\\" auto;=\\\"\\\\&quot;\\\\&quot;\\\" outline:=\\\"\\\\&quot;\\\\&quot;\\\" transparent=\\\"\\\\&quot;\\\\&quot;\\\" solid=\\\"\\\\&quot;\\\\&quot;\\\" 1px;=\\\"\\\\&quot;\\\\&quot;\\\" outline-offset:=\\\"\\\\&quot;\\\\&quot;\\\" -1px;=\\\"\\\\&quot;\\\\&quot;\\\" height:=\\\"\\\\&quot;\\\\&quot;\\\" 20px;=\\\"\\\\&quot;\\\\&quot;\\\" min-height:=\\\"\\\\&quot;\\\\&quot;\\\" 20px;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><div class=\\\"\\\\&quot;\\\\\\\\&quot;kHtcsd\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;display:\\\\&quot;\\\" flex;=\\\"\\\\&quot;\\\\&quot;\\\" align-items:=\\\"\\\\&quot;\\\\&quot;\\\" center;=\\\"\\\\&quot;\\\\&quot;\\\" justify-content:=\\\"\\\\&quot;\\\\&quot;\\\" width:=\\\"\\\\&quot;\\\\&quot;\\\" 28px;=\\\"\\\\&quot;\\\\&quot;\\\" border-radius:=\\\"\\\\&quot;\\\\&quot;\\\" 9999px;=\\\"\\\\&quot;\\\\&quot;\\\" height:=\\\"\\\\&quot;\\\\&quot;\\\" 20px;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><span class=\\\"\\\\&quot;\\\\\\\\&quot;d3o3Ad\\\\&quot;\\\" gjdc8e=\\\"\\\\&quot;\\\\&quot;\\\" hkv2pe\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;display:\\\\&quot;\\\" flex;=\\\"\\\\&quot;\\\\&quot;\\\" align-items:=\\\"\\\\&quot;\\\\&quot;\\\" center;=\\\"\\\\&quot;\\\\&quot;\\\" margin:=\\\"\\\\&quot;\\\\&quot;\\\" 0px;=\\\"\\\\&quot;\\\\&quot;\\\" background-image:=\\\"\\\\&quot;\\\\&quot;\\\" unset=\\\"\\\\&quot;\\\\&quot;\\\" !important;=\\\"\\\\&quot;\\\\&quot;\\\" background-position:=\\\"\\\\&quot;\\\\&quot;\\\" background-size:=\\\"\\\\&quot;\\\\&quot;\\\" background-repeat:=\\\"\\\\&quot;\\\\&quot;\\\" background-attachment:=\\\"\\\\&quot;\\\\&quot;\\\" background-origin:=\\\"\\\\&quot;\\\\&quot;\\\" background-clip:=\\\"\\\\&quot;\\\\&quot;\\\" !important;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><span class=\\\"\\\\&quot;\\\\\\\\&quot;iPjmzb\\\\&quot;\\\" sorfoc=\\\"\\\\&quot;\\\\&quot;\\\" gngsdf\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;display:\\\\&quot;\\\" flex;=\\\"\\\\&quot;\\\\&quot;\\\" height:=\\\"\\\\&quot;\\\\&quot;\\\" unset;=\\\"\\\\&quot;\\\\&quot;\\\" rotate:=\\\"\\\\&quot;\\\\&quot;\\\" 135deg;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><span class=\\\"\\\\&quot;\\\\\\\\&quot;z1asCe\\\\&quot;\\\" sb7k4e\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;display:\\\\&quot;\\\" inline-block;=\\\"\\\\&quot;\\\\&quot;\\\" fill:=\\\"\\\\&quot;\\\\&quot;\\\" currentcolor;=\\\"\\\\&quot;\\\\&quot;\\\" height:=\\\"\\\\&quot;\\\\&quot;\\\" 18px;=\\\"\\\\&quot;\\\\&quot;\\\" line-height:=\\\"\\\\&quot;\\\\&quot;\\\" position:=\\\"\\\\&quot;\\\\&quot;\\\" relative;=\\\"\\\\&quot;\\\\&quot;\\\" width:=\\\"\\\\&quot;\\\\&quot;\\\" 18px;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><svg focusable=\\\"\\\\&quot;\\\\\\\\&quot;false\\\\\\\\&quot;\\\\&quot;\\\" xmlns=\\\"\\\\&quot;\\\\\\\\&quot;http://www.w3.org/2000/svg\\\\\\\\&quot;\\\\&quot;\\\" viewBox=\\\"\\\\&quot;\\\\\\\\&quot;0\\\\&quot;\\\" 0=\\\"\\\\&quot;\\\\&quot;\\\" 24=\\\"\\\\&quot;\\\\&quot;\\\" 24\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><path d=\\\"\\\\&quot;\\\\\\\\&quot;M3.9\\\\&quot;\\\" 12c0-1.71=\\\"\\\\&quot;\\\\&quot;\\\" 1.39-3.1=\\\"\\\\&quot;\\\\&quot;\\\" 3.1-3.1h4v7h7c-2.76=\\\"\\\\&quot;\\\\&quot;\\\" 0-5=\\\"\\\\&quot;\\\\&quot;\\\" 2.24-5=\\\"\\\\&quot;\\\\&quot;\\\" 5s2.24=\\\"\\\\&quot;\\\\&quot;\\\" 5=\\\"\\\\&quot;\\\\&quot;\\\" 5h4v-1.9h7c-1.71=\\\"\\\\&quot;\\\\&quot;\\\" 0-3.1-1.39-3.1-3.1zm8=\\\"\\\\&quot;\\\\&quot;\\\" 13h8v-2h8v2zm9-6h-4v1.9h4c1.71=\\\"\\\\&quot;\\\\&quot;\\\" 0=\\\"\\\\&quot;\\\\&quot;\\\" 3.1=\\\"\\\\&quot;\\\\&quot;\\\" 1.39=\\\"\\\\&quot;\\\\&quot;\\\" 3.1s-1.39=\\\"\\\\&quot;\\\\&quot;\\\" 3.1-3.1=\\\"\\\\&quot;\\\\&quot;\\\" 3.1h-4v17h4c2.76=\\\"\\\\&quot;\\\\&quot;\\\" 5-2.24=\\\"\\\\&quot;\\\\&quot;\\\" 5-5s-2.24-5-5-5z\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"></path></svg></span></span></span></div></div></div></div></span></span></span></li></div><div class=\\\"\\\\&quot;\\\\\\\\&quot;bsmXxe\\\\\\\\&quot;\\\\&quot;\\\" id=\\\"\\\\&quot;\\\\\\\\&quot;wqE3aJmcDI_A4-EPyb6x4AE__70\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;display:\\\\&quot;\\\" contents;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><li style=\\\"\\\\&quot;\\\\\\\\&quot;margin:\\\\&quot;\\\" 0px;=\\\"\\\\&quot;\\\\&quot;\\\" padding:=\\\"\\\\&quot;\\\\&quot;\\\" 0px=\\\"\\\\&quot;\\\\&quot;\\\" 4px;=\\\"\\\\&quot;\\\\&quot;\\\" list-style-position:=\\\"\\\\&quot;\\\\&quot;\\\" inherit;=\\\"\\\\&quot;\\\\&quot;\\\" list-style-image:=\\\"\\\\&quot;\\\\&quot;\\\" list-style-type:=\\\"\\\\&quot;\\\\&quot;\\\" disc;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><span data-huuid=\\\"\\\\&quot;\\\\\\\\&quot;2901253832168689167\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"background-color: rgb(255, 255, 255);\\\" rgb(255,=\\\"\\\" 255,=\\\"\\\" 255);\\\\\\\"=\\\"\\\" 255);\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\">Tentukan juga alamat IP yang dikecualikan (misalnya, untuk server atau perangkat yang memiliki alamat IP statis).<span jscontroller=\\\"\\\\&quot;\\\\\\\\&quot;JHnpme\\\\\\\\&quot;\\\\&quot;\\\" class=\\\"\\\\&quot;\\\\\\\\&quot;pjBG2e\\\\\\\\&quot;\\\\&quot;\\\" data-cid=\\\"\\\\&quot;\\\\\\\\&quot;848b6c4e-ba5b-423e-9c3f-128b0cfcc668\\\\\\\\&quot;\\\\&quot;\\\" jsaction=\\\"\\\\&quot;\\\\\\\\&quot;rcuQ6b:npT2md\\\\\\\\&quot;\\\\&quot;\\\"><span class=\\\"\\\\&quot;\\\\\\\\&quot;UV3uM\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;text-wrap-mode:\\\\&quot;\\\" nowrap;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\">&nbsp;<div class=\\\"\\\\&quot;\\\\\\\\&quot;NPrrbc\\\\\\\\&quot;\\\\&quot;\\\" data-cid=\\\"\\\\&quot;\\\\\\\\&quot;848b6c4e-ba5b-423e-9c3f-128b0cfcc668\\\\\\\\&quot;\\\\&quot;\\\" data-uuids=\\\"\\\\&quot;\\\\\\\\&quot;2901253832168689167\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;margin-right:\\\\&quot;\\\" 6px;=\\\"\\\\&quot;\\\\&quot;\\\" display:=\\\"\\\\&quot;\\\\&quot;\\\" inline-flex;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><div jsname=\\\"\\\\&quot;\\\\\\\\&quot;HtgYJd\\\\\\\\&quot;\\\\&quot;\\\" class=\\\"\\\\&quot;\\\\\\\\&quot;BMebGe\\\\&quot;\\\" btku5b=\\\"\\\\&quot;\\\\&quot;\\\" fcrzyc=\\\"\\\\&quot;\\\\&quot;\\\" lwdv0e=\\\"\\\\&quot;\\\\&quot;\\\" fr7zsc=\\\"\\\\&quot;\\\\&quot;\\\" ojeuxf\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\" aria-label=\\\"\\\\&quot;\\\\\\\\&quot;Lihat\\\\&quot;\\\" link=\\\"\\\\&quot;\\\\&quot;\\\" terkait\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\" role=\\\"\\\\&quot;\\\\\\\\&quot;button\\\\\\\\&quot;\\\\&quot;\\\" tabindex=\\\"\\\\&quot;\\\\\\\\&quot;0\\\\\\\\&quot;\\\\&quot;\\\" jsaction=\\\"\\\\&quot;\\\\\\\\&quot;KjsqPd\\\\\\\\&quot;\\\\&quot;\\\" data-hveid=\\\"\\\\&quot;\\\\\\\\&quot;CP8BEAE\\\\\\\\&quot;\\\\&quot;\\\" data-ved=\\\"\\\\&quot;\\\\\\\\&quot;2ahUKEwiZhfb_rceNAxUP4DgGHUlfDBwQ3fYKegUI_wEQAQ\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;vertical-align:\\\\&quot;\\\" middle;=\\\"\\\\&quot;\\\\&quot;\\\" outline:=\\\"\\\\&quot;\\\\&quot;\\\" 0px;=\\\"\\\\&quot;\\\\&quot;\\\" -webkit-tap-highlight-color:=\\\"\\\\&quot;\\\\&quot;\\\" transparent;=\\\"\\\\&quot;\\\\&quot;\\\" display:=\\\"\\\\&quot;\\\\&quot;\\\" inline-flex=\\\"\\\\&quot;\\\\&quot;\\\" !important;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><div class=\\\"\\\\&quot;\\\\\\\\&quot;niO4u\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;display:\\\\&quot;\\\" flex;=\\\"\\\\&quot;\\\\&quot;\\\" justify-content:=\\\"\\\\&quot;\\\\&quot;\\\" center;=\\\"\\\\&quot;\\\\&quot;\\\" position:=\\\"\\\\&quot;\\\\&quot;\\\" relative;=\\\"\\\\&quot;\\\\&quot;\\\" align-items:=\\\"\\\\&quot;\\\\&quot;\\\" stretch;=\\\"\\\\&quot;\\\\&quot;\\\" width:=\\\"\\\\&quot;\\\\&quot;\\\" 28px;=\\\"\\\\&quot;\\\\&quot;\\\" border-radius:=\\\"\\\\&quot;\\\\&quot;\\\" 9999px;=\\\"\\\\&quot;\\\\&quot;\\\" margin:=\\\"\\\\&quot;\\\\&quot;\\\" 0px=\\\"\\\\&quot;\\\\&quot;\\\" auto;=\\\"\\\\&quot;\\\\&quot;\\\" outline:=\\\"\\\\&quot;\\\\&quot;\\\" transparent=\\\"\\\\&quot;\\\\&quot;\\\" solid=\\\"\\\\&quot;\\\\&quot;\\\" 1px;=\\\"\\\\&quot;\\\\&quot;\\\" outline-offset:=\\\"\\\\&quot;\\\\&quot;\\\" -1px;=\\\"\\\\&quot;\\\\&quot;\\\" height:=\\\"\\\\&quot;\\\\&quot;\\\" 20px;=\\\"\\\\&quot;\\\\&quot;\\\" min-height:=\\\"\\\\&quot;\\\\&quot;\\\" 20px;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><div class=\\\"\\\\&quot;\\\\\\\\&quot;kHtcsd\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;display:\\\\&quot;\\\" flex;=\\\"\\\\&quot;\\\\&quot;\\\" align-items:=\\\"\\\\&quot;\\\\&quot;\\\" center;=\\\"\\\\&quot;\\\\&quot;\\\" justify-content:=\\\"\\\\&quot;\\\\&quot;\\\" width:=\\\"\\\\&quot;\\\\&quot;\\\" 28px;=\\\"\\\\&quot;\\\\&quot;\\\" border-radius:=\\\"\\\\&quot;\\\\&quot;\\\" 9999px;=\\\"\\\\&quot;\\\\&quot;\\\" height:=\\\"\\\\&quot;\\\\&quot;\\\" 20px;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><span class=\\\"\\\\&quot;\\\\\\\\&quot;d3o3Ad\\\\&quot;\\\" gjdc8e=\\\"\\\\&quot;\\\\&quot;\\\" hkv2pe\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;display:\\\\&quot;\\\" flex;=\\\"\\\\&quot;\\\\&quot;\\\" align-items:=\\\"\\\\&quot;\\\\&quot;\\\" center;=\\\"\\\\&quot;\\\\&quot;\\\" margin:=\\\"\\\\&quot;\\\\&quot;\\\" 0px;=\\\"\\\\&quot;\\\\&quot;\\\" background-image:=\\\"\\\\&quot;\\\\&quot;\\\" unset=\\\"\\\\&quot;\\\\&quot;\\\" !important;=\\\"\\\\&quot;\\\\&quot;\\\" background-position:=\\\"\\\\&quot;\\\\&quot;\\\" background-size:=\\\"\\\\&quot;\\\\&quot;\\\" background-repeat:=\\\"\\\\&quot;\\\\&quot;\\\" background-attachment:=\\\"\\\\&quot;\\\\&quot;\\\" background-origin:=\\\"\\\\&quot;\\\\&quot;\\\" background-clip:=\\\"\\\\&quot;\\\\&quot;\\\" !important;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><span class=\\\"\\\\&quot;\\\\\\\\&quot;iPjmzb\\\\&quot;\\\" sorfoc=\\\"\\\\&quot;\\\\&quot;\\\" gngsdf\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;display:\\\\&quot;\\\" flex;=\\\"\\\\&quot;\\\\&quot;\\\" height:=\\\"\\\\&quot;\\\\&quot;\\\" unset;=\\\"\\\\&quot;\\\\&quot;\\\" rotate:=\\\"\\\\&quot;\\\\&quot;\\\" 135deg;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><span class=\\\"\\\\&quot;\\\\\\\\&quot;z1asCe\\\\&quot;\\\" sb7k4e\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;display:\\\\&quot;\\\" inline-block;=\\\"\\\\&quot;\\\\&quot;\\\" fill:=\\\"\\\\&quot;\\\\&quot;\\\" currentcolor;=\\\"\\\\&quot;\\\\&quot;\\\" height:=\\\"\\\\&quot;\\\\&quot;\\\" 18px;=\\\"\\\\&quot;\\\\&quot;\\\" line-height:=\\\"\\\\&quot;\\\\&quot;\\\" position:=\\\"\\\\&quot;\\\\&quot;\\\" relative;=\\\"\\\\&quot;\\\\&quot;\\\" width:=\\\"\\\\&quot;\\\\&quot;\\\" 18px;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><svg focusable=\\\"\\\\&quot;\\\\\\\\&quot;false\\\\\\\\&quot;\\\\&quot;\\\" xmlns=\\\"\\\\&quot;\\\\\\\\&quot;http://www.w3.org/2000/svg\\\\\\\\&quot;\\\\&quot;\\\" viewBox=\\\"\\\\&quot;\\\\\\\\&quot;0\\\\&quot;\\\" 0=\\\"\\\\&quot;\\\\&quot;\\\" 24=\\\"\\\\&quot;\\\\&quot;\\\" 24\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><path d=\\\"\\\\&quot;\\\\\\\\&quot;M3.9\\\\&quot;\\\" 12c0-1.71=\\\"\\\\&quot;\\\\&quot;\\\" 1.39-3.1=\\\"\\\\&quot;\\\\&quot;\\\" 3.1-3.1h4v7h7c-2.76=\\\"\\\\&quot;\\\\&quot;\\\" 0-5=\\\"\\\\&quot;\\\\&quot;\\\" 2.24-5=\\\"\\\\&quot;\\\\&quot;\\\" 5s2.24=\\\"\\\\&quot;\\\\&quot;\\\" 5=\\\"\\\\&quot;\\\\&quot;\\\" 5h4v-1.9h7c-1.71=\\\"\\\\&quot;\\\\&quot;\\\" 0-3.1-1.39-3.1-3.1zm8=\\\"\\\\&quot;\\\\&quot;\\\" 13h8v-2h8v2zm9-6h-4v1.9h4c1.71=\\\"\\\\&quot;\\\\&quot;\\\" 0=\\\"\\\\&quot;\\\\&quot;\\\" 3.1=\\\"\\\\&quot;\\\\&quot;\\\" 1.39=\\\"\\\\&quot;\\\\&quot;\\\" 3.1s-1.39=\\\"\\\\&quot;\\\\&quot;\\\" 3.1-3.1=\\\"\\\\&quot;\\\\&quot;\\\" 3.1h-4v17h4c2.76=\\\"\\\\&quot;\\\\&quot;\\\" 5-2.24=\\\"\\\\&quot;\\\\&quot;\\\" 5-5s-2.24-5-5-5z\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"></path></svg></span></span></span></div></div></div></div></span></span></span></li></div></ul></div></div></div></div></div></li></div><div class=\\\"\\\\&quot;\\\\\\\\&quot;bsmXxe\\\\\\\\&quot;\\\\&quot;\\\" id=\\\"\\\\&quot;\\\\\\\\&quot;wqE3aJmcDI_A4-EPyb6x4AE__76\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;display:\\\\&quot;\\\" contents;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><li class=\\\"\\\\&quot;\\\\\\\\&quot;K3KsMc\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;margin:\\\\&quot;\\\" 0px=\\\"\\\\&quot;\\\\&quot;\\\" 8px;=\\\"\\\\&quot;\\\\&quot;\\\" padding:=\\\"\\\\&quot;\\\\&quot;\\\" 4px;=\\\"\\\\&quot;\\\\&quot;\\\" list-style-position:=\\\"\\\\&quot;\\\\&quot;\\\" inherit;=\\\"\\\\&quot;\\\\&quot;\\\" list-style-image:=\\\"\\\\&quot;\\\\&quot;\\\" list-style-type:=\\\"\\\\&quot;\\\\&quot;\\\" none;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><div class=\\\"\\\\&quot;\\\\\\\\&quot;zMgcWd\\\\&quot;\\\" dskvsb\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\" data-il=\\\"\\\\&quot;\\\\\\\\&quot;\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;padding-bottom:\\\\&quot;\\\" 0px;=\\\"\\\\&quot;\\\\&quot;\\\" padding-top:=\\\"\\\\&quot;\\\\&quot;\\\" border-bottom:=\\\"\\\\&quot;\\\\&quot;\\\" none;=\\\"\\\\&quot;\\\\&quot;\\\" margin-left:=\\\"\\\\&quot;\\\\&quot;\\\" -28px;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><div data-crb-p=\\\"\\\\&quot;\\\\\\\\&quot;\\\\\\\\&quot;\\\\&quot;\\\"><div class=\\\"\\\\&quot;\\\\\\\\&quot;xFTqob\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;flex:\\\\&quot;\\\" 1=\\\"\\\\&quot;\\\\&quot;\\\" 0%;=\\\"\\\\&quot;\\\\&quot;\\\" min-width:=\\\"\\\\&quot;\\\\&quot;\\\" 0px;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><div class=\\\"\\\\&quot;\\\\\\\\&quot;Gur8Ad\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;line-height:\\\\&quot;\\\" 22px;=\\\"\\\\&quot;\\\\&quot;\\\" overflow:=\\\"\\\\&quot;\\\\&quot;\\\" hidden;=\\\"\\\\&quot;\\\\&quot;\\\" padding-bottom:=\\\"\\\\&quot;\\\\&quot;\\\" 4px;=\\\"\\\\&quot;\\\\&quot;\\\" transition:=\\\"\\\\&quot;\\\\&quot;\\\" transform=\\\"\\\\&quot;\\\\&quot;\\\" 200ms=\\\"\\\\&quot;\\\\&quot;\\\" cubic-bezier(0.2,=\\\"\\\\&quot;\\\\&quot;\\\" 0,=\\\"\\\\&quot;\\\\&quot;\\\" 1);\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><span style=\\\"background-color: rgb(255, 255, 255);\\\" rgb(255,=\\\"\\\" 255,=\\\"\\\" 255);\\\\\\\"=\\\"\\\" 255);\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><strong>3.&nbsp;</strong><span data-huuid=\\\"\\\\&quot;\\\\\\\\&quot;2901253832168686377\\\\\\\\&quot;\\\\&quot;\\\"><strong>Konfigurasi Opsi DHCP:</strong></span></span></div><div class=\\\"\\\\&quot;\\\\\\\\&quot;vM0jzc\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;letter-spacing:\\\\&quot;\\\" 0.1px;=\\\"\\\\&quot;\\\\&quot;\\\" line-height:=\\\"\\\\&quot;\\\\&quot;\\\" 22px;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><div class=\\\"\\\\&quot;\\\\\\\\&quot;bsmXxe\\\\\\\\&quot;\\\\&quot;\\\" id=\\\"\\\\&quot;\\\\\\\\&quot;wqE3aJmcDI_A4-EPyb6x4AE__79\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;display:\\\\&quot;\\\" contents;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><ul jscontroller=\\\"\\\\&quot;\\\\\\\\&quot;M2ABbc\\\\\\\\&quot;\\\\&quot;\\\" jsaction=\\\"\\\\&quot;\\\\\\\\&quot;jZtoLb:SaHfyb\\\\\\\\&quot;\\\\&quot;\\\" data-hveid=\\\"\\\\&quot;\\\\\\\\&quot;CNYBEAE\\\\\\\\&quot;\\\\&quot;\\\" data-ved=\\\"\\\\&quot;\\\\\\\\&quot;2ahUKEwiZhfb_rceNAxUP4DgGHUlfDBwQm_YKegUI1gEQAQ\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;margin:\\\\&quot;\\\" 10px=\\\"\\\\&quot;\\\\&quot;\\\" 0px=\\\"\\\\&quot;\\\\&quot;\\\" 20px;=\\\"\\\\&quot;\\\\&quot;\\\" padding:=\\\"\\\\&quot;\\\\&quot;\\\" 24px;=\\\"\\\\&quot;\\\\&quot;\\\" line-height:=\\\"\\\\&quot;\\\\&quot;\\\" 22px;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><div class=\\\"\\\\&quot;\\\\\\\\&quot;bsmXxe\\\\\\\\&quot;\\\\&quot;\\\" id=\\\"\\\\&quot;\\\\\\\\&quot;wqE3aJmcDI_A4-EPyb6x4AE__80\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;display:\\\\&quot;\\\" contents;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><li style=\\\"\\\\&quot;\\\\\\\\&quot;margin:\\\\&quot;\\\" 0px=\\\"\\\\&quot;\\\\&quot;\\\" 8px;=\\\"\\\\&quot;\\\\&quot;\\\" padding:=\\\"\\\\&quot;\\\\&quot;\\\" 4px;=\\\"\\\\&quot;\\\\&quot;\\\" list-style-position:=\\\"\\\\&quot;\\\\&quot;\\\" inherit;=\\\"\\\\&quot;\\\\&quot;\\\" list-style-image:=\\\"\\\\&quot;\\\\&quot;\\\" list-style-type:=\\\"\\\\&quot;\\\\&quot;\\\" disc;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><span data-huuid=\\\"\\\\&quot;\\\\\\\\&quot;2901253832168686288\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"background-color: rgb(255, 255, 255);\\\" rgb(255,=\\\"\\\" 255,=\\\"\\\" 255);\\\\\\\"=\\\"\\\" 255);\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\">Konfigurasi opsi seperti DNS, gateway default, WINS (jika digunakan), dan lainnya.<span jscontroller=\\\"\\\\&quot;\\\\\\\\&quot;JHnpme\\\\\\\\&quot;\\\\&quot;\\\" class=\\\"\\\\&quot;\\\\\\\\&quot;pjBG2e\\\\\\\\&quot;\\\\&quot;\\\" data-cid=\\\"\\\\&quot;\\\\\\\\&quot;4776b1fe-dfa6-4c90-bdc7-03f042bef8eb\\\\\\\\&quot;\\\\&quot;\\\" jsaction=\\\"\\\\&quot;\\\\\\\\&quot;rcuQ6b:npT2md\\\\\\\\&quot;\\\\&quot;\\\"><span class=\\\"\\\\&quot;\\\\\\\\&quot;UV3uM\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;text-wrap-mode:\\\\&quot;\\\" nowrap;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\">&nbsp;<div class=\\\"\\\\&quot;\\\\\\\\&quot;NPrrbc\\\\\\\\&quot;\\\\&quot;\\\" data-cid=\\\"\\\\&quot;\\\\\\\\&quot;4776b1fe-dfa6-4c90-bdc7-03f042bef8eb\\\\\\\\&quot;\\\\&quot;\\\" data-uuids=\\\"\\\\&quot;\\\\\\\\&quot;2901253832168686288\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;margin-right:\\\\&quot;\\\" 6px;=\\\"\\\\&quot;\\\\&quot;\\\" display:=\\\"\\\\&quot;\\\\&quot;\\\" inline-flex;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><div jsname=\\\"\\\\&quot;\\\\\\\\&quot;HtgYJd\\\\\\\\&quot;\\\\&quot;\\\" class=\\\"\\\\&quot;\\\\\\\\&quot;BMebGe\\\\&quot;\\\" btku5b=\\\"\\\\&quot;\\\\&quot;\\\" fcrzyc=\\\"\\\\&quot;\\\\&quot;\\\" lwdv0e=\\\"\\\\&quot;\\\\&quot;\\\" fr7zsc=\\\"\\\\&quot;\\\\&quot;\\\" ojeuxf\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\" aria-label=\\\"\\\\&quot;\\\\\\\\&quot;Lihat\\\\&quot;\\\" link=\\\"\\\\&quot;\\\\&quot;\\\" terkait\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\" role=\\\"\\\\&quot;\\\\\\\\&quot;button\\\\\\\\&quot;\\\\&quot;\\\" tabindex=\\\"\\\\&quot;\\\\\\\\&quot;0\\\\\\\\&quot;\\\\&quot;\\\" jsaction=\\\"\\\\&quot;\\\\\\\\&quot;KjsqPd\\\\\\\\&quot;\\\\&quot;\\\" data-hveid=\\\"\\\\&quot;\\\\\\\\&quot;CP4BEAE\\\\\\\\&quot;\\\\&quot;\\\" data-ved=\\\"\\\\&quot;\\\\\\\\&quot;2ahUKEwiZhfb_rceNAxUP4DgGHUlfDBwQ3fYKegUI_gEQAQ\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;vertical-align:\\\\&quot;\\\" middle;=\\\"\\\\&quot;\\\\&quot;\\\" outline:=\\\"\\\\&quot;\\\\&quot;\\\" 0px;=\\\"\\\\&quot;\\\\&quot;\\\" -webkit-tap-highlight-color:=\\\"\\\\&quot;\\\\&quot;\\\" transparent;=\\\"\\\\&quot;\\\\&quot;\\\" display:=\\\"\\\\&quot;\\\\&quot;\\\" inline-flex=\\\"\\\\&quot;\\\\&quot;\\\" !important;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><div class=\\\"\\\\&quot;\\\\\\\\&quot;niO4u\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;display:\\\\&quot;\\\" flex;=\\\"\\\\&quot;\\\\&quot;\\\" justify-content:=\\\"\\\\&quot;\\\\&quot;\\\" center;=\\\"\\\\&quot;\\\\&quot;\\\" position:=\\\"\\\\&quot;\\\\&quot;\\\" relative;=\\\"\\\\&quot;\\\\&quot;\\\" align-items:=\\\"\\\\&quot;\\\\&quot;\\\" stretch;=\\\"\\\\&quot;\\\\&quot;\\\" width:=\\\"\\\\&quot;\\\\&quot;\\\" 28px;=\\\"\\\\&quot;\\\\&quot;\\\" border-radius:=\\\"\\\\&quot;\\\\&quot;\\\" 9999px;=\\\"\\\\&quot;\\\\&quot;\\\" margin:=\\\"\\\\&quot;\\\\&quot;\\\" 0px=\\\"\\\\&quot;\\\\&quot;\\\" auto;=\\\"\\\\&quot;\\\\&quot;\\\" outline:=\\\"\\\\&quot;\\\\&quot;\\\" transparent=\\\"\\\\&quot;\\\\&quot;\\\" solid=\\\"\\\\&quot;\\\\&quot;\\\" 1px;=\\\"\\\\&quot;\\\\&quot;\\\" outline-offset:=\\\"\\\\&quot;\\\\&quot;\\\" -1px;=\\\"\\\\&quot;\\\\&quot;\\\" height:=\\\"\\\\&quot;\\\\&quot;\\\" 20px;=\\\"\\\\&quot;\\\\&quot;\\\" min-height:=\\\"\\\\&quot;\\\\&quot;\\\" 20px;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><div class=\\\"\\\\&quot;\\\\\\\\&quot;kHtcsd\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;display:\\\\&quot;\\\" flex;=\\\"\\\\&quot;\\\\&quot;\\\" align-items:=\\\"\\\\&quot;\\\\&quot;\\\" center;=\\\"\\\\&quot;\\\\&quot;\\\" justify-content:=\\\"\\\\&quot;\\\\&quot;\\\" width:=\\\"\\\\&quot;\\\\&quot;\\\" 28px;=\\\"\\\\&quot;\\\\&quot;\\\" border-radius:=\\\"\\\\&quot;\\\\&quot;\\\" 9999px;=\\\"\\\\&quot;\\\\&quot;\\\" height:=\\\"\\\\&quot;\\\\&quot;\\\" 20px;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><span class=\\\"\\\\&quot;\\\\\\\\&quot;d3o3Ad\\\\&quot;\\\" gjdc8e=\\\"\\\\&quot;\\\\&quot;\\\" hkv2pe\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;display:\\\\&quot;\\\" flex;=\\\"\\\\&quot;\\\\&quot;\\\" align-items:=\\\"\\\\&quot;\\\\&quot;\\\" center;=\\\"\\\\&quot;\\\\&quot;\\\" margin:=\\\"\\\\&quot;\\\\&quot;\\\" 0px;=\\\"\\\\&quot;\\\\&quot;\\\" background-image:=\\\"\\\\&quot;\\\\&quot;\\\" unset=\\\"\\\\&quot;\\\\&quot;\\\" !important;=\\\"\\\\&quot;\\\\&quot;\\\" background-position:=\\\"\\\\&quot;\\\\&quot;\\\" background-size:=\\\"\\\\&quot;\\\\&quot;\\\" background-repeat:=\\\"\\\\&quot;\\\\&quot;\\\" background-attachment:=\\\"\\\\&quot;\\\\&quot;\\\" background-origin:=\\\"\\\\&quot;\\\\&quot;\\\" background-clip:=\\\"\\\\&quot;\\\\&quot;\\\" !important;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><span class=\\\"\\\\&quot;\\\\\\\\&quot;iPjmzb\\\\&quot;\\\" sorfoc=\\\"\\\\&quot;\\\\&quot;\\\" gngsdf\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;display:\\\\&quot;\\\" flex;=\\\"\\\\&quot;\\\\&quot;\\\" height:=\\\"\\\\&quot;\\\\&quot;\\\" unset;=\\\"\\\\&quot;\\\\&quot;\\\" rotate:=\\\"\\\\&quot;\\\\&quot;\\\" 135deg;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><span class=\\\"\\\\&quot;\\\\\\\\&quot;z1asCe\\\\&quot;\\\" sb7k4e\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;display:\\\\&quot;\\\" inline-block;=\\\"\\\\&quot;\\\\&quot;\\\" fill:=\\\"\\\\&quot;\\\\&quot;\\\" currentcolor;=\\\"\\\\&quot;\\\\&quot;\\\" height:=\\\"\\\\&quot;\\\\&quot;\\\" 18px;=\\\"\\\\&quot;\\\\&quot;\\\" line-height:=\\\"\\\\&quot;\\\\&quot;\\\" position:=\\\"\\\\&quot;\\\\&quot;\\\" relative;=\\\"\\\\&quot;\\\\&quot;\\\" width:=\\\"\\\\&quot;\\\\&quot;\\\" 18px;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><svg focusable=\\\"\\\\&quot;\\\\\\\\&quot;false\\\\\\\\&quot;\\\\&quot;\\\" xmlns=\\\"\\\\&quot;\\\\\\\\&quot;http://www.w3.org/2000/svg\\\\\\\\&quot;\\\\&quot;\\\" viewBox=\\\"\\\\&quot;\\\\\\\\&quot;0\\\\&quot;\\\" 0=\\\"\\\\&quot;\\\\&quot;\\\" 24=\\\"\\\\&quot;\\\\&quot;\\\" 24\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><path d=\\\"\\\\&quot;\\\\\\\\&quot;M3.9\\\\&quot;\\\" 12c0-1.71=\\\"\\\\&quot;\\\\&quot;\\\" 1.39-3.1=\\\"\\\\&quot;\\\\&quot;\\\" 3.1-3.1h4v7h7c-2.76=\\\"\\\\&quot;\\\\&quot;\\\" 0-5=\\\"\\\\&quot;\\\\&quot;\\\" 2.24-5=\\\"\\\\&quot;\\\\&quot;\\\" 5s2.24=\\\"\\\\&quot;\\\\&quot;\\\" 5=\\\"\\\\&quot;\\\\&quot;\\\" 5h4v-1.9h7c-1.71=\\\"\\\\&quot;\\\\&quot;\\\" 0-3.1-1.39-3.1-3.1zm8=\\\"\\\\&quot;\\\\&quot;\\\" 13h8v-2h8v2zm9-6h-4v1.9h4c1.71=\\\"\\\\&quot;\\\\&quot;\\\" 0=\\\"\\\\&quot;\\\\&quot;\\\" 3.1=\\\"\\\\&quot;\\\\&quot;\\\" 1.39=\\\"\\\\&quot;\\\\&quot;\\\" 3.1s-1.39=\\\"\\\\&quot;\\\\&quot;\\\" 3.1-3.1=\\\"\\\\&quot;\\\\&quot;\\\" 3.1h-4v17h4c2.76=\\\"\\\\&quot;\\\\&quot;\\\" 5-2.24=\\\"\\\\&quot;\\\\&quot;\\\" 5-5s-2.24-5-5-5z\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"></path></svg></span></span></span></div></div></div></div></span></span></span></li></div><div class=\\\"\\\\&quot;\\\\\\\\&quot;bsmXxe\\\\\\\\&quot;\\\\&quot;\\\" id=\\\"\\\\&quot;\\\\\\\\&quot;wqE3aJmcDI_A4-EPyb6x4AE__82\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;display:\\\\&quot;\\\" contents;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><li style=\\\"\\\\&quot;\\\\\\\\&quot;margin:\\\\&quot;\\\" 0px;=\\\"\\\\&quot;\\\\&quot;\\\" padding:=\\\"\\\\&quot;\\\\&quot;\\\" 0px=\\\"\\\\&quot;\\\\&quot;\\\" 4px;=\\\"\\\\&quot;\\\\&quot;\\\" list-style-position:=\\\"\\\\&quot;\\\\&quot;\\\" inherit;=\\\"\\\\&quot;\\\\&quot;\\\" list-style-image:=\\\"\\\\&quot;\\\\&quot;\\\" list-style-type:=\\\"\\\\&quot;\\\\&quot;\\\" disc;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><span data-huuid=\\\"\\\\&quot;\\\\\\\\&quot;2901253832168687594\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"background-color: rgb(255, 255, 255);\\\" rgb(255,=\\\"\\\" 255,=\\\"\\\" 255);\\\\\\\"=\\\"\\\" 255);\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\">Opsi-opsi ini akan diteruskan ke klien ketika mereka meminta alamat IP dari server DHCP.<span jscontroller=\\\"\\\\&quot;\\\\\\\\&quot;JHnpme\\\\\\\\&quot;\\\\&quot;\\\" class=\\\"\\\\&quot;\\\\\\\\&quot;pjBG2e\\\\\\\\&quot;\\\\&quot;\\\" data-cid=\\\"\\\\&quot;\\\\\\\\&quot;71d8e603-4bc9-4583-b897-c79bb7491bdc\\\\\\\\&quot;\\\\&quot;\\\" jsaction=\\\"\\\\&quot;\\\\\\\\&quot;rcuQ6b:npT2md\\\\\\\\&quot;\\\\&quot;\\\"><span class=\\\"\\\\&quot;\\\\\\\\&quot;UV3uM\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;text-wrap-mode:\\\\&quot;\\\" nowrap;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\">&nbsp;<div class=\\\"\\\\&quot;\\\\\\\\&quot;NPrrbc\\\\\\\\&quot;\\\\&quot;\\\" data-cid=\\\"\\\\&quot;\\\\\\\\&quot;71d8e603-4bc9-4583-b897-c79bb7491bdc\\\\\\\\&quot;\\\\&quot;\\\" data-uuids=\\\"\\\\&quot;\\\\\\\\&quot;2901253832168687594\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;margin-right:\\\\&quot;\\\" 6px;=\\\"\\\\&quot;\\\\&quot;\\\" display:=\\\"\\\\&quot;\\\\&quot;\\\" inline-flex;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><div jsname=\\\"\\\\&quot;\\\\\\\\&quot;HtgYJd\\\\\\\\&quot;\\\\&quot;\\\" class=\\\"\\\\&quot;\\\\\\\\&quot;BMebGe\\\\&quot;\\\" btku5b=\\\"\\\\&quot;\\\\&quot;\\\" fcrzyc=\\\"\\\\&quot;\\\\&quot;\\\" lwdv0e=\\\"\\\\&quot;\\\\&quot;\\\" fr7zsc=\\\"\\\\&quot;\\\\&quot;\\\" ojeuxf\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\" aria-label=\\\"\\\\&quot;\\\\\\\\&quot;Lihat\\\\&quot;\\\" link=\\\"\\\\&quot;\\\\&quot;\\\" terkait\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\" role=\\\"\\\\&quot;\\\\\\\\&quot;button\\\\\\\\&quot;\\\\&quot;\\\" tabindex=\\\"\\\\&quot;\\\\\\\\&quot;0\\\\\\\\&quot;\\\\&quot;\\\" jsaction=\\\"\\\\&quot;\\\\\\\\&quot;KjsqPd\\\\\\\\&quot;\\\\&quot;\\\" data-hveid=\\\"\\\\&quot;\\\\\\\\&quot;CPoBEAE\\\\\\\\&quot;\\\\&quot;\\\" data-ved=\\\"\\\\&quot;\\\\\\\\&quot;2ahUKEwiZhfb_rceNAxUP4DgGHUlfDBwQ3fYKegUI-gEQAQ\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;vertical-align:\\\\&quot;\\\" middle;=\\\"\\\\&quot;\\\\&quot;\\\" outline:=\\\"\\\\&quot;\\\\&quot;\\\" 0px;=\\\"\\\\&quot;\\\\&quot;\\\" -webkit-tap-highlight-color:=\\\"\\\\&quot;\\\\&quot;\\\" transparent;=\\\"\\\\&quot;\\\\&quot;\\\" display:=\\\"\\\\&quot;\\\\&quot;\\\" inline-flex=\\\"\\\\&quot;\\\\&quot;\\\" !important;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><div class=\\\"\\\\&quot;\\\\\\\\&quot;niO4u\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;display:\\\\&quot;\\\" flex;=\\\"\\\\&quot;\\\\&quot;\\\" justify-content:=\\\"\\\\&quot;\\\\&quot;\\\" center;=\\\"\\\\&quot;\\\\&quot;\\\" position:=\\\"\\\\&quot;\\\\&quot;\\\" relative;=\\\"\\\\&quot;\\\\&quot;\\\" align-items:=\\\"\\\\&quot;\\\\&quot;\\\" stretch;=\\\"\\\\&quot;\\\\&quot;\\\" width:=\\\"\\\\&quot;\\\\&quot;\\\" 28px;=\\\"\\\\&quot;\\\\&quot;\\\" border-radius:=\\\"\\\\&quot;\\\\&quot;\\\" 9999px;=\\\"\\\\&quot;\\\\&quot;\\\" margin:=\\\"\\\\&quot;\\\\&quot;\\\" 0px=\\\"\\\\&quot;\\\\&quot;\\\" auto;=\\\"\\\\&quot;\\\\&quot;\\\" outline:=\\\"\\\\&quot;\\\\&quot;\\\" transparent=\\\"\\\\&quot;\\\\&quot;\\\" solid=\\\"\\\\&quot;\\\\&quot;\\\" 1px;=\\\"\\\\&quot;\\\\&quot;\\\" outline-offset:=\\\"\\\\&quot;\\\\&quot;\\\" -1px;=\\\"\\\\&quot;\\\\&quot;\\\" height:=\\\"\\\\&quot;\\\\&quot;\\\" 20px;=\\\"\\\\&quot;\\\\&quot;\\\" min-height:=\\\"\\\\&quot;\\\\&quot;\\\" 20px;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><div class=\\\"\\\\&quot;\\\\\\\\&quot;kHtcsd\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;display:\\\\&quot;\\\" flex;=\\\"\\\\&quot;\\\\&quot;\\\" align-items:=\\\"\\\\&quot;\\\\&quot;\\\" center;=\\\"\\\\&quot;\\\\&quot;\\\" justify-content:=\\\"\\\\&quot;\\\\&quot;\\\" width:=\\\"\\\\&quot;\\\\&quot;\\\" 28px;=\\\"\\\\&quot;\\\\&quot;\\\" border-radius:=\\\"\\\\&quot;\\\\&quot;\\\" 9999px;=\\\"\\\\&quot;\\\\&quot;\\\" height:=\\\"\\\\&quot;\\\\&quot;\\\" 20px;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><span class=\\\"\\\\&quot;\\\\\\\\&quot;d3o3Ad\\\\&quot;\\\" gjdc8e=\\\"\\\\&quot;\\\\&quot;\\\" hkv2pe\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;display:\\\\&quot;\\\" flex;=\\\"\\\\&quot;\\\\&quot;\\\" align-items:=\\\"\\\\&quot;\\\\&quot;\\\" center;=\\\"\\\\&quot;\\\\&quot;\\\" margin:=\\\"\\\\&quot;\\\\&quot;\\\" 0px;=\\\"\\\\&quot;\\\\&quot;\\\" background-image:=\\\"\\\\&quot;\\\\&quot;\\\" unset=\\\"\\\\&quot;\\\\&quot;\\\" !important;=\\\"\\\\&quot;\\\\&quot;\\\" background-position:=\\\"\\\\&quot;\\\\&quot;\\\" background-size:=\\\"\\\\&quot;\\\\&quot;\\\" background-repeat:=\\\"\\\\&quot;\\\\&quot;\\\" background-attachment:=\\\"\\\\&quot;\\\\&quot;\\\" background-origin:=\\\"\\\\&quot;\\\\&quot;\\\" background-clip:=\\\"\\\\&quot;\\\\&quot;\\\" !important;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><span class=\\\"\\\\&quot;\\\\\\\\&quot;iPjmzb\\\\&quot;\\\" sorfoc=\\\"\\\\&quot;\\\\&quot;\\\" gngsdf\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;display:\\\\&quot;\\\" flex;=\\\"\\\\&quot;\\\\&quot;\\\" height:=\\\"\\\\&quot;\\\\&quot;\\\" unset;=\\\"\\\\&quot;\\\\&quot;\\\" rotate:=\\\"\\\\&quot;\\\\&quot;\\\" 135deg;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><span class=\\\"\\\\&quot;\\\\\\\\&quot;z1asCe\\\\&quot;\\\" sb7k4e\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;display:\\\\&quot;\\\" inline-block;=\\\"\\\\&quot;\\\\&quot;\\\" fill:=\\\"\\\\&quot;\\\\&quot;\\\" currentcolor;=\\\"\\\\&quot;\\\\&quot;\\\" height:=\\\"\\\\&quot;\\\\&quot;\\\" 18px;=\\\"\\\\&quot;\\\\&quot;\\\" line-height:=\\\"\\\\&quot;\\\\&quot;\\\" position:=\\\"\\\\&quot;\\\\&quot;\\\" relative;=\\\"\\\\&quot;\\\\&quot;\\\" width:=\\\"\\\\&quot;\\\\&quot;\\\" 18px;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><svg focusable=\\\"\\\\&quot;\\\\\\\\&quot;false\\\\\\\\&quot;\\\\&quot;\\\" xmlns=\\\"\\\\&quot;\\\\\\\\&quot;http://www.w3.org/2000/svg\\\\\\\\&quot;\\\\&quot;\\\" viewBox=\\\"\\\\&quot;\\\\\\\\&quot;0\\\\&quot;\\\" 0=\\\"\\\\&quot;\\\\&quot;\\\" 24=\\\"\\\\&quot;\\\\&quot;\\\" 24\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><path d=\\\"\\\\&quot;\\\\\\\\&quot;M3.9\\\\&quot;\\\" 12c0-1.71=\\\"\\\\&quot;\\\\&quot;\\\" 1.39-3.1=\\\"\\\\&quot;\\\\&quot;\\\" 3.1-3.1h4v7h7c-2.76=\\\"\\\\&quot;\\\\&quot;\\\" 0-5=\\\"\\\\&quot;\\\\&quot;\\\" 2.24-5=\\\"\\\\&quot;\\\\&quot;\\\" 5s2.24=\\\"\\\\&quot;\\\\&quot;\\\" 5=\\\"\\\\&quot;\\\\&quot;\\\" 5h4v-1.9h7c-1.71=\\\"\\\\&quot;\\\\&quot;\\\" 0-3.1-1.39-3.1-3.1zm8=\\\"\\\\&quot;\\\\&quot;\\\" 13h8v-2h8v2zm9-6h-4v1.9h4c1.71=\\\"\\\\&quot;\\\\&quot;\\\" 0=\\\"\\\\&quot;\\\\&quot;\\\" 3.1=\\\"\\\\&quot;\\\\&quot;\\\" 1.39=\\\"\\\\&quot;\\\\&quot;\\\" 3.1s-1.39=\\\"\\\\&quot;\\\\&quot;\\\" 3.1-3.1=\\\"\\\\&quot;\\\\&quot;\\\" 3.1h-4v17h4c2.76=\\\"\\\\&quot;\\\\&quot;\\\" 5-2.24=\\\"\\\\&quot;\\\\&quot;\\\" 5-5s-2.24-5-5-5z\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"></path></svg></span></span></span></div></div></div></div></span></span></span></li></div></ul></div></div></div></div></div></li></div><div class=\\\"\\\\&quot;\\\\\\\\&quot;bsmXxe\\\\\\\\&quot;\\\\&quot;\\\" id=\\\"\\\\&quot;\\\\\\\\&quot;wqE3aJmcDI_A4-EPyb6x4AE__84\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;display:\\\\&quot;\\\" contents;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><li class=\\\"\\\\&quot;\\\\\\\\&quot;K3KsMc\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;margin:\\\\&quot;\\\" 0px=\\\"\\\\&quot;\\\\&quot;\\\" 8px;=\\\"\\\\&quot;\\\\&quot;\\\" padding:=\\\"\\\\&quot;\\\\&quot;\\\" 4px;=\\\"\\\\&quot;\\\\&quot;\\\" list-style-position:=\\\"\\\\&quot;\\\\&quot;\\\" inherit;=\\\"\\\\&quot;\\\\&quot;\\\" list-style-image:=\\\"\\\\&quot;\\\\&quot;\\\" list-style-type:=\\\"\\\\&quot;\\\\&quot;\\\" none;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><div class=\\\"\\\\&quot;\\\\\\\\&quot;zMgcWd\\\\&quot;\\\" dskvsb\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\" data-il=\\\"\\\\&quot;\\\\\\\\&quot;\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;padding-bottom:\\\\&quot;\\\" 0px;=\\\"\\\\&quot;\\\\&quot;\\\" padding-top:=\\\"\\\\&quot;\\\\&quot;\\\" border-bottom:=\\\"\\\\&quot;\\\\&quot;\\\" none;=\\\"\\\\&quot;\\\\&quot;\\\" margin-left:=\\\"\\\\&quot;\\\\&quot;\\\" -28px;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><div data-crb-p=\\\"\\\\&quot;\\\\\\\\&quot;\\\\\\\\&quot;\\\\&quot;\\\"><div class=\\\"\\\\&quot;\\\\\\\\&quot;xFTqob\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;flex:\\\\&quot;\\\" 1=\\\"\\\\&quot;\\\\&quot;\\\" 0%;=\\\"\\\\&quot;\\\\&quot;\\\" min-width:=\\\"\\\\&quot;\\\\&quot;\\\" 0px;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><div class=\\\"\\\\&quot;\\\\\\\\&quot;Gur8Ad\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;line-height:\\\\&quot;\\\" 22px;=\\\"\\\\&quot;\\\\&quot;\\\" overflow:=\\\"\\\\&quot;\\\\&quot;\\\" hidden;=\\\"\\\\&quot;\\\\&quot;\\\" padding-bottom:=\\\"\\\\&quot;\\\\&quot;\\\" 4px;=\\\"\\\\&quot;\\\\&quot;\\\" transition:=\\\"\\\\&quot;\\\\&quot;\\\" transform=\\\"\\\\&quot;\\\\&quot;\\\" 200ms=\\\"\\\\&quot;\\\\&quot;\\\" cubic-bezier(0.2,=\\\"\\\\&quot;\\\\&quot;\\\" 0,=\\\"\\\\&quot;\\\\&quot;\\\" 1);\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><span style=\\\"background-color: rgb(255, 255, 255);\\\" rgb(255,=\\\"\\\" 255,=\\\"\\\" 255);\\\\\\\"=\\\"\\\" 255);\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><strong>4.&nbsp;</strong><span data-huuid=\\\"\\\\&quot;\\\\\\\\&quot;2901253832168688900\\\\\\\\&quot;\\\\&quot;\\\"><strong>Mulai Layanan DHCP:</strong></span></span></div><div class=\\\"\\\\&quot;\\\\\\\\&quot;vM0jzc\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;letter-spacing:\\\\&quot;\\\" 0.1px;=\\\"\\\\&quot;\\\\&quot;\\\" line-height:=\\\"\\\\&quot;\\\\&quot;\\\" 22px;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><div class=\\\"\\\\&quot;\\\\\\\\&quot;bsmXxe\\\\\\\\&quot;\\\\&quot;\\\" id=\\\"\\\\&quot;\\\\\\\\&quot;wqE3aJmcDI_A4-EPyb6x4AE__86\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;display:\\\\&quot;\\\" contents;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><ul jscontroller=\\\"\\\\&quot;\\\\\\\\&quot;M2ABbc\\\\\\\\&quot;\\\\&quot;\\\" jsaction=\\\"\\\\&quot;\\\\\\\\&quot;jZtoLb:SaHfyb\\\\\\\\&quot;\\\\&quot;\\\" data-hveid=\\\"\\\\&quot;\\\\\\\\&quot;COcBEAE\\\\\\\\&quot;\\\\&quot;\\\" data-ved=\\\"\\\\&quot;\\\\\\\\&quot;2ahUKEwiZhfb_rceNAxUP4DgGHUlfDBwQm_YKegUI5wEQAQ\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;margin:\\\\&quot;\\\" 10px=\\\"\\\\&quot;\\\\&quot;\\\" 0px=\\\"\\\\&quot;\\\\&quot;\\\" 20px;=\\\"\\\\&quot;\\\\&quot;\\\" padding:=\\\"\\\\&quot;\\\\&quot;\\\" 24px;=\\\"\\\\&quot;\\\\&quot;\\\" line-height:=\\\"\\\\&quot;\\\\&quot;\\\" 22px;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><div class=\\\"\\\\&quot;\\\\\\\\&quot;bsmXxe\\\\\\\\&quot;\\\\&quot;\\\" id=\\\"\\\\&quot;\\\\\\\\&quot;wqE3aJmcDI_A4-EPyb6x4AE__87\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;display:\\\\&quot;\\\" contents;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><li style=\\\"\\\\&quot;\\\\\\\\&quot;margin:\\\\&quot;\\\" 0px;=\\\"\\\\&quot;\\\\&quot;\\\" padding:=\\\"\\\\&quot;\\\\&quot;\\\" 0px=\\\"\\\\&quot;\\\\&quot;\\\" 4px;=\\\"\\\\&quot;\\\\&quot;\\\" list-style-position:=\\\"\\\\&quot;\\\\&quot;\\\" inherit;=\\\"\\\\&quot;\\\\&quot;\\\" list-style-image:=\\\"\\\\&quot;\\\\&quot;\\\" list-style-type:=\\\"\\\\&quot;\\\\&quot;\\\" disc;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><span data-huuid=\\\"\\\\&quot;\\\\\\\\&quot;2901253832168688811\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"background-color: rgb(255, 255, 255);\\\" rgb(255,=\\\"\\\" 255,=\\\"\\\" 255);\\\\\\\"=\\\"\\\" 255);\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\">Setelah konfigurasi selesai, pastikan layanan DHCP berjalan dengan benar.<span jscontroller=\\\"\\\\&quot;\\\\\\\\&quot;JHnpme\\\\\\\\&quot;\\\\&quot;\\\" class=\\\"\\\\&quot;\\\\\\\\&quot;pjBG2e\\\\\\\\&quot;\\\\&quot;\\\" data-cid=\\\"\\\\&quot;\\\\\\\\&quot;a375f2a8-6ca0-4bbf-9ec3-afb4b73d4e3a\\\\\\\\&quot;\\\\&quot;\\\" jsaction=\\\"\\\\&quot;\\\\\\\\&quot;rcuQ6b:npT2md\\\\\\\\&quot;\\\\&quot;\\\"><span class=\\\"\\\\&quot;\\\\\\\\&quot;UV3uM\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;text-wrap-mode:\\\\&quot;\\\" nowrap;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\">&nbsp;<div class=\\\"\\\\&quot;\\\\\\\\&quot;NPrrbc\\\\\\\\&quot;\\\\&quot;\\\" data-cid=\\\"\\\\&quot;\\\\\\\\&quot;a375f2a8-6ca0-4bbf-9ec3-afb4b73d4e3a\\\\\\\\&quot;\\\\&quot;\\\" data-uuids=\\\"\\\\&quot;\\\\\\\\&quot;2901253832168688811\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;margin-right:\\\\&quot;\\\" 6px;=\\\"\\\\&quot;\\\\&quot;\\\" display:=\\\"\\\\&quot;\\\\&quot;\\\" inline-flex;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><div jsname=\\\"\\\\&quot;\\\\\\\\&quot;HtgYJd\\\\\\\\&quot;\\\\&quot;\\\" class=\\\"\\\\&quot;\\\\\\\\&quot;BMebGe\\\\&quot;\\\" btku5b=\\\"\\\\&quot;\\\\&quot;\\\" fcrzyc=\\\"\\\\&quot;\\\\&quot;\\\" lwdv0e=\\\"\\\\&quot;\\\\&quot;\\\" fr7zsc=\\\"\\\\&quot;\\\\&quot;\\\" ojeuxf\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\" aria-label=\\\"\\\\&quot;\\\\\\\\&quot;Lihat\\\\&quot;\\\" link=\\\"\\\\&quot;\\\\&quot;\\\" terkait\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\" role=\\\"\\\\&quot;\\\\\\\\&quot;button\\\\\\\\&quot;\\\\&quot;\\\" tabindex=\\\"\\\\&quot;\\\\\\\\&quot;0\\\\\\\\&quot;\\\\&quot;\\\" jsaction=\\\"\\\\&quot;\\\\\\\\&quot;KjsqPd\\\\\\\\&quot;\\\\&quot;\\\" data-hveid=\\\"\\\\&quot;\\\\\\\\&quot;CIACEAE\\\\\\\\&quot;\\\\&quot;\\\" data-ved=\\\"\\\\&quot;\\\\\\\\&quot;2ahUKEwiZhfb_rceNAxUP4DgGHUlfDBwQ3fYKegUIgAIQAQ\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;vertical-align:\\\\&quot;\\\" middle;=\\\"\\\\&quot;\\\\&quot;\\\" outline:=\\\"\\\\&quot;\\\\&quot;\\\" 0px;=\\\"\\\\&quot;\\\\&quot;\\\" -webkit-tap-highlight-color:=\\\"\\\\&quot;\\\\&quot;\\\" transparent;=\\\"\\\\&quot;\\\\&quot;\\\" display:=\\\"\\\\&quot;\\\\&quot;\\\" inline-flex=\\\"\\\\&quot;\\\\&quot;\\\" !important;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><div class=\\\"\\\\&quot;\\\\\\\\&quot;niO4u\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;display:\\\\&quot;\\\" flex;=\\\"\\\\&quot;\\\\&quot;\\\" justify-content:=\\\"\\\\&quot;\\\\&quot;\\\" center;=\\\"\\\\&quot;\\\\&quot;\\\" position:=\\\"\\\\&quot;\\\\&quot;\\\" relative;=\\\"\\\\&quot;\\\\&quot;\\\" align-items:=\\\"\\\\&quot;\\\\&quot;\\\" stretch;=\\\"\\\\&quot;\\\\&quot;\\\" width:=\\\"\\\\&quot;\\\\&quot;\\\" 28px;=\\\"\\\\&quot;\\\\&quot;\\\" border-radius:=\\\"\\\\&quot;\\\\&quot;\\\" 9999px;=\\\"\\\\&quot;\\\\&quot;\\\" margin:=\\\"\\\\&quot;\\\\&quot;\\\" 0px=\\\"\\\\&quot;\\\\&quot;\\\" auto;=\\\"\\\\&quot;\\\\&quot;\\\" outline:=\\\"\\\\&quot;\\\\&quot;\\\" transparent=\\\"\\\\&quot;\\\\&quot;\\\" solid=\\\"\\\\&quot;\\\\&quot;\\\" 1px;=\\\"\\\\&quot;\\\\&quot;\\\" outline-offset:=\\\"\\\\&quot;\\\\&quot;\\\" -1px;=\\\"\\\\&quot;\\\\&quot;\\\" height:=\\\"\\\\&quot;\\\\&quot;\\\" 20px;=\\\"\\\\&quot;\\\\&quot;\\\" min-height:=\\\"\\\\&quot;\\\\&quot;\\\" 20px;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><div class=\\\"\\\\&quot;\\\\\\\\&quot;kHtcsd\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;display:\\\\&quot;\\\" flex;=\\\"\\\\&quot;\\\\&quot;\\\" align-items:=\\\"\\\\&quot;\\\\&quot;\\\" center;=\\\"\\\\&quot;\\\\&quot;\\\" justify-content:=\\\"\\\\&quot;\\\\&quot;\\\" width:=\\\"\\\\&quot;\\\\&quot;\\\" 28px;=\\\"\\\\&quot;\\\\&quot;\\\" border-radius:=\\\"\\\\&quot;\\\\&quot;\\\" 9999px;=\\\"\\\\&quot;\\\\&quot;\\\" height:=\\\"\\\\&quot;\\\\&quot;\\\" 20px;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><span class=\\\"\\\\&quot;\\\\\\\\&quot;d3o3Ad\\\\&quot;\\\" gjdc8e=\\\"\\\\&quot;\\\\&quot;\\\" hkv2pe\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;display:\\\\&quot;\\\" flex;=\\\"\\\\&quot;\\\\&quot;\\\" align-items:=\\\"\\\\&quot;\\\\&quot;\\\" center;=\\\"\\\\&quot;\\\\&quot;\\\" margin:=\\\"\\\\&quot;\\\\&quot;\\\" 0px;=\\\"\\\\&quot;\\\\&quot;\\\" background-image:=\\\"\\\\&quot;\\\\&quot;\\\" unset=\\\"\\\\&quot;\\\\&quot;\\\" !important;=\\\"\\\\&quot;\\\\&quot;\\\" background-position:=\\\"\\\\&quot;\\\\&quot;\\\" background-size:=\\\"\\\\&quot;\\\\&quot;\\\" background-repeat:=\\\"\\\\&quot;\\\\&quot;\\\" background-attachment:=\\\"\\\\&quot;\\\\&quot;\\\" background-origin:=\\\"\\\\&quot;\\\\&quot;\\\" background-clip:=\\\"\\\\&quot;\\\\&quot;\\\" !important;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><span class=\\\"\\\\&quot;\\\\\\\\&quot;iPjmzb\\\\&quot;\\\" sorfoc=\\\"\\\\&quot;\\\\&quot;\\\" gngsdf\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;display:\\\\&quot;\\\" flex;=\\\"\\\\&quot;\\\\&quot;\\\" height:=\\\"\\\\&quot;\\\\&quot;\\\" unset;=\\\"\\\\&quot;\\\\&quot;\\\" rotate:=\\\"\\\\&quot;\\\\&quot;\\\" 135deg;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><span class=\\\"\\\\&quot;\\\\\\\\&quot;z1asCe\\\\&quot;\\\" sb7k4e\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quot;\\\\\\\\&quot;display:\\\\&quot;\\\" inline-block;=\\\"\\\\&quot;\\\\&quot;\\\" fill:=\\\"\\\\&quot;\\\\&quot;\\\" currentcolor;=\\\"\\\\&quot;\\\\&quot;\\\" height:=\\\"\\\\&quot;\\\\&quot;\\\" 18px;=\\\"\\\\&quot;\\\\&quot;\\\" line-height:=\\\"\\\\&quot;\\\\&quot;\\\" position:=\\\"\\\\&quot;\\\\&quot;\\\" relative;=\\\"\\\\&quot;\\\\&quot;\\\" width:=\\\"\\\\&quot;\\\\&quot;\\\" 18px;\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><svg focusable=\\\"\\\\&quot;\\\\\\\\&quot;false\\\\\\\\&quot;\\\\&quot;\\\" xmlns=\\\"\\\\&quot;\\\\\\\\&quot;http://www.w3.org/2000/svg\\\\\\\\&quot;\\\\&quot;\\\" viewBox=\\\"\\\\&quot;\\\\\\\\&quot;0\\\\&quot;\\\" 0=\\\"\\\\&quot;\\\\&quot;\\\" 24=\\\"\\\\&quot;\\\\&quot;\\\" 24\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"><path d=\\\"\\\\&quot;\\\\\\\\&quot;M3.9\\\\&quot;\\\" 12c0-1.71=\\\"\\\\&quot;\\\\&quot;\\\" 1.39-3.1=\\\"\\\\&quot;\\\\&quot;\\\" 3.1-3.1h4v7h7c-2.76=\\\"\\\\&quot;\\\\&quot;\\\" 0-5=\\\"\\\\&quot;\\\\&quot;\\\" 2.24-5=\\\"\\\\&quot;\\\\&quot;\\\" 5s2.24=\\\"\\\\&quot;\\\\&quot;\\\" 5=\\\"\\\\&quot;\\\\&quot;\\\" 5h4v-1.9h7c-1.71=\\\"\\\\&quot;\\\\&quot;\\\" 0-3.1-1.39-3.1-3.1zm8=\\\"\\\\&quot;\\\\&quot;\\\" 13h8v-2h8v2zm9-6h-4v1.9h4c1.71=\\\"\\\\&quot;\\\\&quot;\\\" 0=\\\"\\\\&quot;\\\\&quot;\\\" 3.1=\\\"\\\\&quot;\\\\&quot;\\\" 1.39=\\\"\\\\&quot;\\\\&quot;\\\" 3.1s-1.39=\\\"\\\\&quot;\\\\&quot;\\\" 3.1-3.1=\\\"\\\\&quot;\\\\&quot;\\\" 3.1h-4v17h4c2.76=\\\"\\\\&quot;\\\\&quot;\\\" 5-2.24=\\\"\\\\&quot;\\\\&quot;\\\" 5-5s-2.24-5-5-5z\\\\\\\\\\\\\\\"=\\\"\\\\&quot;\\\\&quot;\\\"></path></svg></span></span></span></div></div></div></div></span></span></span></li></div></ul></div></div></div></div></div></li></div><div class=\\\"\\\\&quot;\\\\\\\\&quot;bsmXxe\\\\\\\\&quot;\\\\&quot;\\\" id=\\\"\\\\&quot;\\\\\\\\&quot;wqE3aJmcDI_A4-EPyb6x4AE__89\\\\\\\\&quot;\\\\&quot;\\\" style=\\\"\\\\&quo', NULL, 'text', 12, '2025-05-28 23:56:25', '2025-05-29 07:05:13');
INSERT INTO `materials` (`id`, `class_id`, `category_id`, `title`, `content`, `file_url`, `material_type`, `uploaded_by`, `uploaded_at`, `updated_at`) VALUES
(10, 3, 2, 'DNS Server', '<h2><span style=\\\"background-color: rgb(255, 255, 255);\\\">Tutorial Dns Srver</span></h2>', NULL, 'text', 12, '2025-06-01 05:28:45', '2025-06-01 12:28:45');

-- --------------------------------------------------------

--
-- Table structure for table `redemptions`
--

CREATE TABLE `redemptions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `quantity` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `skills`
--

CREATE TABLE `skills` (
  `id` int(11) NOT NULL,
  `type` enum('soft','hard') NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `skills`
--

INSERT INTO `skills` (`id`, `type`, `name`) VALUES
(1, 'soft', 'Communication Skill'),
(2, 'soft', 'Problem Solving'),
(3, 'soft', 'Creative and Innovative'),
(4, 'soft', 'Time Management'),
(5, 'soft', 'Teamwork'),
(6, 'soft', 'Critical Thinking'),
(7, 'soft', 'Leadership'),
(8, 'hard', 'Cyber Security'),
(9, 'hard', 'IT Network System Administration'),
(10, 'hard', 'Information Network Cabling'),
(11, 'hard', 'Microcontroller Arduino'),
(12, 'hard', 'Basic Programming Skill'),
(13, 'hard', 'Basic Computer & Network');

-- --------------------------------------------------------

--
-- Table structure for table `store_items`
--

CREATE TABLE `store_items` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `store_items`
--

INSERT INTO `store_items` (`id`, `name`, `description`, `image_url`, `stock`, `created_at`) VALUES
(8, 'eror', '', 'Screenshot 2025-05-23 103146.png', 98, '2025-06-10 04:56:12'),
(10, 'mouse', '', 'Screenshot 2025-05-16 183253.png', 99, '2025-06-10 06:38:09');

-- --------------------------------------------------------

--
-- Table structure for table `store_item_badge_rules`
--

CREATE TABLE `store_item_badge_rules` (
  `id` int(11) NOT NULL,
  `store_item_id` int(11) NOT NULL,
  `badge_count_required` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `badge_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `store_item_badge_rules`
--

INSERT INTO `store_item_badge_rules` (`id`, `store_item_id`, `badge_count_required`, `created_at`, `badge_id`) VALUES
(6, 8, 1, '2025-06-10 05:03:00', 1),
(7, 8, 1, '2025-06-10 05:03:00', 2),
(9, 10, 1, '2025-06-10 06:38:09', 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('admin','teknisi','siswa','developer') NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `kelas` enum('X','XI','XII') DEFAULT NULL,
  `jurusan` enum('TEKNIK KOMPUTER DAN JARINGAN','TEKNIK PENGELASAN','TEKNIK PEMESINAN','TEKNK INSTALASI TENAGA LISTRIK','TEKNIK KENDARAAN RINGAN','BROADCASTING DAN PERFILEMAN','DESAIN KOMUNIKASI VISUAL','PEMASARAN') DEFAULT NULL,
  `create_at` datetime NOT NULL DEFAULT current_timestamp(),
  `session_id` varchar(255) DEFAULT NULL,
  `phone_number` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `role`, `full_name`, `photo`, `kelas`, `jurusan`, `create_at`, `session_id`, `phone_number`) VALUES
(9, 'sheva', '$2a$12$KNhsTpeeUrRT85fDlwt.ouiqnC7S.kOPiDxFjs6TBqjKnirIxa12C', 'sheva@nic.com', 'admin', 'Sheva Ramdhani', '', NULL, NULL, '2025-05-26 08:22:08', NULL, NULL),
(12, 'abimanyu237', '$2y$10$JuTewQyq1JJ6B8aEEanr8uakCdF0OaLxv/ueBd9gh8uRb1s3O/TPi', 'abimanyu@gmail.com', 'developer', 'Abimanyu Pradipa Wisnu', 'user_12_1749528787.jpg', NULL, NULL, '2025-05-27 19:08:31', 'jeb8iot6bgei301sj2sp305cnr', NULL),
(15, 'Evan', '$2a$12$KNhsTpeeUrRT85fDlwt.ouiqnC7S.kOPiDxFjs6TBqjKnirIxa12C', 'evan@nic.com', 'siswa', 'Evan', NULL, 'X', 'TEKNIK KOMPUTER DAN JARINGAN', '2025-05-29 05:21:42', '', '08787876876765'),
(16, 'nopal', '$2a$12$KNhsTpeeUrRT85fDlwt.ouiqnC7S.kOPiDxFjs6TBqjKnirIxa12C', 'nopal@nic.com', 'admin', 'Nopal Fajri', NULL, NULL, NULL, '2025-05-29 05:41:34', NULL, NULL),
(19, 'hamdan', '$2y$10$Dh5/P4.GJJRLySGAUU4GBuwIl7fxSq9VE6IU/OFf06dI4ayOqzcLe', 'hamdan@nic.com', 'admin', 'hamdan tr', NULL, NULL, NULL, '2025-06-10 16:16:58', NULL, NULL),
(20, 'hapid xjr', '$2y$10$iYllW5dBhXLqezACaEspWORBdBnI6reEUUwL89XejAcb8Z6zggOWu', 'hapid@nic.com', 'admin', 'hapid penyodom', NULL, NULL, NULL, '2025-06-10 16:27:58', NULL, NULL),
(22, 'Hamdani', '$2y$10$Y8Dminwlh/7Wgd5HfHqoI.f5whojZcQsceuiNAGuN7gdfiskQ8Ay2', 'hamdantrisnawan917@gmail.com', 'siswa', 'Hamdantri', NULL, NULL, NULL, '2025-06-10 16:33:43', 'vql082lc2lccgr81lvrrd2prbs', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_badges`
--

CREATE TABLE `user_badges` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `badge_id` int(11) NOT NULL,
  `given_by` int(11) DEFAULT NULL,
  `given_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `note` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_badges`
--

INSERT INTO `user_badges` (`id`, `user_id`, `badge_id`, `given_by`, `given_at`, `note`) VALUES
(3, 15, 1, 12, '2025-06-10 07:43:40', ''),
(4, 15, 2, 12, '2025-06-10 07:43:47', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `about_section`
--
ALTER TABLE `about_section`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `assignments`
--
ALTER TABLE `assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kelas_id` (`kelas_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `assignment_submissions`
--
ALTER TABLE `assignment_submissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assignment_id` (`assignment_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `reviewed_by` (`reviewed_by`);

--
-- Indexes for table `badges`
--
ALTER TABLE `badges`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `contact_content`
--
ALTER TABLE `contact_content`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `home_content`
--
ALTER TABLE `home_content`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `informasi`
--
ALTER TABLE `informasi`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ip_address` (`ip_address`),
  ADD KEY `attempt_time` (`attempt_time`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `materials`
--
ALTER TABLE `materials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

--
-- Indexes for table `redemptions`
--
ALTER TABLE `redemptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `skills`
--
ALTER TABLE `skills`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `store_items`
--
ALTER TABLE `store_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `store_item_badge_rules`
--
ALTER TABLE `store_item_badge_rules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `store_item_id` (`store_item_id`),
  ADD KEY `badge_id` (`badge_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_badges`
--
ALTER TABLE `user_badges`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `badge_id` (`badge_id`),
  ADD KEY `given_by` (`given_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `about_section`
--
ALTER TABLE `about_section`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `assignments`
--
ALTER TABLE `assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `assignment_submissions`
--
ALTER TABLE `assignment_submissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `badges`
--
ALTER TABLE `badges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `contact_content`
--
ALTER TABLE `contact_content`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `home_content`
--
ALTER TABLE `home_content`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `informasi`
--
ALTER TABLE `informasi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `materials`
--
ALTER TABLE `materials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `redemptions`
--
ALTER TABLE `redemptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `skills`
--
ALTER TABLE `skills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `store_items`
--
ALTER TABLE `store_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `store_item_badge_rules`
--
ALTER TABLE `store_item_badge_rules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `user_badges`
--
ALTER TABLE `user_badges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assignments`
--
ALTER TABLE `assignments`
  ADD CONSTRAINT `assignments_ibfk_1` FOREIGN KEY (`kelas_id`) REFERENCES `classes` (`id`),
  ADD CONSTRAINT `assignments_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `assignment_submissions`
--
ALTER TABLE `assignment_submissions`
  ADD CONSTRAINT `assignment_submissions_ibfk_1` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`),
  ADD CONSTRAINT `assignment_submissions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `assignment_submissions_ibfk_3` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `classes`
--
ALTER TABLE `classes`
  ADD CONSTRAINT `classes_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `materials`
--
ALTER TABLE `materials`
  ADD CONSTRAINT `materials_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `materials_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `materials_ibfk_3` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `redemptions`
--
ALTER TABLE `redemptions`
  ADD CONSTRAINT `redemptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `redemptions_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `store_items` (`id`);

--
-- Constraints for table `store_item_badge_rules`
--
ALTER TABLE `store_item_badge_rules`
  ADD CONSTRAINT `store_item_badge_rules_ibfk_1` FOREIGN KEY (`store_item_id`) REFERENCES `store_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `store_item_badge_rules_ibfk_2` FOREIGN KEY (`badge_id`) REFERENCES `badges` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_badges`
--
ALTER TABLE `user_badges`
  ADD CONSTRAINT `user_badges_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `user_badges_ibfk_2` FOREIGN KEY (`badge_id`) REFERENCES `badges` (`id`),
  ADD CONSTRAINT `user_badges_ibfk_3` FOREIGN KEY (`given_by`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
