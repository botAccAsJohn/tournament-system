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

$id         = !empty($_POST['id'])         ? (int)$_POST['id']         : null;
$match_date = trim($_POST['match_date'] ?? '');

if (!$id) {
    echo json_encode(['status' => 'error', 'message' => 'Match ID is required.']);
    exit();
}
if (!$match_date) {
    echo json_encode(['status' => 'error', 'message' => 'Match date is required.']);
    exit();
}
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $match_date)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid date format.']);
    exit();
}

// Validate date is within tournament bounds
$stmt = $conn->prepare("
    SELECT t.start_date, t.end_date 
    FROM matches m 
    JOIN tournaments t ON m.tournament_id = t.id 
    WHERE m.id = ?
");
$stmt->execute([$id]);
$tournament = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tournament) {
    echo json_encode(['status' => 'error', 'message' => 'Match not found.']);
    exit();
}

if ($match_date < $tournament['start_date'] || $match_date > $tournament['end_date']) {
    echo json_encode(['status' => 'error', 'message' => 'Date must be within the tournament dates.']);
    exit();
}

try {
    $stmt = $conn->prepare("UPDATE matches SET match_date = ? WHERE id = ?");
    $stmt->execute([$match_date, $id]);
    echo json_encode(['status' => 'success', 'message' => 'Match updated successfully!']);
} catch (\Throwable $th) {
    echo json_encode(['status' => 'error', 'message' => 'Error updating match: ' . $th->getMessage()]);
}
