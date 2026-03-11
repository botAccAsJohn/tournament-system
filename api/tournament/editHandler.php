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
$type = trim($_POST['type'] ?? '');
$start_date = trim($_POST['start_date'] ?? '');
$end_date = trim($_POST['end_date'] ?? '');
$status = trim($_POST['status'] ?? 'upcoming');

// Validate
$errors = [];

if (empty($id))
    $errors[] = 'Tournament id is required.';
if (empty($name))
    $errors[] = 'Tournament name is required.';
if (strlen($name) < 3)
    $errors[] = 'Name must be at least 3 characters.';
if (!in_array($type, ['league', 'knockout']))
    $errors[] = 'Invalid tournament type.';
if (empty($start_date))
    $errors[] = 'Start date is required.';
if (empty($end_date))
    $errors[] = 'End date is required.';
if ($end_date < $start_date)
    $errors[] = 'End date must be on or after start date.';
if (!in_array($status, ['upcoming', 'ongoing', 'completed']))
    $errors[] = 'Invalid status.';

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
// update tournament
$stmt = $conn->prepare("
    UPDATE tournaments 
    SET name = ?, start_date = ?, end_date = ?, type =?, status=?
    WHERE id = ?;
");
$stmt->execute([$name, $start_date, $end_date, $type, $status, $id]);

echo json_encode([
    'status' => 'success',
    'message' => 'Tournament "' . $name . '" Updated successfully!'
]);
?>