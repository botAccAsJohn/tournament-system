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

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    exit();
}

$sql = "SELECT players.id, players.name, players.team_id, teams.name as team_name, users.email 
        FROM players 
        LEFT JOIN teams ON players.team_id = teams.id
        LEFT JOIN users ON players.user_id = users.id;";

$result = $conn->query($sql);
if ($result->rowCount() === 0) {
    echo json_encode(['status' => 'success', 'message' => 'No players found', 'data' => []]);
    exit();
} else {
    $rows = $result->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode([
        'status' => 'success',
        'message' => 'Players fetched successfully!',
        'data' => $rows
    ]);
}
