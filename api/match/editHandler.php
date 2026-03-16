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

$id            = !empty($_POST['id'])            ? (int)$_POST['id']            : null;
$tournament_id = !empty($_POST['tournament_id']) ? (int)$_POST['tournament_id'] : null;
$team_id_1     = !empty($_POST['team_id_1'])     ? (int)$_POST['team_id_1']     : null;
$team_id_2     = !empty($_POST['team_id_2'])     ? (int)$_POST['team_id_2']     : null;
$match_date    = trim($_POST['start_date'] ?? '');

// Validate
$errors = [];
if (empty($id))               $errors[] = 'Match ID is required.';
if (empty($tournament_id))    $errors[] = 'Tournament is required.';
if (empty($team_id_1))        $errors[] = 'Team 1 is required.';
if (empty($team_id_2))        $errors[] = 'Team 2 is required.';
if ($team_id_1 === $team_id_2) $errors[] = 'Team 1 and Team 2 must be different.';
if (empty($match_date))       $errors[] = 'Start date is required.';
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $match_date)) $errors[] = 'Invalid date format.';

if (!empty($errors)) {
    echo json_encode(['status' => 'error', 'message' => implode(' ', $errors)]);
    exit();
}

$stmt = $conn->prepare("SELECT id FROM matches WHERE id = ?");
$stmt->execute([$id]);
$match = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$match) {
    echo json_encode(['status' => 'error', 'message' => 'Match not found.']);
    exit();
}

// Validate start_date is within tournament bounds
$stmt = $conn->prepare("SELECT start_date, end_date FROM tournaments WHERE id = ?");
$stmt->execute([$tournament_id]);
$tournament = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tournament) {
    echo json_encode(['status' => 'error', 'message' => 'Tournament not found.']);
    exit();
}

if ($match_date < $tournament['start_date'] || $match_date > $tournament['end_date']) {
    echo json_encode(['status' => 'error', 'message' => 'Start date must be within the tournament dates.']);
    exit();
}

// Check both teams belong to the tournament
$stmt = $conn->prepare("SELECT id FROM teams WHERE id = ? AND tournament_id = ?");
$stmt->execute([$team_id_1, $tournament_id]);
if (!$stmt->fetch()) {
    echo json_encode(['status' => 'error', 'message' => 'Team 1 does not belong to the selected tournament.']);
    exit();
}

$stmt->execute([$team_id_2, $tournament_id]);
if (!$stmt->fetch()) {
    echo json_encode(['status' => 'error', 'message' => 'Team 2 does not belong to the selected tournament.']);
    exit();
}

// Check both teams have at least 5 players
$stmt = $conn->prepare("
    SELECT t.id, t.name, COUNT(p.id) AS player_count
    FROM teams t
    LEFT JOIN players p ON p.team_id = t.id
    WHERE t.id IN (?, ?)
    GROUP BY t.id, t.name
    HAVING COUNT(p.id) < 5
");
$stmt->execute([$team_id_1, $team_id_2]);
$shortTeams = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (!empty($shortTeams)) {
    $names = implode(', ', array_column($shortTeams, 'name'));
    echo json_encode(['status' => 'error', 'message' => "The following team(s) have fewer than 5 players and cannot be scheduled: {$names}."]);
    exit();
}

// Check for duplicate match in same tournament (exclude the match being edited)
$stmt = $conn->prepare("
    SELECT id FROM matches 
    WHERE tournament_id = ? 
      AND ((team1_id = ? AND team2_id = ?) OR (team1_id = ? AND team2_id = ?))
      AND id != ?
");
$stmt->execute([$tournament_id, $team_id_1, $team_id_2, $team_id_2, $team_id_1, $id]);
if ($stmt->fetch()) {
    echo json_encode(['status' => 'error', 'message' => 'A match between these two teams already exists in this tournament.']);
    exit();
}

// Check same-day double booking for either team (exclude the current match)
$stmt = $conn->prepare("
    SELECT id FROM matches
    WHERE match_date = ?
      AND (team1_id IN (?,?) OR team2_id IN (?,?))
      AND id != ?
");
$stmt->execute([$match_date, $team_id_1, $team_id_2, $team_id_1, $team_id_2, $id]);
if ($stmt->fetch()) {
    echo json_encode(['status' => 'error', 'message' => 'One or both teams already have a match scheduled on this date.']);
    exit();
}

try {
    $stmt = $conn->prepare("
        UPDATE matches 
        SET tournament_id = ?, team1_id = ?, team2_id = ?, match_date = ?
        WHERE id = ?;");
    $stmt->execute([$tournament_id, $team_id_1, $team_id_2, $match_date, $id]);

    echo json_encode([
        'status'  => 'success',
        'message' => 'Match updated successfully!'
    ]);
} catch (\Throwable $th) {
    echo json_encode(['status' => 'error', 'message' => 'Error updating match: ' . $th->getMessage()]);
}
