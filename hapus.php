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
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $data['id']);
    
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Data berhasil dihapus"]);
    } else {
        echo json_encode(["success" => false, "message" => "Gagal menghapus data"]);
    }
    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "ID tidak valid"]);
}

$conn->close();
?>