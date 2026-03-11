<?php
session_start();
require_once '../../DB/dbConnection.php';
$conn = dbConnection();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

header('Content-Type: application/json');

$name     = trim($_POST['name']     ?? '');
$email    = trim($_POST['email']    ?? '');
$password = trim($_POST['password'] ?? '');
$confirm  = trim($_POST['confirm_password']  ?? '');

$errors = [];

if (empty($name))                              $errors[] = 'Name is required.';
if (strlen($name) < 2)                         $errors[] = 'Name must be at least 2 characters.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email address.';
if (strlen($password) < 8)                     $errors[] = 'Password must be at least 8 characters.';
if ($password !== $confirm)                    $errors[] = 'Passwords do not match.';

if (!empty($errors)) {
    echo json_encode(['status' => 'error', 'message' => implode(' ', $errors)]);
    exit;
}

$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);

if ($stmt->fetch()) {
    echo json_encode(['status' => 'error', 'message' => 'An account with this email already exists.']);
    exit;
}
try {
    $conn->beginTransaction();
    $hashed = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?);");
    $stmt->execute([$name, $email, $hashed]);

    $new_user_id = $conn->lastInsertId();

    $stmt = $conn->prepare("INSERT INTO players (user_id,name) VALUES (?, ?);");
    $stmt->execute([$new_user_id, $name]);
    $conn->commit();
} catch (\Throwable $th) {
    $conn->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'An Error Accure in register']);
    exit;
}

// 5. Auto-login after registration — By session immediately
$_SESSION['user_id']    = $new_user_id;
$_SESSION['user_name']  = $name;
$_SESSION['user_email'] = $email;

echo json_encode([
    'status'  => 'success',
    'message' => 'Account created! Welcome, ' . $name . '!',
    'name'    => $name
]);
