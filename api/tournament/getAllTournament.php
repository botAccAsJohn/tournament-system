<?php
session_start();
require_once '../../DB/dbConnection.php';
$conn = dbConnection();

if (!isset($_SESSION['user_id'])) {
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

    $stmt = $conn->prepare("SELECT id, name, type, start_date, end_date, status FROM tournaments ORDER BY created_at DESC");
    $stmt->execute();
    if ($stmt->rowCount() === 0) {
        echo json_encode(['status' => 'success', 'message' => 'No tournaments found', 'data' => []]);
        exit();
    }
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode([
        'status'  => 'success',
        'message' => 'Tournaments fetched successfully!',
        'data'    => $rows
    ]);

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