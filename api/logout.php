<?php
require_once '../db_connect.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$username = $input['username'];

try {
    $stmt = $pdo->prepare("DELETE FROM players WHERE username = ?");
    $stmt->execute([$username]);
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}