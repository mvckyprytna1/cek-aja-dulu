<?php
ob_start();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

error_reporting(0);
ini_set('display_errors', 0);

$host = "localhost";
$user = "vicj7142_user_cekajadulu";     
$pass = "supriyanto";             
$db   = "vicj7142_cekajadulu"; 

$_con = new mysqli($host, $user, $pass, $db);

if ($_con->connect_error) {
    ob_clean();
    echo json_encode(["status" => "error", "message" => "Koneksi database gagal: " . $_con->connect_error]);
    exit;
}

// DELETE GALERI
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'delete_galeri') {
    $galeri_id = (int)$_POST['galeri_id'];
    $result = $_con->query("SELECT image_name FROM galeri_universal WHERE id = $galeri_id");
    $row = $result->fetch_assoc();
    if ($row) {
        @unlink("../uploads/" . $row['image_name']);
        $_con->query("DELETE FROM galeri_universal WHERE id = $galeri_id");
        ob_clean();
        echo json_encode(["status" => "success"]);
    } else {
        ob_clean();
        echo json_encode(["status" => "error", "message" => "Foto tidak ditemukan"]);
    }
    exit;
}

// VALIDASI PARAMETER FITUR
$fitur = isset($_GET['fitur']) ? trim($_con->real_escape_string($_GET['fitur'])) : '';
$fitur_valid = ['properti', 'jasa', 'barang', 'rekomendasi'];

if (empty($fitur) || !in_array($fitur, $fitur_valid)) {
    ob_clean();
    echo json_encode(["status" => "error", "message" => "Parameter fitur '" . htmlspecialchars($fitur) . "' tidak valid atau kosong!"]);
    exit;
}

$id_param = isset($_GET['id']) ? trim($_con->real_escape_string($_GET['id'])) : '';
$tabel_target = "katalog_" . $fitur;

$cek_tabel = $_con->query("SHOW TABLES LIKE '$tabel_target'");
if ($cek_tabel->num_rows == 0) {
    ob_clean();
    echo json_encode(["status" => "error", "message" => "Tabel '$tabel_target' tidak ditemukan!"]);
    exit;
}

// QUERY
if (!empty($id_param)) {
    $query = "SELECT $tabel_target.*, 
              GROUP_CONCAT(galeri_universal.id SEPARATOR '|') as gallery_ids,
              GROUP_CONCAT(galeri_universal.image_name SEPARATOR '|') as gallery 
              FROM $tabel_target 
              LEFT JOIN galeri_universal ON $tabel_target.id = galeri_universal.item_id AND galeri_universal.fitur = '$fitur'
              WHERE $tabel_target.id = '$id_param'
              GROUP BY $tabel_target.id";
} else {
    $query = "SELECT $tabel_target.*, 
              GROUP_CONCAT(galeri_universal.id SEPARATOR '|') as gallery_ids,
              GROUP_CONCAT(galeri_universal.image_name SEPARATOR '|') as gallery 
              FROM $tabel_target 
              LEFT JOIN galeri_universal ON $tabel_target.id = galeri_universal.item_id AND galeri_universal.fitur = '$fitur'
              GROUP BY $tabel_target.id 
              ORDER BY $tabel_target.id DESC";
}

$result = $_con->query($query);

if (!$result) {
    ob_clean();
    echo json_encode(["status" => "error", "message" => "Gagal query: " . $_con->error]);
    exit;
}

// HELPER: konversi gallery string ke array object
function parseGallery($row) {
    if (!empty($row['gallery'])) {
        $names = explode('|', $row['gallery']);
        $ids   = explode('|', $row['gallery_ids'] ?? '');
        $combined = [];
        foreach ($names as $i => $name) {
            $combined[] = [
                "id"         => (int)($ids[$i] ?? 0),
                "image_name" => $name
            ];
        }
        $row['gallery'] = $combined;
    } else {
        $row['gallery'] = [];
    }
    unset($row['gallery_ids']);
    return $row;
}

// PEMPROSESAN HASIL
if (!empty($id_param)) {
    $row = $result->fetch_assoc();
    if ($row) {
        $row = parseGallery($row);
        $response = ["status" => "success", "fitur" => $fitur, "data" => $row];
    } else {
        ob_clean();
        echo json_encode(["status" => "error", "message" => "Data dengan ID $id_param tidak ditemukan!"]);
        exit;
    }
} else {
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = parseGallery($row);
    }
    $response = ["status" => "success", "fitur" => $fitur, "total" => count($data), "data" => $data];
}

$output_json = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

if ($output_json === false) {
    ob_clean();
    echo json_encode(["status" => "error", "message" => "Gagal konversi JSON: " . json_last_error_msg()]);
} else {
    ob_clean();
    echo $output_json;
}

$_con->close();
exit;
?>