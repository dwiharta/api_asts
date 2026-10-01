<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/**
 * Pastikan struktur tabel accounts kompatibel dengan sistem login/register.
 * Ini sengaja dibuat otomatis supaya database lama yang belum punya kolom
 * name / whatsapp / role tetap bisa dipakai tanpa error "Unknown column".
 */
function ensureAuthSchema(mysqli $conn): void {
    $conn->query("CREATE TABLE IF NOT EXISTS accounts (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NULL,
        email VARCHAR(150) NOT NULL,
        whatsapp VARCHAR(20) NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('user','admin') NOT NULL DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $columns = [];
    $result = $conn->query("SHOW COLUMNS FROM accounts");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $columns[strtolower($row['Field'])] = true;
        }
        $result->free();
    }

    if (!isset($columns['name'])) {
        $conn->query("ALTER TABLE accounts ADD COLUMN name VARCHAR(100) NULL AFTER id");
    }

    if (!isset($columns['whatsapp'])) {
        $conn->query("ALTER TABLE accounts ADD COLUMN whatsapp VARCHAR(20) NULL AFTER email");
    }

    if (!isset($columns['role'])) {
        $conn->query("ALTER TABLE accounts ADD COLUMN role ENUM('user','admin') NOT NULL DEFAULT 'user' AFTER password");
    }

    if (!isset($columns['created_at'])) {
        $conn->query("ALTER TABLE accounts ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
    }

    // Data lama yang belum punya nama diberi nama sementara dari bagian email.
    $conn->query("UPDATE accounts
                  SET name = LEFT(SUBSTRING_INDEX(email, '@', 1), 100)
                  WHERE (name IS NULL OR name = '') AND email IS NOT NULL AND email <> ''");

    // Pastikan admin bawaan tersedia.
    $adminEmail = 'admin@ujianasts.local';
    $adminName = 'Administrator';
    $adminWhatsapp = '080000000000';
    $adminHash = '$2y$12$jeVIOfKRqSSZ/ldg6/boTuJxTkoJ2RRbtURkGNg8hr0ZZtu4qSMSO'; // Admin123!

    $check = $conn->prepare("SELECT id FROM accounts WHERE email = ? LIMIT 1");
    if ($check) {
        $check->bind_param('s', $adminEmail);
        $check->execute();
        $existing = $check->get_result()->fetch_assoc();
        $check->close();

        if ($existing) {
            $stmt = $conn->prepare("UPDATE accounts SET name=?, whatsapp=?, password=?, role='admin' WHERE id=?");
            if ($stmt) {
                $id = (int)$existing['id'];
                $stmt->bind_param('sssi', $adminName, $adminWhatsapp, $adminHash, $id);
                $stmt->execute();
                $stmt->close();
            }
        } else {
            $stmt = $conn->prepare("INSERT INTO accounts (name,email,whatsapp,password,role) VALUES (?,?,?,?, 'admin')");
            if ($stmt) {
                $stmt->bind_param('ssss', $adminName, $adminEmail, $adminWhatsapp, $adminHash);
                $stmt->execute();
                $stmt->close();
            }
        }
    }
}

function requireLogin(): void {
    if (empty($_SESSION['auth_user'])) {
        header('Location: login.php');
        exit;
    }
}

function requireAdmin(): void {
    requireLogin();
    if (($_SESSION['auth_user']['role'] ?? '') !== 'admin') {
        header('Location: user_dashboard.php');
        exit;
    }
}

function requireUser(): void {
    requireLogin();
    if (($_SESSION['auth_user']['role'] ?? '') !== 'user') {
        header('Location: index.php');
        exit;
    }
}

function doLogin(string $requiredRole): void {
    require_once __DIR__ . '/koneksi.php';
    require_once __DIR__ . '/fonnte.php';

    ensureAuthSchema($conn);

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        respondLogin(false, 'Method tidak diizinkan.');
    }

    $email = trim($_POST['email'] ?? '');
    $password = (string)($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        respondLogin(false, 'Email dan password wajib diisi.');
    }

    $stmt = $conn->prepare('SELECT id, name, email, whatsapp, password, role FROM accounts WHERE email = ? LIMIT 1');
    if (!$stmt) {
        respondLogin(false, 'Database belum kompatibel. Jalankan database_auth.sql atau refresh halaman lalu coba lagi.');
    }

    $stmt->bind_param('s', $email);
    $stmt->execute();
    $account = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$account || !password_verify($password, $account['password'])) {
        respondLogin(false, 'Email atau password salah.');
    }

    if (($account['role'] ?? 'user') !== $requiredRole) {
        $message = $requiredRole === 'admin'
            ? 'Akun ini bukan akun admin. Gunakan Login User.'
            : 'Akun ini bukan akun user. Gunakan Login Admin.';
        respondLogin(false, $message);
    }

    session_regenerate_id(true);
    $_SESSION['auth_user'] = [
        'id' => (int)$account['id'],
        'name' => $account['name'] ?: 'User',
        'email' => $account['email'],
        'whatsapp' => $account['whatsapp'] ?? '',
        'role' => $account['role']
    ];

    $fonnte = sendLoginNotification($_SESSION['auth_user']['name'], $_SESSION['auth_user']['whatsapp']);
    $redirect = $requiredRole === 'admin' ? 'index.php' : 'user_dashboard.php';

    respondLogin(true, 'Login berhasil.', $redirect, $fonnte['success']);
}

function respondLogin(bool $success, string $message, string $redirect = '', bool $notificationSent = false): void {
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'redirect' => $redirect,
        'notification' => ['sent' => $notificationSent]
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
