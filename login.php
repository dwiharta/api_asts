<?php
session_start();
if (!empty($_SESSION['auth_user'])) {
    $role = $_SESSION['auth_user']['role'] ?? 'user';
    header('Location: ' . ($role === 'admin' ? 'index.php' : 'user_dashboard.php'));
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Login — Ujian ASTS</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box}body{margin:0;min-height:100vh;background:#f5f7fb;color:#111827;font-family:Inter,Arial,sans-serif;display:grid;place-items:center;padding:24px}.box{width:min(820px,100%);background:#fff;border:1px solid #e5e7eb;border-radius:24px;padding:42px;box-shadow:0 24px 60px #0f172a12;text-align:center}.logo{width:52px;height:52px;margin:auto;border-radius:15px;background:#4f46e5;color:#fff;display:grid;place-items:center;font-weight:800}.eyebrow{font-size:11px;color:#4f46e5;font-weight:800;letter-spacing:.14em;text-transform:uppercase;margin-top:22px}.box h1{font-size:30px;margin:8px 0}.box p{color:#6b7280;font-size:13px;margin:0 auto 30px}.cards{display:grid;grid-template-columns:1fr 1fr;gap:16px;text-align:left}.card{display:block;border:1px solid #e5e7eb;border-radius:17px;padding:24px;text-decoration:none;color:#111827;transition:.18s}.card:hover{border-color:#818cf8;box-shadow:0 12px 28px #4f46e51a;transform:translateY(-2px)}.icon{width:44px;height:44px;border-radius:12px;background:#eef2ff;color:#4f46e5;display:grid;place-items:center;font-size:20px}.card.admin .icon{background:#111827;color:#fff}.card h2{font-size:17px;margin:17px 0 7px}.card p{margin:0;font-size:11px;line-height:1.6}.register{margin-top:24px;font-size:12px;color:#6b7280}.register a{color:#4f46e5;font-weight:800;text-decoration:none}@media(max-width:650px){.box{padding:28px 20px}.cards{grid-template-columns:1fr}}
</style></head><body><main class="box"><div class="logo">UA</div><div class="eyebrow">Account access</div><h1>Masuk ke sistem</h1><p>Pilih jenis akun yang ingin digunakan untuk masuk.</p><div class="cards"><a class="card" href="login_user.php"><div class="icon">👤</div><h2>Login User</h2><p>Untuk akun yang dibuat melalui halaman registrasi.</p></a><a class="card admin" href="login_admin.php"><div class="icon">⚙</div><h2>Login Admin</h2><p>Khusus akun admin untuk mengelola dashboard dan data.</p></a></div><div class="register">Belum punya akun? <a href="register.php">Register sebagai User</a></div></main></body></html>
