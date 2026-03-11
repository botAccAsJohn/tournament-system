<?php
// session_start();
// include_once("dbConnection.php");
// $conn = dbConnection();

// if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
// $err = array();
// $password = $_POST['password'];
// $email = $_POST['email'];
// if(empty($password) || empty($email) ) {
//     array_push($err,'All fields must be fill !');
// }
// if(filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
//     array_push($err,'Enter Valid Email !');
// }

// $sql = "SELECT * FROM users WHERE email = '$email' AND password = '$password';";
// $result = $conn->query($sql);
// $data = $result->fetchAll(PDO::FETCH_ASSOC);
// if(count($data) == 0) {
//     array_push($err,"NO USER FOUND !!");
// }else{
//     $_SESSION['user_id'] = $data[0]['id'];
//     $_SESSION['name'] = $data[0]['name'];
//     header("Location: welcome.php");
//     exit();
// }

// if(count($err) > 0){
//     $_SESSION['errors']   = $err;
//         $_SESSION['old_input'] = [
//             'name'  => $name,
//             'email' => $email,
//         ];
//         header("Location: login.php");
//         exit();
// }
// }

?>


<?php
session_start();
require_once '../../DB/dbConnection.php';
$conn = dbConnection();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

header('Content-Type: application/json');

$email       = trim($_POST['email']       ?? '');
$password    = trim($_POST['password']    ?? '');

// Validate
if (empty($email) || empty($password)) {
    echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid email format.']);
    exit;
}

$stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || $_POST['role'] !== $user['role']) {
    echo json_encode(['status' => 'error', 'message' => 'User Role Not Match !!']);
    exit;
}
if (!$user || !password_verify($password, $user['password'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid email or password.']);
    exit;
}

// Set session
$_SESSION['user_id']    = $user['id'];
$_SESSION['user_name']  = $user['name'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['user_role'] = $user['role'];

echo json_encode([
    'status'  => 'success',
    'message' => 'Welcome back, ' . $user['name'] . '!',
    'name'    => $user['name']
]);