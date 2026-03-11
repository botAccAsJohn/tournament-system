<?php
require_once __DIR__ . '/../dbConnection.php';

try {
    $conn = dbConnection();
    echo "Connected to database successfully.\n";

    // 1. Seed Tournaments
    echo "Seeding tournaments...\n";
    $tournamentsData = json_decode(file_get_contents(__DIR__ . '/tournament.json'), true);
    $stmt = $conn->prepare("INSERT IGNORE INTO tournaments (name, start_date, end_date, type, status) VALUES (?, ?, ?, ?, ?)");
    foreach ($tournamentsData as $t) {
        $stmt->execute([$t['name'], $t['start_date'], $t['end_date'], $t['type'], $t['status']]);
    }

    // 2. Seed Teams
    echo "Seeding teams...\n";
    $teamsData = json_decode(file_get_contents(__DIR__ . '/team.json'), true);
    foreach ($teamsData as $group) {
        $stmtT = $conn->prepare("SELECT id FROM tournaments WHERE name = ?");
        $stmtT->execute([$group['tournament_name']]);
        $tournament = $stmtT->fetch(PDO::FETCH_ASSOC);

        if ($tournament) {
            $stmtInsert = $conn->prepare("INSERT IGNORE INTO teams (tournament_id, name) VALUES (?, ?)");
            foreach ($group['teams'] as $teamName) {
                $stmtInsert->execute([$tournament['id'], $teamName]);
            }
        }
    }

    // 3. Seed Players and Users
    echo "Seeding users and players...\n";
    $playersData = json_decode(file_get_contents(__DIR__ . '/player.json'), true);
    $hashedPassword = password_hash('password123', PASSWORD_BCRYPT);

    foreach ($playersData as $p) {
        // Create User
        $stmtUser = $conn->prepare("INSERT IGNORE INTO users (name, email, password, role) VALUES (?, ?, ?, 'player')");
        $stmtUser->execute([$p['name'], $p['email'], $hashedPassword]);
        
        $userId = $conn->lastInsertId();
        if (!$userId) {
            // Probably already exists, get the ID
            $stmtUserGet = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmtUserGet->execute([$p['email']]);
            $userId = $stmtUserGet->fetchColumn();
        }

        // Get Team ID
        $teamId = null;
        if (!empty($p['team_name'])) {
            $stmtTeam = $conn->prepare("SELECT id FROM teams WHERE name = ?");
            $stmtTeam->execute([$p['team_name']]);
            $teamId = $stmtTeam->fetchColumn() ?: null;
        }

        // Create Player
        $stmtPlayer = $conn->prepare("INSERT IGNORE INTO players (user_id, team_id, name) VALUES (?, ?, ?)");
        $stmtPlayer->execute([$userId, $teamId, $p['name']]);
    }

    echo "Seeding completed successfully!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
