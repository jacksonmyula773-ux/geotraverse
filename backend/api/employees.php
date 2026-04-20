<?php
// backend/api/employees.php
require_once '../config/database.php';
require_once '../includes/response.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    $db = getDB();
    
    // GET - Fetch all employees
    if ($method === 'GET') {
        $stmt = $db->query("SELECT * FROM employees ORDER BY id DESC");
        $employees = $stmt->fetchAll();
        sendSuccess($employees);
    }
    
    // POST - Create new employee
    elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data['name']) || empty($data['email']) || empty($data['role']) || empty($data['department'])) {
            sendError('Name, email, role and department are required', 400);
        }
        
        // Check if email exists
        $check = $db->prepare("SELECT id FROM employees WHERE email = :email");
        $check->execute([':email' => $data['email']]);
        if ($check->fetch()) {
            sendError('Employee with this email already exists', 400);
        }
        
        $stmt = $db->prepare("INSERT INTO employees (name, email, phone, role, department, salary, status) VALUES (:name, :email, :phone, :role, :department, :salary, 'active')");
        $stmt->execute([
            ':name' => $data['name'],
            ':email' => $data['email'],
            ':phone' => $data['phone'] ?? '',
            ':role' => $data['role'],
            ':department' => $data['department'],
            ':salary' => $data['salary'] ?? 0
        ]);
        
        // Log activity
        $log = $db->prepare("INSERT INTO activity_logs (action, user_email) VALUES ('Added new employee: " . $data['name'] . "', 'admin')");
        $log->execute();
        
        sendSuccess(['id' => $db->lastInsertId()], 'Employee created successfully', 201);
    }
    
    // PUT - Update employee
    elseif ($method === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data['id'])) {
            sendError('Employee ID is required', 400);
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
        
        sendSuccess(null, 'Employee updated successfully');
    }
    
    // DELETE - Delete employee
    elseif ($method === 'DELETE') {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if (!$id) {
            sendError('Employee ID is required', 400);
        }
        
        // Get employee name for log
        $getName = $db->prepare("SELECT name FROM employees WHERE id = :id");
        $getName->execute([':id' => $id]);
        $employee = $getName->fetch();
        
        $stmt = $db->prepare("DELETE FROM employees WHERE id = :id");
        $stmt->execute([':id' => $id]);
        
        if ($employee) {
            $log = $db->prepare("INSERT INTO activity_logs (action, user_email) VALUES ('Deleted employee: " . $employee['name'] . "', 'admin')");
            $log->execute();
        }
        
        sendSuccess(null, 'Employee deleted successfully');
    }
    
} catch(PDOException $e) {
    sendError('Database error: ' . $e->getMessage(), 500);
}
?>