<?php
// backend/api/employees.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include database connection
require_once __DIR__ . '/../config/database.php';

function sendResponse($success, $message, $data = null, $code = 200) {
    http_response_code($code);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    $db = getDB();
    
    // GET - Fetch all employees
    if ($method === 'GET') {
        $stmt = $db->query("SELECT * FROM employees ORDER BY id DESC");
        $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
        sendResponse(true, 'Employees fetched successfully', $employees);
    }
    
    // POST - Create new employee
    elseif ($method === 'POST') {
        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);
        
        if (!$data) {
            sendResponse(false, 'Invalid JSON data', null, 400);
        }
        
        $name = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');
        $phone = trim($data['phone'] ?? '');
        $role = trim($data['role'] ?? '');
        $department = trim($data['department'] ?? '');
        $salary = floatval($data['salary'] ?? 0);
        
        if (empty($name) || empty($email) || empty($role) || empty($department)) {
            sendResponse(false, 'Name, email, role and department are required', null, 400);
        }
        
        // Check if email exists
        $check = $db->prepare("SELECT id FROM employees WHERE email = :email");
        $check->execute([':email' => $email]);
        if ($check->fetch()) {
            sendResponse(false, 'Employee with this email already exists', null, 400);
        }
        
        $stmt = $db->prepare("INSERT INTO employees (name, email, phone, role, department, salary, status) VALUES (:name, :email, :phone, :role, :department, :salary, 'active')");
        $result = $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':phone' => $phone,
            ':role' => $role,
            ':department' => $department,
            ':salary' => $salary
        ]);
        
        if ($result) {
            $newId = $db->lastInsertId();
            
            // Log activity
            try {
                $log = $db->prepare("INSERT INTO activity_logs (action, user_email) VALUES (:action, 'admin')");
                $log->execute([':action' => "Added new employee: $name"]);
            } catch(Exception $e) {
                // Ignore log errors
            }
            
            sendResponse(true, 'Employee created successfully', ['id' => $newId], 201);
        } else {
            sendResponse(false, 'Failed to create employee', null, 500);
        }
    }
    
    // PUT - Update employee
    elseif ($method === 'PUT') {
        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);
        
        if (!$data || empty($data['id'])) {
            sendResponse(false, 'Employee ID is required', null, 400);
        }
        
        $stmt = $db->prepare("UPDATE employees SET name = :name, email = :email, phone = :phone, role = :role, department = :department, salary = :salary WHERE id = :id");
        $stmt->execute([
            ':id' => $data['id'],
            ':name' => $data['name'],
            ':email' => $data['email'],
            ':phone' => $data['phone'] ?? '',
            ':role' => $data['role'],
            ':department' => $data['department'],
            ':salary' => $data['salary'] ?? 0
        ]);
        
        sendResponse(true, 'Employee updated successfully');
    }
    
    // DELETE - Delete employee
    elseif ($method === 'DELETE') {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if (!$id) {
            sendResponse(false, 'Employee ID is required', null, 400);
        }
        
        // Get employee name for log
        $getName = $db->prepare("SELECT name FROM employees WHERE id = :id");
        $getName->execute([':id' => $id]);
        $employee = $getName->fetch();
        
        $stmt = $db->prepare("DELETE FROM employees WHERE id = :id");
        $stmt->execute([':id' => $id]);
        
        if ($employee) {
            try {
                $log = $db->prepare("INSERT INTO activity_logs (action, user_email) VALUES (:action, 'admin')");
                $log->execute([':action' => "Deleted employee: " . $employee['name']]);
            } catch(Exception $e) {
                // Ignore
            }
        }
        
        sendResponse(true, 'Employee deleted successfully');
    }
    
    else {
        sendResponse(false, 'Method not allowed', null, 405);
    }
    
} catch(PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage(), null, 500);
} catch(Exception $e) {
    sendResponse(false, 'Server error: ' . $e->getMessage(), null, 500);
}
?>