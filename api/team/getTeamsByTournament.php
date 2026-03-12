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
if (!isset($_POST['tournament_id']) || $_POST['tournament_id'] === '') {
    echo json_encode(['status' => 'error', 'message' => 'Tournament ID is required.']);
    exit();
}
$t_id = filter_var($_POST['tournament_id'], FILTER_SANITIZE_NUMBER_INT);
if ($t_id === false) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Tournament ID.']);
    exit();
}

// check the tournament is exist !!
$stmt = $conn->prepare("SELECT name FROM tournaments WHERE id = ?");
$stmt->execute([$t_id]);
if ($stmt->rowCount() === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Tournament not found.']);
    exit();
}

$sql = "SELECT 
        teams.id,
        teams.name
        FROM teams
        WHERE teams.tournament_id = ?;";

$stmt = $conn->prepare($sql);
$stmt->execute([$t_id]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (!$rows) {
    echo json_encode(['status' => 'success', 'message' => 'No teams found', 'data' => []]);
    exit();
} else {
    echo json_encode([
        'status' => 'success',
        'message' => 'Teams Fetch successfully!',
        'data' => $rows
    ]);
}
