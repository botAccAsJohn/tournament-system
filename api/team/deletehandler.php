<?php
session_start();
require_once '../../DB/dbConnection.php';
$conn = dbConnection();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit();
}

$id = trim($_POST['id'] ?? '');

// Validate
$errors = [];

if (empty($id))
    $errors[] = 'team id is required.';


if (!empty($errors)) {
    echo json_encode(['status' => 'error', 'message' => implode(' ', $errors)]);
    exit();
}

// Check tournament is exist
$stmt = $conn->prepare("SELECT * FROM teams WHERE id = ?");
$stmt->execute([$id]);
$rows = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$rows) {
    echo json_encode(['status' => 'error', 'message' => 'A team with this data not exists.']);
    exit();
}
$id = $rows['id'];

// DELETE tournament
$stmt = $conn->prepare("DELETE FROM teams WHERE id = ?;");
$stmt->execute([$id]);

echo json_encode([
    'status' => 'success',
    'message' => 'Team Deleted successfully!'
]);
?>