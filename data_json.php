<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (($_SESSION['auth_user']['role'] ?? '') !== 'admin') {
    http_response_code(403);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(['success'=>false,'message'=>'Akses hanya untuk admin.']);
    exit;
}
error_reporting(E_ALL);
ini_set('display_errors', 0);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Content-Type: application/json; charset=UTF-8");

$file_json = 'users.json';

// Buat file users.json otomatis jika belum ada
if (!file_exists($file_json)) {
    file_put_contents($file_json, json_encode([]));
}

// Fungsi Helper Baca JSON
function readJsonData($file) {
    $content = @file_get_contents($file);
    if ($content === false) return [];
    $data = json_decode($content, true);
    return is_array($data) ? $data : [];
}

// Fungsi Helper Tulis JSON
function writeJsonData($file, $data) {
    @file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
}

// Tangkap parameter 'action' dari GET atau dari JSON Body (POST)
$action = $_GET['action'] ?? '';
if (empty($action)) {
    $inputData = json_decode(file_get_contents('php://input'), true);
    $action = $inputData['action'] ?? '';
}

switch ($action) {
    // 1. Ambil Semua Data
    case 'get_all':
        echo json_encode(readJsonData($file_json));
        break;

    // 2. Hapus Semua Data
    case 'delete_all':
        writeJsonData($file_json, []);
        echo json_encode(["status" => "success", "message" => "Seluruh data user berhasil dihapus total!"]);
        break;

    // 3. Hapus Data Terpilih (Batch Delete)
    case 'delete_selected':
        $input = json_decode(file_get_contents('php://input'), true);
        $idsToDelete = $input['ids'] ?? [];
        $idsToDeleteStr = array_map('strval', $idsToDelete);
        
        $users = readJsonData($file_json);
        $filteredUsers = array_values(array_filter($users, function($user) use ($idsToDeleteStr) {
            return !in_array((string)$user['id'], $idsToDeleteStr);
        }));

        writeJsonData($file_json, $filteredUsers);
        echo json_encode(["status" => "success", "message" => "Data terpilih berhasil dihapus!"]);
        break;

    // 4. Hapus Satu Data
    case 'delete':
        $id = $_GET['id'] ?? 0;
        $users = readJsonData($file_json);
        $filteredUsers = array_values(array_filter($users, function($user) use ($id) {
            return (string)$user['id'] !== (string)$id;
        }));

        writeJsonData($file_json, $filteredUsers);
        echo json_encode(["status" => "success", "message" => "Pengguna berhasil dihapus!"]);
        break;

    // 5. Tambah Data Manual (Single)
    case 'add':
        $input = json_decode(file_get_contents('php://input'), true);
        $users = readJsonData($file_json);
        
        $newId = count($users) > 0 ? max(array_column($users, 'id')) + 1 : 1;
        $input['id'] = $newId;
        
        $users[] = $input;
        writeJsonData($file_json, $users);
        echo json_encode(["status" => "success", "message" => "Pengguna berhasil ditambahkan!"]);
        break;

    // 6. Update Data
    case 'update':
        $id = $_GET['id'] ?? 0;
        $input = json_decode(file_get_contents('php://input'), true);
        $users = readJsonData($file_json);
        
        foreach ($users as &$user) {
            if ((string)$user['id'] === (string)$id) {
                $user['name'] = $input['name'] ?? $user['name'];
                $user['email'] = $input['email'] ?? $user['email'];
                $user['nisn'] = $input['nisn'] ?? $user['nisn'];
                $user['gender'] = $input['gender'] ?? $user['gender'];
                $user['ttl'] = $input['ttl'] ?? $user['ttl'];
                $user['address'] = $input['address'] ?? $user['address'];
                break;
            }
        }
        
        writeJsonData($file_json, $users);
        echo json_encode(["status" => "success", "message" => "Data berhasil diperbarui!"]);
        break;


    default:
        echo json_encode(["status" => "error", "message" => "Aksi tidak dikenali: " . $action]);
        break;
}
?>