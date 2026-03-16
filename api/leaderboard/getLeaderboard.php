<?php
session_start();
require_once '../../DB/dbConnection.php';
$conn = dbConnection();
header('Content-Type: application/json');

// Both admin and player can view the leaderboard
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    exit();
}

$tournament_id = !empty($_GET['tournament_id']) ? (int)$_GET['tournament_id'] : null;

try {
    $where  = $tournament_id ? 'AND t.id = ?' : '';
    $params = $tournament_id ? [$tournament_id] : [];

    $sql = "
        SELECT
            t.id            AS tournament_id,
            t.name          AS tournament_name,
            t.type          AS tournament_type,
            tm.id           AS team_id,
            tm.name         AS team_name,
            COUNT(m.id)     AS matches_played,

            SUM(CASE
                WHEN (m.team1_id = tm.id AND ms.team1_score > ms.team2_score)
                  OR (m.team2_id = tm.id AND ms.team2_score > ms.team1_score)
                THEN 1 ELSE 0
            END) AS wins,

            SUM(CASE
                WHEN (m.team1_id = tm.id AND ms.team1_score < ms.team2_score)
                  OR (m.team2_id = tm.id AND ms.team2_score < ms.team1_score)
                THEN 1 ELSE 0
            END) AS losses,

            SUM(CASE
                WHEN ms.team1_score IS NOT NULL
                 AND ms.team1_score = ms.team2_score
                THEN 1 ELSE 0
            END) AS draws,

            SUM(CASE
                WHEN (m.team1_id = tm.id AND ms.team1_score > ms.team2_score)
                  OR (m.team2_id = tm.id AND ms.team2_score > ms.team1_score) THEN 2
                WHEN ms.team1_score IS NOT NULL
                 AND ms.team1_score = ms.team2_score THEN 1
                ELSE 0
            END) AS points

        FROM teams tm
        JOIN tournaments t  ON tm.tournament_id = t.id
        LEFT JOIN matches m
            ON  (m.team1_id = tm.id OR m.team2_id = tm.id)
            AND m.tournament_id = t.id
            AND m.status = 'completed'
        LEFT JOIN match_scores ms ON ms.match_id = m.id
        WHERE 1=1 $where
        GROUP BY t.id, t.name, t.type, tm.id, tm.name
        ORDER BY t.name ASC, points DESC, wins DESC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $rows]);
} catch (\Throwable $th) {
    echo json_encode(['status' => 'error', 'message' => 'Error fetching leaderboard: ' . $th->getMessage()]);
}
