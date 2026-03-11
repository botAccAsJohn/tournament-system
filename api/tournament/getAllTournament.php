<?php
session_start();
require_once '../../DB/dbConnection.php';
$conn = dbConnection();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    exit();
}

header('Content-Type: application/json');

// Validate
$errors = [];

if (!empty($errors)) {
    echo json_encode(['status' => 'error', 'message' => implode(' ', $errors)]);
    exit();
}

// Check duplicate name
$result = $conn->query("SELECT * FROM tournaments;");
if ($result->rowCount() === 0) {
    echo json_encode(['status' => 'success', 'message' => 'No tournaments found', 'data' => []]);
    exit();
} else {
    $rows = $result->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode([
    'status'  => 'success',
    'message' => 'Tournament Fetch successfully!',
    'data' => $rows
]);
}

// Insert
// $stmt = $conn->prepare("
//     INSERT INTO tournaments (name, start_date, end_date, type, status)
//     VALUES (?, ?, ?, ?, ?)
// ");
// $stmt->execute([$name, $start_date, $end_date, $type, $status]);

// echo json_encode([
//     'status'  => 'success',
//     'message' => 'Tournament "' . $name . '" created successfully!'
// ]);
?>