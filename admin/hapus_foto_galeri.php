<?php
// hapus_foto_galeri.php
$host = "localhost";
$user = "vicj7142_user_cekajadulu"; 
$pass = "supriyanto"; 
$db_name = "vicj7142_cekajadulu"; 

$conn = mysqli_connect($host, $user, $pass, $db_name);
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

$foto_name = mysqli_real_escape_string($conn, $_GET['foto_name']);
$back_id = (int)$_GET['back_id'];
$back_fitur = mysqli_real_escape_string($conn, $_GET['back_fitur']);

if (!empty($foto_name)) {
    // 1. Hapus file fisik fotonya di dalam folder uploads
    $target_file = "../uploads/" . $foto_name;
    if (file_exists($target_file)) {
        unlink($target_file);
    }
    
    // 2. Hapus record datanya di database
    $query = "DELETE FROM galeri_universal WHERE image_name = '$foto_name'";
    mysqli_query($conn, $query);
}

// SOLUSI AMAN ANTI-404: Menggunakan redirect dinamis sesuai pola URL server lu (tanpa .php)
header("Location: edit_data?id=" . $back_id . "&fitur=" . $back_fitur);
exit;
?>