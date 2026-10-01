<?php
ob_start();
session_start();
header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/fonnte.php';

ensureAuthSchema($conn);

function registerResponse(bool $success, string $message, array $extra = []): void {
    if (ob_get_length()) { ob_clean(); }
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

function validateRegistrationInput(): array {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $whatsapp = normalizeWhatsapp($_POST['whatsapp'] ?? '');
    $password = (string)($_POST['password'] ?? '');
    $confirmPassword = (string)($_POST['confirm_password'] ?? '');

    if ($name === '' || $email === '' || $whatsapp === '' || $password === '' || $confirmPassword === '') {
        registerResponse(false, 'Semua field wajib diisi.');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        registerResponse(false, 'Format email tidak valid.');
    }
    if (strlen($password) < 6) {
        registerResponse(false, 'Password minimal 6 karakter.');
    }
    if ($password !== $confirmPassword) {
        registerResponse(false, 'Konfirmasi password tidak sama.');
    }
    if (!preg_match('/^08[0-9]{8,13}$/', $whatsapp)) {
        registerResponse(false, 'Nomor WhatsApp tidak valid. Gunakan nomor aktif, contoh: 081234567890.');
    }

    return compact('name', 'email', 'whatsapp', 'password');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    registerResponse(false, 'Method tidak diizinkan.');
}

$action = $_POST['action'] ?? 'send_otp';

// STEP 1: validasi data + kirim OTP melalui Fonnte.
if ($action === 'send_otp') {
    $data = validateRegistrationInput();

    $check = $conn->prepare('SELECT id FROM accounts WHERE email = ? OR whatsapp = ? LIMIT 1');
    $check->bind_param('ss', $data['email'], $data['whatsapp']);
    $check->execute();
    $exists = $check->get_result()->fetch_assoc();
    $check->close();

    if ($exists) {
        registerResponse(false, 'Email atau nomor WhatsApp sudah terdaftar.');
    }

    $otp = (string)random_int(100000, 999999);
    $otpHash = password_hash($otp, PASSWORD_DEFAULT);

    $_SESSION['pending_registration'] = [
        'name' => $data['name'],
        'email' => $data['email'],
        'whatsapp' => $data['whatsapp'],
        'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
        'otp_hash' => $otpHash,
        'expires_at' => time() + 300,
        'last_sent_at' => time()
    ];

    $message = "Halo {$data['name']}! 👋\n\n"
             . "Kode OTP registrasi akun kamu: {$otp}\n\n"
             . "Kode berlaku selama 5 menit. Jangan berikan kode ini kepada siapa pun.\n\n"
             . "Jika kamu tidak merasa mendaftar, abaikan pesan ini.";

    $fonnte = sendFonnteMessage($data['whatsapp'], $message);

    if (!$fonnte['success']) {
        unset($_SESSION['pending_registration']);
        registerResponse(false, 'OTP tidak dapat dikirim ke WhatsApp. ' . ($fonnte['message'] ?? 'Periksa token Fonnte dan koneksi server.'));
    }

    registerResponse(true, 'OTP sudah dikirim ke nomor WhatsApp kamu.', [
        'step' => 'verify_otp',
        'whatsapp' => $data['whatsapp'],
        'expires_in' => 300
    ]);
}

// STEP 2: verifikasi OTP. Akun BARU dibuat hanya setelah OTP benar.
if ($action === 'verify_otp') {
    $otp = preg_replace('/\D/', '', (string)($_POST['otp'] ?? ''));
    $pending = $_SESSION['pending_registration'] ?? null;

    if (!$pending) {
        registerResponse(false, 'Sesi registrasi sudah tidak tersedia. Silakan daftar ulang.');
    }
    if (time() > (int)$pending['expires_at']) {
        unset($_SESSION['pending_registration']);
        registerResponse(false, 'OTP sudah kedaluwarsa. Silakan minta OTP baru.');
    }
    if (!preg_match('/^[0-9]{6}$/', $otp)) {
        registerResponse(false, 'Masukkan OTP 6 digit.');
    }
    if (!password_verify($otp, $pending['otp_hash'])) {
        registerResponse(false, 'OTP salah. Periksa kode yang dikirim ke WhatsApp kamu.');
    }

    // Cek ulang agar tidak terjadi duplikasi selama OTP masih aktif.
    $check = $conn->prepare('SELECT id FROM accounts WHERE email = ? OR whatsapp = ? LIMIT 1');
    $check->bind_param('ss', $pending['email'], $pending['whatsapp']);
    $check->execute();
    $exists = $check->get_result()->fetch_assoc();
    $check->close();
    if ($exists) {
        unset($_SESSION['pending_registration']);
        registerResponse(false, 'Email atau nomor WhatsApp sudah terdaftar.');
    }

    $stmt = $conn->prepare("INSERT INTO accounts (name, email, whatsapp, password, role) VALUES (?, ?, ?, ?, 'user')");
    if (!$stmt) {
        registerResponse(false, 'Database tidak siap: ' . $conn->error);
    }
    $stmt->bind_param('ssss', $pending['name'], $pending['email'], $pending['whatsapp'], $pending['password_hash']);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        registerResponse(false, 'Gagal membuat akun: ' . $error);
    }
    $stmt->close();

    $welcome = sendRegistrationNotification($pending['name'], $pending['whatsapp']);
    unset($_SESSION['pending_registration']);

    registerResponse(true, 'Nomor WhatsApp berhasil diverifikasi dan akun berhasil dibuat.', [
        'step' => 'complete',
        'notification' => [
            'sent' => $welcome['success'],
            'message' => $welcome['message'] ?? ''
        ]
    ]);
}

// Meminta OTP baru dengan data yang sama dari sesi pending.
if ($action === 'resend_otp') {
    $pending = $_SESSION['pending_registration'] ?? null;
    if (!$pending) {
        registerResponse(false, 'Sesi registrasi sudah tidak tersedia. Silakan daftar ulang.');
    }

    if (time() - (int)$pending['last_sent_at'] < 30) {
        $wait = 30 - (time() - (int)$pending['last_sent_at']);
        registerResponse(false, "Tunggu {$wait} detik sebelum meminta OTP baru.");
    }

    $otp = (string)random_int(100000, 999999);
    $pending['otp_hash'] = password_hash($otp, PASSWORD_DEFAULT);
    $pending['expires_at'] = time() + 300;
    $pending['last_sent_at'] = time();
    $_SESSION['pending_registration'] = $pending;

    $message = "Kode OTP registrasi baru: {$otp}\n\nBerlaku selama 5 menit. Jangan berikan kode ini kepada siapa pun.";
    $fonnte = sendFonnteMessage($pending['whatsapp'], $message);

    if (!$fonnte['success']) {
        registerResponse(false, 'OTP baru gagal dikirim. ' . ($fonnte['message'] ?? 'Periksa Fonnte.'));
    }

    registerResponse(true, 'OTP baru sudah dikirim.', [
        'step' => 'verify_otp',
        'expires_in' => 300
    ]);
}

registerResponse(false, 'Aksi registrasi tidak dikenal.');
