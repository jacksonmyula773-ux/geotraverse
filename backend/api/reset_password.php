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
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$token = $data['token'] ?? '';
$email = $data['email'] ?? '';
$newPassword = $data['password'] ?? '';

// Debug log
error_log("Reset attempt - Token: " . substr($token, 0, 20) . "...");
error_log("Reset attempt - Email: " . $email);

if (empty($token) || empty($email) || empty($newPassword)) {
    echo json_encode(['success' => false, 'message' => 'Token, email and password required']);
    exit();
}

if (strlen($newPassword) < 4) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 4 characters']);
    exit();
}

// First, check if token exists and is valid
$stmt = $pdo->prepare("SELECT * FROM password_resets WHERE email = ? AND token = ? AND expires_at > NOW()");
$stmt->execute([$email, $token]);
$reset = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reset) {
    // Check if token exists but expired
    $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE email = ? AND token = ?");
    $stmt->execute([$email, $token]);
    $anyToken = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($anyToken) {
        echo json_encode(['success' => false, 'message' => 'Reset link has expired. Please request a new one.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid reset token. Please request a new reset link.']);
    }
    exit();
}

// Update password
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
$stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
$stmt->execute([$hashedPassword, $email]);

// Delete all tokens for this email
$stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
$stmt->execute([$email]);

echo json_encode(['success' => true, 'message' => 'Password reset successfully']);
?>