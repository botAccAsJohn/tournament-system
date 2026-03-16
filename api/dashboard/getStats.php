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

try {
    $stats = [];

    $queries = [
        'tournaments'         => "SELECT COUNT(*) FROM tournaments",
        'teams'               => "SELECT COUNT(*) FROM teams",
        'players'             => "SELECT COUNT(*) FROM players",
        'matches_total'       => "SELECT COUNT(*) FROM matches",
        'matches_completed'   => "SELECT COUNT(*) FROM matches WHERE status = 'completed'",
        'matches_scheduled'   => "SELECT COUNT(*) FROM matches WHERE status = 'scheduled'",
        'tournaments_ongoing' => "SELECT COUNT(*) FROM tournaments WHERE status = 'ongoing'",
        'tournaments_upcoming'=> "SELECT COUNT(*) FROM tournaments WHERE status = 'upcoming'",
        'tournaments_completed'=> "SELECT COUNT(*) FROM tournaments WHERE status = 'completed'",
    ];

    foreach ($queries as $key => $sql) {
        $stats[$key] = (int)$conn->query($sql)->fetchColumn();
    }

    echo json_encode(['status' => 'success', 'data' => $stats]);
} catch (\Throwable $th) {
    echo json_encode(['status' => 'error', 'message' => 'Error fetching stats: ' . $th->getMessage()]);
}
