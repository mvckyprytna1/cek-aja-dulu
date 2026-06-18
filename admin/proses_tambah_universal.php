<?php
// proses_tambah_universal.php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

$host = "localhost";
$user = "vicj7142_user_cekajadulu"; 
$pass = "supriyanto"; 
$db_name = "vicj7142_cekajadulu";

// Koneksi database
$conn = mysqli_connect($host, $user, $pass, $db_name);
if (!$conn) {
    error_log("DB Connection Failed: " . mysqli_connect_error());
    die("Database connection error. Please contact admin.");
}

mysqli_set_charset($conn, "utf8mb4");

// === VALIDASI INPUT ===
function validateInput($data) {
    if (empty($data)) return false;
    return trim($data);
}

// Cek method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request method");
}

// Tangkap data dari form — sesuai kolom tabel yang ada
$title         = validateInput($_POST['title'] ?? '');
$category      = validateInput($_POST['category'] ?? '') ?: '';
$status        = validateInput($_POST['status'] ?? '') ?: 'Aktif';
$status_class  = validateInput($_POST['status_class'] ?? '') ?: 'active';
$location      = validateInput($_POST['location'] ?? '');
$price         = intval($_POST['price'] ?? 0);
$price_display = validateInput($_POST['price_display'] ?? '') ?: '';
$price_period  = validateInput($_POST['price_period'] ?? '') ?: '';
$specs         = validateInput($_POST['specs'] ?? '') ?: '';
$description   = validateInput($_POST['description'] ?? '') ?: '';
$wa_message    = validateInput($_POST['wa_message'] ?? '') ?: '';

// Validasi field wajib
if (!$title || !$location) {
    die("Error: Title dan Location wajib diisi!");
}

// === PROSES UPLOAD GAMBAR UTAMA ===
$image_type = "";
if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] == 0) {
    $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $file_ext = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));
    
    if (!in_array($file_ext, $allowed_ext)) {
        die("Error: Format file tidak didukung. Gunakan JPG, PNG, GIF, atau WEBP");
    }
    
    if ($_FILES['image_file']['size'] > 5242880) {
        die("Error: Ukuran file terlalu besar (max 5MB)");
    }
    
    $image_type = "utama_" . uniqid() . "." . $file_ext;
    if (!move_uploaded_file($_FILES['image_file']['tmp_name'], "../uploads/" . $image_type)) {
        die("Error: Gagal upload gambar utama");
    }
}

// === TENTUKAN TABEL BERDASARKAN MODUL ===
$modul = validateInput($_POST['modul'] ?? '') ?: 'properti';

$tabel_map = [
    'properti'    => 'katalog_properti',
    'jasa'        => 'katalog_jasa',
    'barang'      => 'katalog_barang',
    'rekomendasi' => 'katalog_rekomendasi',
];

if (!array_key_exists($modul, $tabel_map)) {
    die("Error: Modul tidak valid!");
}

$nama_tabel = $tabel_map[$modul];

// === INSERT KE TABEL YANG SESUAI ===
$query = "INSERT INTO `$nama_tabel` 
          (title, location, price, price_display, price_period, specs, description, category, status, status_class, image_type, wa_message) 
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $query);
if (!$stmt) {
    error_log("Prepare Failed: " . mysqli_error($conn));
    die("Error preparing statement: " . mysqli_error($conn));
}

// 12 parameter: s,s,i,s,s,s,s,s,s,s,s,s
mysqli_stmt_bind_param(
    $stmt,
    "ssisssssssss",
    $title, $location, $price, $price_display, $price_period,
    $specs, $description, $category, $status, $status_class,
    $image_type, $wa_message
);

if (!mysqli_stmt_execute($stmt)) {
    error_log("Execute Failed: " . mysqli_stmt_error($stmt));
    die("Error executing statement: " . mysqli_stmt_error($stmt));
}

$item_id = mysqli_insert_id($conn);
mysqli_stmt_close($stmt);

// === PROSES UPLOAD MULTIPLE GALERI ===
// Kolom galeri_universal: fitur, item_id, image_name
if (isset($_FILES['gallery_images']) && !empty($_FILES['gallery_images']['name'][0])) {
    $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    foreach ($_FILES['gallery_images']['tmp_name'] as $key => $tmp_name) {
        if ($_FILES['gallery_images']['error'][$key] == 0) {
            $file_name = $_FILES['gallery_images']['name'][$key];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            if (!in_array($file_ext, $allowed_ext)) {
                error_log("Invalid gallery file format: " . $file_name);
                continue;
            }
            
            if ($_FILES['gallery_images']['size'][$key] > 5242880) {
                error_log("Gallery file too large: " . $file_name);
                continue;
            }
            
            $new_file_name = "galeri_" . uniqid() . "." . $file_ext;
            
            if (move_uploaded_file($tmp_name, "../uploads/" . $new_file_name)) {
                $query_galeri = "INSERT INTO galeri_universal (fitur, item_id, image_name) VALUES (?, ?, ?)";
                $stmt_galeri = mysqli_prepare($conn, $query_galeri);
                
                if ($stmt_galeri) {
                    mysqli_stmt_bind_param($stmt_galeri, "sis", $modul, $item_id, $new_file_name);
                    if (!mysqli_stmt_execute($stmt_galeri)) {
                        error_log("Galeri insert failed: " . mysqli_stmt_error($stmt_galeri));
                    }
                    mysqli_stmt_close($stmt_galeri);
                }
            } else {
                error_log("Failed to move gallery file: " . $file_name);
            }
        }
    }
}

mysqli_close($conn);

// === REDIRECT SETELAH SUKSES ===
header("Location: proses_tambah_universal.php?success=1", true, 302);
exit();
?>