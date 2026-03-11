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
$name = trim($_POST['name'] ?? '');
$t_id = trim($_POST['tournament_id'] ?? '');

$errors = [];

if (empty($id) || !filter_var($id, FILTER_VALIDATE_INT) || $id < 1)
    $errors[] = 'Valid team ID is required.';

if (empty($name))
    $errors[] = 'Team name is required.';
elseif (strlen($name) < 3)
    $errors[] = 'Name must be at least 3 characters.';

if (empty($t_id) || !filter_var($t_id, FILTER_VALIDATE_INT) || $t_id < 1)
    $errors[] = 'Valid tournament ID is required.';

if (!empty($errors)) {
    echo json_encode(['status' => 'error', 'message' => implode(' ', $errors)]);
    exit();
}

// Check tournament exists and is not completed
$stmt = $conn->prepare("SELECT id, status FROM tournaments WHERE id = ?");
$stmt->execute([$t_id]);
$tournament = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$tournament) {
    echo json_encode(['status' => 'error', 'message' => 'Tournament not found.']);
    exit();
}
if ($tournament['status'] === 'completed') {
    echo json_encode(['status' => 'error', 'message' => 'Cannot edit a team in a completed tournament.']);
    exit();
}

// Check team exists and belongs to this tournament
$stmt = $conn->prepare("SELECT id, name, tournament_id FROM teams WHERE id = ?"); // ✅ correct table
$stmt->execute([$id]);
$team = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$team) {
    echo json_encode(['status' => 'error', 'message' => 'Team not found.']);
    exit();
}


// Check duplicate name in same tournament (exclude self)
$stmt = $conn->prepare("SELECT id FROM teams WHERE name = ? AND tournament_id = ? AND id != ?"); // ✅
$stmt->execute([$name, $t_id, $id]);
if ($stmt->fetch()) {
    echo json_encode(['status' => 'error', 'message' => 'A team with this name already exists in this tournament.']);
    exit();
}

// Update
$stmt = $conn->prepare("UPDATE teams SET name = ?, tournament_id = ? WHERE id = ?");
$stmt->execute([$name, $t_id, $id]);

echo json_encode([
    'status' => 'success',
    'message' => 'Team "' . $name . '" updated successfully!'
]);
