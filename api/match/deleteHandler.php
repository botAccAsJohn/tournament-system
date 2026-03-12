<?php
session_start();
require_once '../../DB/dbConnection.php';
$conn = dbConnection();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit();
}

header('Content-Type: application/json');

$id = !empty($_POST['id']) ? (int)$_POST['id'] : null;

if (!$id) {
    echo json_encode(['status' => 'error', 'message' => 'Match ID is required.']);
    exit();
}

try {
    $stmt = $conn->prepare("DELETE FROM matches WHERE id = ?");
    $stmt->execute([$id]);

    if ($stmt->rowCount() === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Match not found.']);
    } else {
        echo json_encode(['status' => 'success', 'message' => 'Match deleted successfully!']);
    }
} catch (\Throwable $th) {
    echo json_encode(['status' => 'error', 'message' => 'Error deleting match: ' . $th->getMessage()]);
}
