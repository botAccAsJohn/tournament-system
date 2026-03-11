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


$name = trim($_POST['name'] ?? '');
$id = trim($_POST['id'] ?? '');


// Validate
$errors = [];



if (empty($id) ||  $id < 1) { //!filter_var($id, FILTER_VALIDATE_INT) ||
    $errors[] = 'A valid Tournament is required.';
}
if (empty($name)) $errors[] = 'Team name is required.';
if (strlen($name) < 3)  $errors[] = 'Name must be at least 3 characters.';


if (!empty($errors)) {
    echo json_encode(['status' => 'error', 'message' => implode(' ', $errors)]);
    exit();
}

// Check Tournament exist with given id
$stmt = $conn->prepare("SELECT * FROM tournaments WHERE id = ?;");
$stmt->execute([$id]);
$tournament = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tournament) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Tournament ID .']);
    exit();
}
if ($tournament['status'] == 'completed') {
    echo json_encode(['status' => 'error', 'message' => 'Cannot add teams to a completed tournament.']);
    exit();
}

// Check duplicate name
$stmt = $conn->prepare("SELECT id FROM teams WHERE name = ? AND tournament_id = ?;");
$stmt->execute([$name,$id]);
if ($stmt->fetch()) {
    echo json_encode(['status' => 'error', 'message' => 'A Team with this name already exists this Tournament .']);
    exit();
}

// Insert
$stmt = $conn->prepare("INSERT INTO teams (name, tournament_id) VALUES (?, ?);");
$stmt->execute([$name, $id]);

echo json_encode([
    'status'  => 'success',
    'message' => 'Team "' . $name . '" created successfully!'
]);
?>