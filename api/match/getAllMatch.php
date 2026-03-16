<?php
session_start();
require_once '../../DB/dbConnection.php';
$conn = dbConnection();

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    exit();
}

header('Content-Type: application/json');

try {
    $stmt = $conn->prepare("
        SELECT 
            m.id,
            t.name        AS tournament_name,
            t1.name       AS team1_name,
            t2.name       AS team2_name,
            m.match_date,
            m.status,
            w.name        AS winner_team_name
        FROM matches m
        JOIN tournaments t  ON m.tournament_id  = t.id
        JOIN teams t1       ON m.team1_id        = t1.id
        JOIN teams t2       ON m.team2_id        = t2.id
        LEFT JOIN teams w   ON m.winner_team_id  = w.id
        ORDER BY m.match_date DESC
    ");
    $stmt->execute();
    $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'data'   => $matches
    ]);
} catch (\Throwable $th) {
    echo json_encode(['status' => 'error', 'message' => 'Error fetching matches: ' . $th->getMessage()]);
}
