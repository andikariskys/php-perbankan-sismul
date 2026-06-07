<?php
session_start();
include "../../config/database.php";

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php?pesan=belum_login');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$upload_dir = "../../assets/uploads/";
$max_size = 5 * 1024 * 1024; 
$allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];

if (!isset($_FILES['foto_profil']) || $_FILES['foto_profil']['error'] !== UPLOAD_ERR_OK) {
    header('Location: index.php?pesan=foto_gagal');
    exit;
}

$file = $_FILES['foto_profil'];

if ($file['size'] > $max_size) {
    header('Location: index.php?pesan=file_terlalu_besar');
    exit;
}

$image_info = getimagesize($file['tmp_name']);
if ($image_info === false || !in_array($image_info['mime'], $allowed_types)) {
    header('Location: index.php?pesan=format_tidak_valid');
    exit;
}

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if ($ext === 'jpeg') {
    $ext = 'jpg';
}

$nama_file = 'profil_' . $user_id . '_' . time() . '.' . $ext;
$target_path = $upload_dir . $nama_file;

$max_width = 400;
$max_height = 400;

$source_width = $image_info[0];
$source_height = $image_info[1];

$ratio = min($max_width / $source_width, $max_height / $source_height);
if ($ratio >= 1) {
    
    $new_width = $source_width;
    $new_height = $source_height;
} else {
    $new_width = (int) round($source_width * $ratio);
    $new_height = (int) round($source_height * $ratio);
}

switch ($image_info['mime']) {
    case 'image/jpeg':
    case 'image/jpg':
        $source_image = imagecreatefromjpeg($file['tmp_name']);
        break;
    case 'image/png':
        $source_image = imagecreatefrompng($file['tmp_name']);
        break;
    default:
        header('Location: index.php?pesan=format_tidak_valid');
        exit;
}

if (!$source_image) {
    header('Location: index.php?pesan=foto_gagal');
    exit;
}

$new_image = imagecreatetruecolor($new_width, $new_height);

if ($image_info['mime'] === 'image/png') {
    imagealphablending($new_image, false);
    imagesavealpha($new_image, true);
    $transparent = imagecolorallocatealpha($new_image, 0, 0, 0, 127);
    imagefill($new_image, 0, 0, $transparent);
}

imagecopyresampled(
    $new_image,
    $source_image,
    0,
    0,
    0,
    0,
    $new_width,
    $new_height,
    $source_width,
    $source_height
);

if ($ext === 'png') {
    $save_success = imagepng($new_image, $target_path, 6); 
} else {
    $save_success = imagejpeg($new_image, $target_path, 80); 
}

imagedestroy($source_image);
imagedestroy($new_image);

if (!$save_success) {
    header('Location: index.php?pesan=foto_gagal');
    exit;
}

$stmt = mysqli_prepare($conn, "SELECT foto_profil FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$old_user = mysqli_fetch_assoc($result);
$old_foto = $old_user['foto_profil'];

$stmt = mysqli_prepare($conn, "UPDATE users SET foto_profil = ?, updated_at = NOW() WHERE id = ?");
mysqli_stmt_bind_param($stmt, "si", $nama_file, $user_id);

if (mysqli_stmt_execute($stmt)) {
    
    if ($old_foto !== 'default.png' && file_exists($upload_dir . $old_foto)) {
        unlink($upload_dir . $old_foto);
    }
    
    $_SESSION['foto_profil'] = $nama_file;
    header('Location: index.php?pesan=foto_berhasil');
} else {
    
    if (file_exists($target_path)) {
        unlink($target_path);
    }
    header('Location: index.php?pesan=foto_gagal');
}
exit;
