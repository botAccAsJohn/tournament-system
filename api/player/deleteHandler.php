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

$id = $_POST['id'] ?? null;

if (empty($id)) {
    echo json_encode(['status' => 'error', 'message' => 'Player ID is required.']);
    exit();
}

try {
    $conn->beginTransaction();
    
    // Get user_id and team_id associated with this player
    $stmt = $conn->prepare("SELECT user_id, team_id FROM players WHERE id = ?");
    $stmt->execute([$id]);
    $player = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$player) {
        throw new Exception("Player not found.");
    }
    
    // Enforce min 5 players per team
    if (!empty($player['team_id'])) {
        $stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM players WHERE team_id = ?");
        $stmt->execute([$player['team_id']]);
        $count = (int)$stmt->fetchColumn();
        if ($count <= 5) {
            $conn->rollBack();
            echo json_encode(['status' => 'error', 'message' => 'Cannot delete player: a team must have at least 5 players. Remove the player from the team first, or ensure the team retains 5 members.']);
            exit();
        }
    }
    
    $user_id = $player['user_id'];
    
    $stmt = $conn->prepare("DELETE FROM players WHERE id = ?");
    $stmt->execute([$id]);
    
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    
    $conn->commit();
    
    echo json_encode([
        'status'  => 'success',
        'message' => 'Player and associated user account deleted successfully!'
    ]);
} catch (\Throwable $th) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => 'Error deleting player: ' . $th->getMessage()]);
}
?>
