<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$host = 'localhost';
$dbname = 'geotraverse_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id']) || !isset($data['password'])) {
    echo json_encode(['success' => false, 'message' => 'ID and password required']);
    exit();
}

$id = $data['id'];
$newPassword = password_hash($data['password'], PASSWORD_DEFAULT);

// Check if employee has a user account
$stmt = $pdo->prepare("SELECT user_id FROM employees WHERE id = ?");
$stmt->execute([$id]);
$employee = $stmt->fetch(PDO::FETCH_ASSOC);

if ($employee && $employee['user_id']) {
    // Update user password
    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
    $stmt->execute([$newPassword, $employee['user_id']]);
    echo json_encode(['success' => true, 'message' => 'Password changed successfully']);
} else {
    // Demo mode - just return success
    echo json_encode(['success' => true, 'message' => 'Password changed successfully (Demo)']);
}
?>