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

$tournament_id = !empty($_POST['tournament_id']) ? (int)$_POST['tournament_id'] : null;

if (!$tournament_id) {
    echo json_encode(['status' => 'error', 'message' => 'Tournament ID is required.']);
    exit();
}

// Verify tournament exists, is league type, and get its date range
$stmt = $conn->prepare("SELECT id, name, type, start_date, end_date FROM tournaments WHERE id = ?");
$stmt->execute([$tournament_id]);
$tournament = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tournament) {
    echo json_encode(['status' => 'error', 'message' => 'Tournament not found.']);
    exit();
}
if ($tournament['type'] !== 'league') {
    echo json_encode(['status' => 'error', 'message' => 'Auto-generate is only available for League type tournaments.']);
    exit();
}

// Fetch all teams for this tournament
$stmt = $conn->prepare("SELECT id, name FROM teams WHERE tournament_id = ? ORDER BY id");
$stmt->execute([$tournament_id]);
$teams = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($teams) < 2) {
    echo json_encode(['status' => 'error', 'message' => 'Tournament needs at least 2 teams to generate matches.']);
    exit();
}

// Check all teams have at least 5 players
$teamIds   = array_column($teams, 'id');
$placeholders = implode(',', array_fill(0, count($teamIds), '?'));
$stmt = $conn->prepare("
    SELECT t.id, t.name, COUNT(p.id) AS player_count
    FROM teams t
    LEFT JOIN players p ON p.team_id = t.id
    WHERE t.id IN ({$placeholders})
    GROUP BY t.id, t.name
    HAVING COUNT(p.id) < 5
");
$stmt->execute($teamIds);
$shortTeams = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (!empty($shortTeams)) {
    $names = implode(', ', array_column($shortTeams, 'name'));
    echo json_encode(['status' => 'error', 'message' => "Cannot generate matches: the following team(s) have fewer than 5 players: {$names}."]);
    exit();
}

// Build all unique pairs (combinations, not permutations)
$pairs = [];
for ($i = 0; $i < count($teams); $i++) {
    for ($j = $i + 1; $j < count($teams); $j++) {
        $pairs[] = [$teams[$i]['id'], $teams[$j]['id']];
    }
}

// Check which pairs already have a match in this tournament
$stmt = $conn->prepare("SELECT team1_id, team2_id FROM matches WHERE tournament_id = ?");
$stmt->execute([$tournament_id]);
$existing = $stmt->fetchAll(PDO::FETCH_ASSOC);

$existingSet = [];
foreach ($existing as $m) {
    $key1 = $m['team1_id'] . '_' . $m['team2_id'];
    $key2 = $m['team2_id'] . '_' . $m['team1_id'];
    $existingSet[$key1] = true;
    $existingSet[$key2] = true;
}

$toCreate = array_filter($pairs, function($pair) use ($existingSet) {
    $key = $pair[0] . '_' . $pair[1];
    return !isset($existingSet[$key]);
});

if (empty($toCreate)) {
    echo json_encode(['status' => 'error', 'message' => 'All possible league matches already exist for this tournament.']);
    exit();
}

// Distribute match dates evenly across the tournament window
$start    = new DateTime($tournament['start_date']);
$end      = new DateTime($tournament['end_date']);
$interval = $start->diff($end)->days;
$count    = count($toCreate);
$step     = $count > 1 ? (int)floor($interval / ($count - 1)) : 0;

try {
    $conn->beginTransaction();

    $stmt = $conn->prepare("
        INSERT INTO matches (tournament_id, team1_id, team2_id, match_date)
        VALUES (?, ?, ?, ?)
    ");

    $i = 0;
    foreach ($toCreate as $pair) {
        $date = clone $start;
        if ($step > 0) $date->modify("+{$i} day" . ($i * $step > 1 ? 's' : ''));
        // Clamp to tournament end
        if ($date > $end) $date = clone $end;
        $stmt->execute([$tournament_id, $pair[0], $pair[1], $date->format('Y-m-d')]);
        $i++;
    }

    $conn->commit();

    echo json_encode([
        'status'  => 'success',
        'message' => count($toCreate) . ' match(es) generated for "' . $tournament['name'] . '".',
        'created' => count($toCreate)
    ]);
} catch (\Throwable $th) {
    $conn->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Error generating matches: ' . $th->getMessage()]);
}
