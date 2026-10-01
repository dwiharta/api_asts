<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (($_SESSION['auth_user']['role'] ?? '') !== 'admin') {
    http_response_code(403);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(['success'=>false,'message'=>'Akses hanya untuk admin.']);
    exit;
}
header("Content-Type: application/json; charset=UTF-8");
require_once "koneksi.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!empty($data['name']) && !empty($data['nisn'])) {
    $stmt = $conn->prepare("INSERT INTO users (name, nisn, ttl, gender, email, address) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $data['name'], $data['nisn'], $data['ttl'], $data['gender'], $data['email'], $data['address']);
    
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Data berhasil ditambahkan"]);
    } else {
        echo json_encode(["success" => false, "message" => "Gagal menambahkan data"]);
    }
    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Data tidak lengkap"]);
}

$conn->close();
?>