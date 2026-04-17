<?php
/**
 * Admin Login API
 * Endpoint: /backend/api/admin_login.php
 * Method: POST
 * Body: { "email": "user@example.com", "password": "password" }
 */

// ============================================
// HEADERS - CORS and JSON
// ============================================
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ============================================
// ONLY POST METHOD ALLOWED
// ============================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Use POST.'
    ]);
    exit();
}

// ============================================
// GET JSON DATA FROM REQUEST BODY
// ============================================
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Check if data is valid
if (!$data) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON data'
    ]);
    exit();
}

// ============================================
// EXTRACT EMAIL AND PASSWORD
// ============================================
$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

// Validate email and password are not empty
if (empty($email) || empty($password)) {
    echo json_encode([
        'success' => false,
        'message' => 'Email and password are required'
    ]);
    exit();
}

// ============================================
// ADMIN CREDENTIALS (Hardcoded for now)
// Baadae utaunganisha na database
// ============================================
$admin_email = 'jacksonmyula773@gmail.com';
$admin_password = '1234';

// ============================================
// VERIFY CREDENTIALS
// ============================================
if ($email === $admin_email && $password === $admin_password) {
    // Start session
    session_start();
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_email'] = $email;
    $_SESSION['admin_role'] = 'admin';
    $_SESSION['login_time'] = time();
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'user' => [
            'name' => 'Admin User',
            'email' => $email,
            'role' => 'admin'
        ]
    ]);
} else {
    // Return error response
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid email or password'
    ]);
}
?>