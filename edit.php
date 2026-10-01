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

if (!empty($data['id'])) {
    $stmt = $conn->prepare("UPDATE users SET name=?, nisn=?, ttl=?, gender=?, email=?, address=? WHERE id=?");
    $stmt->bind_param("ssssssi", $data['name'], $data['nisn'], $data['ttl'], $data['gender'], $data['email'], $data['address'], $data['id']);
    
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Data berhasil diperbarui"]);
    } else {
        echo json_encode(["success" => false, "message" => "Gagal memperbarui data"]);
    }
    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "ID tidak ditemukan"]);
}

$conn->close();
?>