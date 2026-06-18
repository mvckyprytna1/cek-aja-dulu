<?php
// proses_edit_universal.php - FIXED VERSION
$host = "localhost";
$user = "vicj7142_user_cekajadulu"; 
$pass = "supriyanto"; 
$db_name = "vicj7142_cekajadulu"; 

$conn = mysqli_connect($host, $user, $pass, $db_name);
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8mb4");

// ============================================
// 1. TANGKAP DATA DARI FORM TERLEBIH DAHULU
// ============================================
$id = (int)$_POST['id'];
$modul = mysqli_real_escape_string($conn, $_POST['modul']);
$title = mysqli_real_escape_string($conn, $_POST['title']);
$category = mysqli_real_escape_string($conn, $_POST['category']);
$status = mysqli_real_escape_string($conn, $_POST['status']);
$status_class = mysqli_real_escape_string($conn, $_POST['status_class']);
$location = mysqli_real_escape_string($conn, $_POST['location']);
$price = (int)$_POST['price'];
$price_display = mysqli_real_escape_string($conn, $_POST['price_display']);
$price_period = mysqli_real_escape_string($conn, $_POST['price_period']);
$specs = mysqli_real_escape_string($conn, $_POST['specs']);
$description = mysqli_real_escape_string($conn, $_POST['description']);
$no_wa = mysqli_real_escape_string($conn, $_POST['no_wa']);
$wa_message = mysqli_real_escape_string($conn, $_POST['wa_message']);

// ============================================
// 2. VALIDASI MODUL
// ============================================
$fitur_valid = ['properti', 'barang', 'jasa', 'rekomendasi'];
if (!in_array($modul, $fitur_valid)) {
    die("Modul tidak valid!");
}
$tabel = "katalog_" . $modul;

// ============================================
// 3. CEK APAKAH ADA FILE GAMBAR BARU
// ============================================
$query_image_part = "";
if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] == 0) {
    $ext = pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION);
    $main_image = "utama_" . uniqid() . "." . $ext;
    if (move_uploaded_file($_FILES['image_file']['tmp_name'], "../uploads/" . $main_image)) {
        $query_image_part = ", image_type = '$main_image'";
    }
}

// ============================================
// 4. UPDATE DATA KATALOG (DYNAMIC TABLE)
// ============================================
$query_update = "UPDATE $tabel SET 
                    title = '$title', 
                    category = '$category', 
                    status = '$status', 
                    status_class = '$status_class', 
                    location = '$location', 
                    price = $price, 
                    price_display = '$price_display', 
                    price_period = '$price_period', 
                    specs = '$specs', 
                    description = '$description', 
                    no_wa = '$no_wa', 
                    wa_message = '$wa_message'
                    $query_image_part 
                 WHERE id = $id";

if (mysqli_query($conn, $query_update)) {
    
    // ============================================
    // 5. HANDLE GALERI TAMBAHAN (JIKA ADA)
    // ============================================
    if (!empty($_FILES['gallery_images']['name'][0])) {
        foreach ($_FILES['gallery_images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['gallery_images']['error'][$key] == 0) {
                $file_name = $_FILES['gallery_images']['name'][$key];
                $file_tmp  = $_FILES['gallery_images']['tmp_name'][$key];
                
                $ext = pathinfo($file_name, PATHINFO_EXTENSION);
                $new_file_name = "galeri_" . uniqid() . "." . $ext;
                
                if (move_uploaded_file($file_tmp, "../uploads/" . $new_file_name)) {
                    $query_galeri = "INSERT INTO galeri_universal (fitur, item_id, image_name) 
                                     VALUES ('$modul', '$id', '$new_file_name')";
                    mysqli_query($conn, $query_galeri);
                }
            }
        }
    }

    // ============================================
    // 6. NOTIFIKASI SUKSES & REDIRECT
    // ============================================
    echo "<script>
            alert('✅ PERUBAHAN DATA BERHASIL DISIMPAN!');
            window.location.href = 'edit_data?id=" . $id . "&fitur=" . $modul . "';
          </script>";
    exit;
} else {
    echo "❌ Gagal mengupdate data ke database: " . mysqli_error($conn);
}

mysqli_close($conn);
?>