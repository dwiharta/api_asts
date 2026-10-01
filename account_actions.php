<?php
ob_start();
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/fonnte.php';

ensureAuthSchema($conn);

function accountResponse(bool $success, string $message, array $extra = [], int $status = 200): void {
    if (ob_get_length()) ob_clean();
    http_response_code($status);
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

function currentAccountId(): int {
    return (int)($_SESSION['auth_user']['id'] ?? 0);
}

function cleanAccountInput(): array {
    $name = trim((string)($_POST['name'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $whatsapp = normalizeWhatsapp($_POST['whatsapp'] ?? '');
    $password = (string)($_POST['password'] ?? '');
    $role = trim((string)($_POST['role'] ?? 'user'));
    return compact('name', 'email', 'whatsapp', 'password', 'role');
}

function validateAccountData(array $data, bool $passwordRequired = false): void {
    if ($data['name'] === '' || $data['email'] === '' || $data['whatsapp'] === '') {
        accountResponse(false, 'Nama, email, dan WhatsApp wajib diisi.', [], 422);
    }
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        accountResponse(false, 'Format email tidak valid.', [], 422);
    }
    if (!preg_match('/^08[0-9]{8,13}$/', $data['whatsapp'])) {
        accountResponse(false, 'Nomor WhatsApp tidak valid. Gunakan nomor aktif, contoh 081234567890.', [], 422);
    }
    if ($passwordRequired && strlen($data['password']) < 6) {
        accountResponse(false, 'Password minimal 6 karakter.', [], 422);
    }
    if ($data['password'] !== '' && strlen($data['password']) < 6) {
        accountResponse(false, 'Password minimal 6 karakter.', [], 422);
    }
}

function getAccount(int $id): ?array {
    global $conn;
    $stmt = $conn->prepare('SELECT id, name, email, whatsapp, password, role, created_at FROM accounts WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

function emailOrWhatsappTaken(string $email, string $whatsapp, int $excludeId = 0): bool {
    global $conn;
    $stmt = $conn->prepare('SELECT id FROM accounts WHERE (email = ? OR whatsapp = ?) AND id <> ? LIMIT 1');
    $stmt->bind_param('ssi', $email, $whatsapp, $excludeId);
    $stmt->execute();
    $found = (bool)$stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $found;
}

function updateSessionAccount(array $account): void {
    $_SESSION['auth_user'] = [
        'id' => (int)$account['id'],
        'name' => $account['name'] ?: 'User',
        'email' => $account['email'],
        'whatsapp' => $account['whatsapp'] ?? '',
        'role' => $account['role']
    ];
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    accountResponse(false, 'Method tidak diizinkan.', [], 405);
}

$action = $_POST['action'] ?? '';
$me = $_SESSION['auth_user'] ?? null;
if (!$me) accountResponse(false, 'Sesi login tidak ditemukan.', [], 401);

/* USER: edit akun sendiri */
if ($action === 'self_update') {
    if (($me['role'] ?? '') !== 'user') accountResponse(false, 'Aksi ini khusus user.', [], 403);

    $data = cleanAccountInput();
    validateAccountData($data, false);

    $account = getAccount((int)$me['id']);
    if (!$account) accountResponse(false, 'Akun tidak ditemukan.', [], 404);

    if ($data['password'] !== '' && !password_verify((string)($_POST['current_password'] ?? ''), $account['password'])) {
        accountResponse(false, 'Password saat ini salah.', [], 422);
    }
    if (emailOrWhatsappTaken($data['email'], $data['whatsapp'], (int)$account['id'])) {
        accountResponse(false, 'Email atau nomor WhatsApp sudah digunakan akun lain.', [], 409);
    }

    $passwordHash = $data['password'] !== '' ? password_hash($data['password'], PASSWORD_DEFAULT) : $account['password'];
    $stmt = $conn->prepare('UPDATE accounts SET name = ?, email = ?, whatsapp = ?, password = ? WHERE id = ?');
    $stmt->bind_param('ssssi', $data['name'], $data['email'], $data['whatsapp'], $passwordHash, $account['id']);
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        accountResponse(false, 'Gagal memperbarui akun: ' . $error, [], 500);
    }
    $stmt->close();

    $updated = getAccount((int)$account['id']);
    updateSessionAccount($updated);

    $message = "Halo {$updated['name']}! 👋\n\nProfil akun kamu berhasil diperbarui.\nEmail: {$updated['email']}\nNomor WhatsApp: {$updated['whatsapp']}\n\nJika perubahan ini bukan kamu, segera hubungi admin.";
    $wa = sendFonnteMessage($updated['whatsapp'], $message);

    accountResponse(true, 'Data akun berhasil diperbarui.', [
        'notification' => ['sent' => $wa['success'], 'message' => $wa['message'] ?? ''],
        'account' => [
            'id' => (int)$updated['id'], 'name' => $updated['name'], 'email' => $updated['email'],
            'whatsapp' => $updated['whatsapp'], 'role' => $updated['role']
        ]
    ]);
}

/* USER: hapus akun sendiri */
if ($action === 'self_delete') {
    if (($me['role'] ?? '') !== 'user') accountResponse(false, 'Aksi ini khusus user.', [], 403);
    $password = (string)($_POST['password'] ?? '');
    $account = getAccount((int)$me['id']);
    if (!$account) accountResponse(false, 'Akun tidak ditemukan.', [], 404);
    if (!password_verify($password, $account['password'])) accountResponse(false, 'Password salah. Akun tidak dihapus.', [], 422);

    // Kirim notifikasi sebelum data dihapus.
    $wa = sendFonnteMessage($account['whatsapp'], "Halo {$account['name']}! 👋\n\nAkun kamu di Sistem Ujian ASTS telah dihapus sesuai permintaan.\nWaktu: " . date('d-m-Y H:i:s') . " WIB\n\nJika ini bukan kamu, hubungi admin.");

    $stmt = $conn->prepare('DELETE FROM accounts WHERE id = ? AND role = \'user\'');
    $stmt->bind_param('i', $account['id']);
    $ok = $stmt->execute();
    $stmt->close();
    if (!$ok) accountResponse(false, 'Gagal menghapus akun.', [], 500);

    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();

    accountResponse(true, 'Akun berhasil dihapus.', ['notification' => ['sent' => $wa['success'], 'message' => $wa['message'] ?? '']]);
}

/* ADMIN: ambil semua akun */
if ($action === 'admin_list') {
    if (($me['role'] ?? '') !== 'admin') accountResponse(false, 'Akses hanya untuk admin.', [], 403);
    $result = $conn->query('SELECT id, name, email, whatsapp, role, created_at FROM accounts ORDER BY id DESC');
    $accounts = [];
    while ($row = $result->fetch_assoc()) $accounts[] = $row;
    accountResponse(true, 'Data akun berhasil diambil.', ['accounts' => $accounts]);
}

/* ADMIN: tambah akun */
if ($action === 'admin_create') {
    if (($me['role'] ?? '') !== 'admin') accountResponse(false, 'Akses hanya untuk admin.', [], 403);
    $data = cleanAccountInput();
    validateAccountData($data, true);
    if (!in_array($data['role'], ['user', 'admin'], true)) accountResponse(false, 'Role tidak valid.', [], 422);
    if (emailOrWhatsappTaken($data['email'], $data['whatsapp'], 0)) accountResponse(false, 'Email atau nomor WhatsApp sudah digunakan akun lain.', [], 409);

    $hash = password_hash($data['password'], PASSWORD_DEFAULT);
    $stmt = $conn->prepare('INSERT INTO accounts (name,email,whatsapp,password,role) VALUES (?,?,?,?,?)');
    $stmt->bind_param('sssss', $data['name'], $data['email'], $data['whatsapp'], $hash, $data['role']);
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        accountResponse(false, 'Gagal membuat akun: ' . $error, [], 500);
    }
    $id = $stmt->insert_id;
    $stmt->close();

    $wa = sendFonnteMessage($data['whatsapp'], "Halo {$data['name']}! 👋\n\nAkun kamu telah dibuat oleh admin Sistem Ujian ASTS.\nRole akun: " . strtoupper($data['role']) . "\nEmail: {$data['email']}\nWaktu: " . date('d-m-Y H:i:s') . " WIB\n\nKamu dapat login menggunakan email dan password yang diberikan admin.");
    accountResponse(true, 'Akun berhasil dibuat.', ['notification'=>['sent'=>$wa['success'],'message'=>$wa['message'] ?? ''], 'id'=>$id]);
}

/* ADMIN: edit akun user/admin */
if ($action === 'admin_update') {
    if (($me['role'] ?? '') !== 'admin') accountResponse(false, 'Akses hanya untuk admin.', [], 403);

    $id = (int)($_POST['id'] ?? 0);
    $data = cleanAccountInput();
    if ($id <= 0) accountResponse(false, 'ID akun tidak valid.', [], 422);
    if (!in_array($data['role'], ['user', 'admin'], true)) accountResponse(false, 'Role tidak valid.', [], 422);
    validateAccountData($data, false);

    $account = getAccount($id);
    if (!$account) accountResponse(false, 'Akun tidak ditemukan.', [], 404);
    if ($id === (int)$me['id'] && $data['role'] !== 'admin') {
        accountResponse(false, 'Admin yang sedang login tidak boleh mengubah role dirinya sendiri.', [], 422);
    }
    if (emailOrWhatsappTaken($data['email'], $data['whatsapp'], $id)) {
        accountResponse(false, 'Email atau nomor WhatsApp sudah digunakan akun lain.', [], 409);
    }

    if ($data['password'] !== '') {
        $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare('UPDATE accounts SET name=?, email=?, whatsapp=?, password=?, role=? WHERE id=?');
        $stmt->bind_param('sssssi', $data['name'], $data['email'], $data['whatsapp'], $passwordHash, $data['role'], $id);
    } else {
        $stmt = $conn->prepare('UPDATE accounts SET name=?, email=?, whatsapp=?, role=? WHERE id=?');
        $stmt->bind_param('ssssi', $data['name'], $data['email'], $data['whatsapp'], $data['role'], $id);
    }
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        accountResponse(false, 'Gagal memperbarui akun: ' . $error, [], 500);
    }
    $stmt->close();

    $updated = getAccount($id);
    $roleChanged = $account['role'] !== $updated['role'];
    $waMessage = "Halo {$updated['name']}! 👋\n\nData akun kamu telah diperbarui oleh admin Sistem Ujian ASTS.\nRole akun: " . strtoupper($updated['role']) . "\nEmail: {$updated['email']}\nNomor WhatsApp: {$updated['whatsapp']}\nWaktu: " . date('d-m-Y H:i:s') . " WIB\n\n" . ($roleChanged ? 'Role akun kamu baru saja diubah oleh admin.' : 'Perubahan data akun kamu telah disimpan.') . "\nJika perubahan ini tidak kamu kenali, hubungi admin.";
    $wa = sendFonnteMessage($updated['whatsapp'], $waMessage);

    if ($id === (int)$me['id']) updateSessionAccount($updated);

    accountResponse(true, 'Data akun berhasil diperbarui.', [
        'notification' => ['sent' => $wa['success'], 'message' => $wa['message'] ?? ''],
        'role_changed' => $roleChanged,
        'account' => ['id'=>(int)$updated['id'], 'name'=>$updated['name'], 'email'=>$updated['email'], 'whatsapp'=>$updated['whatsapp'], 'role'=>$updated['role'], 'created_at'=>$updated['created_at']]
    ]);
}

/* ADMIN: hapus akun user */
if ($action === 'admin_delete') {
    if (($me['role'] ?? '') !== 'admin') accountResponse(false, 'Akses hanya untuk admin.', [], 403);
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) accountResponse(false, 'ID akun tidak valid.', [], 422);
    if ($id === (int)$me['id']) accountResponse(false, 'Admin yang sedang login tidak boleh menghapus akunnya sendiri dari dashboard.', [], 422);

    $account = getAccount($id);
    if (!$account) accountResponse(false, 'Akun tidak ditemukan.', [], 404);

    $wa = sendFonnteMessage($account['whatsapp'], "Halo {$account['name']}! 👋\n\nAkun kamu di Sistem Ujian ASTS telah dihapus oleh admin.\nWaktu: " . date('d-m-Y H:i:s') . " WIB\n\nJika kamu merasa penghapusan ini tidak semestinya, hubungi admin sekolah.");

    $stmt = $conn->prepare('DELETE FROM accounts WHERE id = ?');
    $stmt->bind_param('i', $id);
    $ok = $stmt->execute();
    $stmt->close();
    if (!$ok) accountResponse(false, 'Gagal menghapus akun.', [], 500);

    accountResponse(true, 'Akun berhasil dihapus.', ['notification' => ['sent' => $wa['success'], 'message' => $wa['message'] ?? '']]);
}

accountResponse(false, 'Aksi tidak dikenali.', [], 400);
