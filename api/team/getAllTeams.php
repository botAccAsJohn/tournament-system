<?php
session_start();
require_once '../../DB/dbConnection.php';
$conn = dbConnection();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    exit();
}

header('Content-Type: application/json');

$sql = "SELECT teams.id, teams.name, teams.tournament_id, tournaments.name as tournament_name FROM teams INNER JOIN tournaments ON teams.tournament_id = tournaments.id;";

// Check duplicate name
$result = $conn->query($sql);
if ($result->rowCount() === 0) {
    echo json_encode(['status' => 'success', 'message' => 'No teams found', 'data' => []]);
    exit();
} else {
    $rows = $result->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode([
        'status' => 'success',
        'message' => 'Tournament Fetch successfully!',
        'data' => $rows
    ]);
}

?>