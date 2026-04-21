<?php
// Call this at the beginning of your API files
require_once __DIR__ . '/../includes/response.php';

// Set headers
setJsonHeader();
setCorsHeaders();

// Your code here...

// Send success response
sendSuccess($data, "Operation successful");

// Send error response
sendError("Something went wrong", 400);

// Send validation error
sendValidationError(['email' => 'Email is required'], 'Validation failed');

// Send not found
sendNotFound("Employee");

// Send unauthorized
sendUnauthorized();

// Send forbidden
sendForbidden();

// Log activity
logActivity($db, "User performed action", $userEmail);

// Sanitize input
$cleanName = sanitizeInput($_POST['name']);

// Validate email
if (isValidEmail($email)) {
    // Email is valid
}

// Generate token
$token = generateToken();

// Get JSON input
$data = getJsonInput();
?>