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
    
    // Get user_id associated with this player
    $stmt = $conn->prepare("SELECT user_id FROM players WHERE id = ?");
    $stmt->execute([$id]);
    $player = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$player) {
        throw new Exception("Player not found.");
    }
    
    $user_id = $player['user_id'];
    
    // Deleting the user will cascade to the player (if FK is ON DELETE CASCADE)
    // or we can delete both explicitly to be safe.
    
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
