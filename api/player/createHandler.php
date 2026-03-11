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

$name       = trim($_POST['name'] ?? '');
$email      = trim($_POST['email'] ?? '');
$password   = trim($_POST['password'] ?? '');
$team_id    = !empty($_POST['team_id']) ? (int)$_POST['team_id'] : null;

// Validate
$errors = [];
if (empty($name))                          $errors[] = 'Name is required.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email address.';
if (strlen($password) < 8)                 $errors[] = 'Password must be at least 8 characters.';

if (!empty($errors)) {
    echo json_encode(['status' => 'error', 'message' => implode(' ', $errors)]);
    exit();
}

// Check duplicate email
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    echo json_encode(['status' => 'error', 'message' => 'An account with this email already exists.']);
    exit();
}

try {
    $conn->beginTransaction();
    
    $hashed = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'player');");
    $stmt->execute([$name, $email, $hashed]);
    
    $new_user_id = $conn->lastInsertId();
    
    $stmt = $conn->prepare("INSERT INTO players (user_id, name, team_id) VALUES (?, ?, ?);");
    $stmt->execute([$new_user_id, $name, $team_id]);
    
    $conn->commit();
    
    echo json_encode([
        'status'  => 'success',
        'message' => 'Player "' . $name . '" created successfully!'
    ]);
} catch (\Throwable $th) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => 'Error creating player: ' . $th->getMessage()]);
}
?>
