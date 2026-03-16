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

// ── Input ────────────────────────────────────────────────────────────────────
$match_id    = !empty($_POST['id'])          ? (int)$_POST['id']          : null;
$team1_score = isset($_POST['team1_score'])  ? (int)$_POST['team1_score'] : null;
$team2_score = isset($_POST['team2_score'])  ? (int)$_POST['team2_score'] : null;

// ── Validate ─────────────────────────────────────────────────────────────────
$errors = [];
if (empty($match_id))             $errors[] = 'Match ID is required.';
if ($team1_score === null)        $errors[] = 'Team 1 score is required.';
if ($team2_score === null)        $errors[] = 'Team 2 score is required.';
if ($team1_score !== null && $team1_score < 0) $errors[] = 'Team 1 score cannot be negative.';
if ($team2_score !== null && $team2_score < 0) $errors[] = 'Team 2 score cannot be negative.';

if (!empty($errors)) {
    echo json_encode(['status' => 'error', 'message' => implode(' ', $errors)]);
    exit();
}

// ── Verify match exists & fetch team IDs ─────────────────────────────────────
$stmt = $conn->prepare("SELECT id, team1_id, team2_id FROM matches WHERE id = ?");
$stmt->execute([$match_id]);
$match = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$match) {
    echo json_encode(['status' => 'error', 'message' => 'Match not found.']);
    exit();
}

// ── Determine winner ─────────────────────────────────────────────────────────
if ($team1_score > $team2_score) {
    $winner_team_id = $match['team1_id'];
} elseif ($team2_score > $team1_score) {
    $winner_team_id = $match['team2_id'];
} else {
    $winner_team_id = null; // Draw
}

// ── Upsert score & update match status ───────────────────────────────────────
try {
    $conn->beginTransaction();

    // Insert or update the score row (match_id is UNIQUE in match_scores)
    $stmt = $conn->prepare("
        INSERT INTO match_scores (match_id, team1_score, team2_score)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE
            team1_score = VALUES(team1_score),
            team2_score = VALUES(team2_score)
    ");
    $stmt->execute([$match_id, $team1_score, $team2_score]);

    // Mark the match as completed and record the winner
    $stmt = $conn->prepare("
        UPDATE matches
        SET status = 'completed', winner_team_id = ?
        WHERE id = ?
    ");
    $stmt->execute([$winner_team_id, $match_id]);

    // ── Auto-complete tournament if all its matches are now done ──────────────
    // Get tournament_id for this match
    $stmt = $conn->prepare("SELECT tournament_id FROM matches WHERE id = ?");
    $stmt->execute([$match_id]);
    $tournament_id = (int)$stmt->fetchColumn();

    if ($tournament_id) {
        $stmt = $conn->prepare("
            SELECT
                COUNT(*)                                        AS total,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS done
            FROM matches
            WHERE tournament_id = ?
        ");
        $stmt->execute([$tournament_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ((int)$row['total'] > 0 && (int)$row['total'] === (int)$row['done']) {
            $stmt = $conn->prepare("UPDATE tournaments SET status = 'completed' WHERE id = ?");
            $stmt->execute([$tournament_id]);
        }
    }

    $conn->commit();

    echo json_encode([
        'status'  => 'success',
        'message' => 'Match score updated successfully!'
    ]);
} catch (\Throwable $th) {
    $conn->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Error updating score: ' . $th->getMessage()]);
}
