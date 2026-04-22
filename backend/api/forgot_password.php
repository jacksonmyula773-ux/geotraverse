<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');

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
$email = $data['email'] ?? '';

if (empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Email address required']);
    exit();
}

// Check if user exists
$stmt = $pdo->prepare("SELECT id, email, full_name FROM users WHERE email = ? AND role = 'admin' AND is_active = 1");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'Email address not found']);
    exit();
}

// Generate reset token
$resetToken = bin2hex(random_bytes(32));
// CHANGE HERE: 24 hours instead of 1 hour
$expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));

// Delete old tokens
$stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
$stmt->execute([$email]);

// Save new token
$stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
$result = $stmt->execute([$email, $resetToken, $expiresAt]);

if (!$result) {
    echo json_encode(['success' => false, 'message' => 'Failed to generate reset token']);
    exit();
}

// Create reset link
$resetLink = "http://localhost/geotraverse/reset-password.html?token=" . $resetToken . "&email=" . urlencode($email);

echo json_encode([
    'success' => true, 
    'message' => 'Reset link generated successfully (valid for 24 hours)',
    'reset_link' => $resetLink,
    'token' => $resetToken
]);
?>