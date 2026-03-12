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
    // $conn->exec(`SELECT 
    //                             t.name AS Tournament,
    //                             team1.name AS Team1,
    //                             team2.name AS Team2,
    //                             COALESCE(ms.team1_score, 0) AS Team1_Score,
    //                             COALESCE(ms.team2_score, 0) AS Team2_Score,
    //                             CASE 
    //                                 WHEN ms.team1_score > ms.team2_score THEN team1.name
    //                                 WHEN ms.team2_score > ms.team1_score THEN team2.name
    //                                 ELSE 'Draw'
    //                             END AS Result
    //                         FROM matches m
    //                         JOIN tournaments t 
    //                             ON m.tournament_id = t.id
    //                         JOIN teams team1 
    //                             ON m.team1_id = team1.id
    //                         JOIN teams team2 
    //                             ON m.team2_id = team2.id
    //                         LEFT JOIN match_scores ms 
    //                             ON m.id = ms.match_id
    //                         ORDER BY t.name, m.match_date;`);
    // $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // echo json_encode([
    //     'status' => 'success',
    //     'data'   => $matches
    // ]);

    $sql = "SELECT 
            t.name AS Tournament,
            team1.name AS Team1,
            team2.name AS Team2,
            COALESCE(ms.team1_score, 0) AS Team1_Score,
            COALESCE(ms.team2_score, 0) AS Team2_Score,
            CASE 
                WHEN ms.team1_score > ms.team2_score THEN team1.name
                WHEN ms.team2_score > ms.team1_score THEN team2.name
                ELSE 'Draw'
            END AS Result
        FROM matches m
        JOIN tournaments t ON m.tournament_id = t.id
        JOIN teams team1 ON m.team1_id = team1.id
        JOIN teams team2 ON m.team2_id = team2.id
        LEFT JOIN match_scores ms ON m.id = ms.match_id
        ORDER BY t.name, m.match_date";

    $stmt = $conn->prepare($sql);
    $stmt->execute();

    $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'data'   => $matches
    ]);
} catch (\Throwable $th) {
    echo json_encode(['status' => 'error', 'message' => 'Error fetching matches: ' . $th->getMessage()]);
}
