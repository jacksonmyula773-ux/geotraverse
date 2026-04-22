<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database connection
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

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        $stmt = $pdo->query("SELECT id, name, email, phone, role, department, salary, status FROM employees ORDER BY id DESC");
        $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $employees]);
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            echo json_encode(['success' => false, 'message' => 'Invalid data received']);
            exit();
        }
        
        $stmt = $pdo->prepare("INSERT INTO employees (name, email, phone, role, department, salary, status) VALUES (?, ?, ?, ?, ?, ?, 'active')");
        $result = $stmt->execute([
            $data['name'], 
            $data['email'], 
            $data['phone'] ?? '', 
            $data['role'], 
            $data['department'], 
            $data['salary']
        ]);
        
        if ($result) {
            $newId = $pdo->lastInsertId();
            echo json_encode(['success' => true, 'message' => 'Employee added successfully', 'id' => $newId]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add employee']);
        }
        break;
        
    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data || !isset($data['id'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid data received']);
            exit();
        }
        
        $stmt = $pdo->prepare("UPDATE employees SET name = ?, email = ?, phone = ?, role = ?, department = ?, salary = ? WHERE id = ?");
        $result = $stmt->execute([
            $data['name'], 
            $data['email'], 
            $data['phone'] ?? '', 
            $data['role'], 
            $data['department'], 
            $data['salary'],
            $data['id']
        ]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Employee updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update employee']);
        }
        break;
        
    case 'DELETE':
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID required']);
            exit();
        }
        
        $stmt = $pdo->prepare("DELETE FROM employees WHERE id = ?");
        $result = $stmt->execute([$id]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Employee deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete employee']);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Method not supported']);
}
?>