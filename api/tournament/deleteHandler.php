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

$id         = trim($_POST['id']       ?? '');

// Validate
$errors = [];

if (empty($id)) $errors[] = 'Tournament id is required.';


if (!empty($errors)) {
    echo json_encode(['status' => 'error', 'message' => implode(' ', $errors)]);
    exit();
}

// Check tournament is exist
$stmt = $conn->prepare("SELECT * FROM tournaments WHERE id = ?");
$stmt->execute([$id]);
$rows = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$rows) {
    echo json_encode(['status' => 'error', 'message' => 'A tournament with this data not exists.']);
    exit();
}
$id = $rows['id'];

// Block deletion if the tournament already has matches (data integrity)
$stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM matches WHERE tournament_id = ?");
$stmt->execute([$id]);
$matchCount = (int)$stmt->fetchColumn();
if ($matchCount > 0) {
    echo json_encode(['status' => 'error', 'message' => "Cannot delete tournament: it has {$matchCount} match(es). Delete the matches first."]);
    exit();
}

// DELETE tournament
$stmt = $conn->prepare("DELETE FROM tournaments WHERE id = ?;");
$stmt->execute([$id]);

echo json_encode([
    'status'  => 'success',
    'message' => 'Tournament Deleted successfully!'
]);
?>