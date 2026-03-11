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

$name       = trim($_POST['name']       ?? '');
$type       = trim($_POST['type']       ?? '');
$start_date = trim($_POST['start_date'] ?? '');
$end_date   = trim($_POST['end_date']   ?? '');
$status     = trim($_POST['status']     ?? 'upcoming');

// Validate
$errors = [];

if (empty($name))                          $errors[] = 'Tournament name is required.';
if (strlen($name) < 3)                     $errors[] = 'Name must be at least 3 characters.';
if (!in_array($type, ['league','knockout']))$errors[] = 'Invalid tournament type.';
if (empty($start_date))                    $errors[] = 'Start date is required.';
if (empty($end_date))                      $errors[] = 'End date is required.';
if ($end_date < $start_date)               $errors[] = 'End date must be on or after start date.';
if (!in_array($status, ['upcoming','ongoing','completed'])) $errors[] = 'Invalid status.';

if (!empty($errors)) {
    echo json_encode(['status' => 'error', 'message' => implode(' ', $errors)]);
    exit();
}

// Check duplicate name
$stmt = $conn->prepare("SELECT id FROM tournaments WHERE name = ?");
$stmt->execute([$name]);
if ($stmt->fetch()) {
    echo json_encode(['status' => 'error', 'message' => 'A tournament with this name already exists.']);
    exit();
}

// Insert
$stmt = $conn->prepare("
    INSERT INTO tournaments (name, start_date, end_date, type, status)
    VALUES (?, ?, ?, ?, ?)
");
$stmt->execute([$name, $start_date, $end_date, $type, $status]);

echo json_encode([
    'status'  => 'success',
    'message' => 'Tournament "' . $name . '" created successfully!'
]);
?>