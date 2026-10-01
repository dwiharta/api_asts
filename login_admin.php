<?php
session_start();
if (!empty($_SESSION['auth_user'])) { header('Location: ' . (($_SESSION['auth_user']['role'] ?? '') === 'admin' ? 'index.php' : 'user_dashboard.php')); exit; }
$role = 'admin'; $title = 'Login Admin'; $subtitle = 'Gunakan akun admin untuk membuka dashboard pengelolaan.';
$action = 'login_admin_action.php';
include __DIR__ . '/login_form.php';
