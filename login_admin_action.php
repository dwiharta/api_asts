<?php
session_start();
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/auth.php';
doLogin('admin');
