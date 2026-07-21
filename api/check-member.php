<?php
require_once '../config/database.php';
header('Content-Type: application/json');

$phone = $_GET['phone'] ?? '';

if (empty($phone)) {
    echo json_encode(['status' => 'error', 'message' => 'Nomor HP kosong']);
    exit;
}

$stmt = $pdo->prepare("SELECT id, name, points FROM customers WHERE phone = ?");
$stmt->execute([$phone]);
$customer = $stmt->fetch();

if ($customer) {
    echo json_encode(['status' => 'success', 'data' => $customer]);
} else {
    echo json_encode(['status' => 'not_found', 'message' => 'Member belum terdaftar']);
}