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

$id         = $_POST['id'] ?? null;
$name       = trim($_POST['name'] ?? '');
$team_id    = !empty($_POST['team_id']) ? (int)$_POST['team_id'] : null;

if (empty($id) || empty($name)) {
    echo json_encode(['status' => 'error', 'message' => 'ID and Name are required.']);
    exit();
}

try {
    $conn->beginTransaction();
    
    // Get user_id associated with this player
    $stmt = $conn->prepare("SELECT user_id FROM players WHERE id = ?");
    $stmt->execute([$id]);
    $player = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$player) {
        throw new Exception("Player not found.");
    }
    
    $user_id = $player['user_id'];
    
    // Update users table name
    $stmt = $conn->prepare("UPDATE users SET name = ? WHERE id = ?");
    $stmt->execute([$name, $user_id]);
    
    // Update players table
    $stmt = $conn->prepare("UPDATE players SET name = ?, team_id = ? WHERE id = ?");
    $stmt->execute([$name, $team_id, $id]);
    
    $conn->commit();
    
    echo json_encode([
        'status'  => 'success',
        'message' => 'Player updated successfully!'
    ]);
} catch (\Throwable $th) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => 'Error updating player: ' . $th->getMessage()]);
}
?>
