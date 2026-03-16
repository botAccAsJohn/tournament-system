<?php
session_start();
require_once '../../DB/dbConnection.php';
$conn = dbConnection();
header('Content-Type: application/json');

// Both admin and player can view winners
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    exit();
}

try {
    // Fetch all tournaments (not just completed) so UI can show "in progress" too
    $stmt = $conn->prepare("
        SELECT id, name, type, status,
            (SELECT COUNT(*) FROM matches m WHERE m.tournament_id = tournaments.id) AS total_matches,
            (SELECT COUNT(*) FROM matches m WHERE m.tournament_id = tournaments.id AND m.status = 'completed') AS completed_matches
        FROM tournaments
        ORDER BY name
    ");
    $stmt->execute();
    $tournaments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $results = [];

    foreach ($tournaments as $t) {
        $winner = null;

        if ($t['type'] === 'league') {
            // League: team with highest points among completed matches
            $s = $conn->prepare("
                SELECT
                    tm.id   AS team_id,
                    tm.name AS team_name,
                    SUM(CASE
                        WHEN (m.team1_id = tm.id AND ms.team1_score > ms.team2_score)
                          OR (m.team2_id = tm.id AND ms.team2_score > ms.team1_score) THEN 2
                        WHEN ms.team1_score IS NOT NULL
                         AND ms.team1_score = ms.team2_score THEN 1
                        ELSE 0
                    END) AS points
                FROM teams tm
                LEFT JOIN matches m
                    ON  (m.team1_id = tm.id OR m.team2_id = tm.id)
                    AND m.tournament_id = ?
                    AND m.status = 'completed'
                LEFT JOIN match_scores ms ON ms.match_id = m.id
                WHERE tm.tournament_id = ?
                GROUP BY tm.id, tm.name
                ORDER BY points DESC
                LIMIT 1
            ");
            $s->execute([$t['id'], $t['id']]);
            $winner = $s->fetch(PDO::FETCH_ASSOC);

        } else {
            // Knockout: winner of the last completed match that has a winner
            $s = $conn->prepare("
                SELECT tm.id AS team_id, tm.name AS team_name
                FROM matches m
                JOIN teams tm ON tm.id = m.winner_team_id
                WHERE m.tournament_id = ?
                  AND m.winner_team_id IS NOT NULL
                ORDER BY m.match_date DESC
                LIMIT 1
            ");
            $s->execute([$t['id']]);
            $winner = $s->fetch(PDO::FETCH_ASSOC);
        }

        $results[] = [
            'tournament_id'         => $t['id'],
            'tournament_name'       => $t['name'],
            'type'                  => $t['type'],
            'status'                => $t['status'],
            'total_matches'         => (int)$t['total_matches'],
            'completed_matches'     => (int)$t['completed_matches'],
            'winner'                => $winner,
        ];
    }

    echo json_encode(['status' => 'success', 'data' => $results]);
} catch (\Throwable $th) {
    echo json_encode(['status' => 'error', 'message' => 'Error fetching winners: ' . $th->getMessage()]);
}
