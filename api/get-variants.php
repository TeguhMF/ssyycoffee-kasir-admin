<?php
// api/get-variants.php
require_once '../config/database.php';
header('Content-Type: application/json');

$menu_id = isset($_GET['menu_id']) ? intval($_GET['menu_id']) : 0;

try {
    $stmt_groups = $pdo->prepare("SELECT * FROM variant_groups WHERE menu_id = ? ORDER BY id ASC");
    $stmt_groups->execute([$menu_id]);
    $groups = $stmt_groups->fetchAll();

    $result = [];
    foreach ($groups as $g) {
        $stmt_opts = $pdo->prepare("SELECT * FROM variant_options WHERE variant_group_id = ? ORDER BY id ASC");
        $stmt_opts->execute([$g['id']]);
        
        $result[] = [
            'group_id' => $g['id'],
            'group_name' => $g['group_name'],
            'options' => $stmt_opts->fetchAll()
        ];
    }
    
    echo json_encode(['status' => 'success', 'data' => $result]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}