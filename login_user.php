<?php
session_start();
if (!empty($_SESSION['auth_user'])) { header('Location: ' . (($_SESSION['auth_user']['role'] ?? '') === 'admin' ? 'index.php' : 'user_dashboard.php')); exit; }
$role = 'user'; $title = 'Login User'; $subtitle = 'Masuk menggunakan akun user yang sudah terdaftar.';
$action = 'login_user_action.php';
include __DIR__ . '/login_form.php';
