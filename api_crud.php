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
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$file_json = __DIR__ . '/users.json';

if (!file_exists($file_json)) {
    file_put_contents($file_json, "[]", LOCK_EX);
}

function readJsonData($file) {
    $content = @file_get_contents($file);

    if ($content === false || trim($content) === '') {
        return [];
    }

    $data = json_decode($content, true);

    return is_array($data) ? $data : [];
}

function writeJsonData($file, $data) {
    $json = json_encode(
        array_values($data),
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
    );

    return file_put_contents($file, $json, LOCK_EX) !== false;
}

function getInputData() {
    $raw = file_get_contents('php://input');

    if (!$raw) {
        return [];
    }

    $data = json_decode($raw, true);

    return is_array($data) ? $data : [];
}

function getNextId($users) {
    $maxId = 0;

    foreach ($users as $user) {
        if (isset($user['id']) && is_numeric($user['id'])) {
            $maxId = max($maxId, (int)$user['id']);
        }
    }

    return $maxId + 1;
}

function cleanUserData($input) {
    return [
        'name'    => trim((string)($input['name'] ?? '')),
        'email'   => trim((string)($input['email'] ?? '')),
        'nisn'    => trim((string)($input['nisn'] ?? '')),
        'ttl'     => trim((string)($input['ttl'] ?? '')),
        'gender'  => trim((string)($input['gender'] ?? 'MALE')),
        'address' => trim((string)($input['address'] ?? ''))
    ];
}

function sendResponse($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

$action = $_GET['action'] ?? '';

if ($action === '') {
    $input = getInputData();
    $action = $input['action'] ?? '';
}

$users = readJsonData($file_json);

switch ($action) {

    case 'get_all':
        sendResponse($users);
        break;

    case 'add':
        $input = cleanUserData(getInputData());

        if ($input['name'] === '' || $input['nisn'] === '') {
            sendResponse([
                "success" => false,
                "message" => "Nama dan NISN wajib diisi."
            ], 400);
        }

        if (strlen($input['nisn']) !== 10 || !ctype_digit($input['nisn'])) {
            sendResponse([
                "success" => false,
                "message" => "NISN harus terdiri dari 10 digit angka."
            ], 400);
        }

        $input['id'] = getNextId($users);
        $users[] = $input;

        if (!writeJsonData($file_json, $users)) {
            sendResponse([
                "success" => false,
                "message" => "Gagal menyimpan data ke users.json."
            ], 500);
        }

        sendResponse([
            "success" => true,
            "message" => "Pengguna berhasil ditambahkan!",
            "data" => $input
        ]);
        break;

    case 'update':
        $id = $_GET['id'] ?? '';
        $input = cleanUserData(getInputData());

        if ($id === '') {
            sendResponse([
                "success" => false,
                "message" => "ID tidak ditemukan."
            ], 400);
        }

        if ($input['name'] === '' || $input['nisn'] === '') {
            sendResponse([
                "success" => false,
                "message" => "Nama dan NISN wajib diisi."
            ], 400);
        }

        if (strlen($input['nisn']) !== 10 || !ctype_digit($input['nisn'])) {
            sendResponse([
                "success" => false,
                "message" => "NISN harus terdiri dari 10 digit angka."
            ], 400);
        }

        $found = false;

        foreach ($users as &$user) {
            if ((string)($user['id'] ?? '') === (string)$id) {
                $input['id'] = $user['id'];
                $user = $input;
                $found = true;
                break;
            }
        }
        unset($user);

        if (!$found) {
            sendResponse([
                "success" => false,
                "message" => "User dengan ID tersebut tidak ditemukan."
            ], 404);
        }

        if (!writeJsonData($file_json, $users)) {
            sendResponse([
                "success" => false,
                "message" => "Gagal menyimpan perubahan."
            ], 500);
        }

        sendResponse([
            "success" => true,
            "message" => "Data berhasil diperbarui!"
        ]);
        break;

    case 'delete':
        $id = $_GET['id'] ?? '';

        if ($id === '') {
            sendResponse([
                "success" => false,
                "message" => "ID tidak valid."
            ], 400);
        }

        $oldCount = count($users);

        $users = array_values(array_filter($users, function($user) use ($id) {
            return (string)($user['id'] ?? '') !== (string)$id;
        }));

        if (count($users) === $oldCount) {
            sendResponse([
                "success" => false,
                "message" => "User tidak ditemukan."
            ], 404);
        }

        if (!writeJsonData($file_json, $users)) {
            sendResponse([
                "success" => false,
                "message" => "Gagal menghapus data."
            ], 500);
        }

        sendResponse([
            "success" => true,
            "message" => "Pengguna berhasil dihapus!"
        ]);
        break;

    case 'delete_selected':
        $input = getInputData();
        $idsToDelete = $input['ids'] ?? [];

        if (!is_array($idsToDelete) || count($idsToDelete) === 0) {
            sendResponse([
                "success" => false,
                "message" => "Tidak ada user yang dipilih."
            ], 400);
        }

        $idsToDelete = array_map('strval', $idsToDelete);

        $filteredUsers = array_values(array_filter($users, function($user) use ($idsToDelete) {
            return !in_array((string)($user['id'] ?? ''), $idsToDelete, true);
        }));

        $deletedCount = count($users) - count($filteredUsers);

        if (!writeJsonData($file_json, $filteredUsers)) {
            sendResponse([
                "success" => false,
                "message" => "Gagal menghapus data terpilih."
            ], 500);
        }

        sendResponse([
            "success" => true,
            "message" => $deletedCount . " data user berhasil dihapus."
        ]);
        break;

    case 'delete_all':
        if (!writeJsonData($file_json, [])) {
            sendResponse([
                "success" => false,
                "message" => "Gagal menghapus seluruh data."
            ], 500);
        }

        sendResponse([
            "success" => true,
            "message" => "Seluruh data user berhasil dihapus!"
        ]);
        break;

    default:
        sendResponse([
            "success" => false,
            "message" => "Aksi tidak dikenali: " . $action
        ], 400);
}
?>
