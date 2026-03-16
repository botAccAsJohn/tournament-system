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
$id            = $rows['id'];
$tournament_id = $rows['tournament_id'];

// Enforce min 2 teams per tournament
$stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM teams WHERE tournament_id = ?");
$stmt->execute([$tournament_id]);
$teamCount = (int)$stmt->fetchColumn();
if ($teamCount <= 2) {
    echo json_encode(['status' => 'error', 'message' => 'Cannot delete team: a tournament must have at least 2 teams.']);
    exit();
}

// DELETE team
$stmt = $conn->prepare("DELETE FROM teams WHERE id = ?;");
$stmt->execute([$id]);

echo json_encode([
    'status' => 'success',
    'message' => 'Team Deleted successfully!'
]);
?>