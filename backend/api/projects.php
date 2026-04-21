<?php
// backend/api/projects.php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/database.php';

function sendResponse($success, $message, $data = null, $code = 200) {
    http_response_code($code);
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    $db = getDB();
    
    // GET - Fetch all projects
    if ($method === 'GET') {
        $stmt = $db->query("SELECT * FROM projects ORDER BY id DESC");
        $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
        sendResponse(true, 'Projects fetched successfully', $projects);
    }
    
    // POST - Create new project
    elseif ($method === 'POST') {
        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);
        
        if (!$data || empty($data['name'])) {
            sendResponse(false, 'Project name is required', 400);
        }
        
        $stmt = $db->prepare("INSERT INTO projects (name, client_name, client_phone, client_email, description, amount, status, progress, image_url, location, start_date, end_date) 
                              VALUES (:name, :client_name, :client_phone, :client_email, :description, :amount, :status, :progress, :image_url, :location, :start_date, :end_date)");
        
        $stmt->execute([
            ':name' => $data['name'],
            ':client_name' => $data['client_name'] ?? '',
            ':client_phone' => $data['client_phone'] ?? '',
            ':client_email' => $data['client_email'] ?? '',
            ':description' => $data['description'] ?? '',
            ':amount' => $data['amount'] ?? 0,
            ':status' => $data['status'] ?? 'pending',
            ':progress' => $data['progress'] ?? 0,
            ':image_url' => $data['image_url'] ?? '',
            ':location' => $data['location'] ?? '',
            ':start_date' => $data['start_date'] ?? null,
            ':end_date' => $data['end_date'] ?? null
        ]);
        
        sendResponse(true, 'Project created successfully', ['id' => $db->lastInsertId()], 201);
    }
    
    // PUT - Update project
    elseif ($method === 'PUT') {
        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);
        
        if (!$data || empty($data['id'])) {
            sendResponse(false, 'Project ID is required', 400);
        }
        
        $stmt = $db->prepare("UPDATE projects SET name = :name, client_name = :client_name, client_phone = :client_phone, client_email = :client_email, description = :description, amount = :amount, status = :status, progress = :progress, image_url = :image_url, location = :location, start_date = :start_date, end_date = :end_date WHERE id = :id");
        
        $stmt->execute([
            ':id' => $data['id'],
            ':name' => $data['name'],
            ':client_name' => $data['client_name'] ?? '',
            ':client_phone' => $data['client_phone'] ?? '',
            ':client_email' => $data['client_email'] ?? '',
            ':description' => $data['description'] ?? '',
            ':amount' => $data['amount'] ?? 0,
            ':status' => $data['status'] ?? 'pending',
            ':progress' => $data['progress'] ?? 0,
            ':image_url' => $data['image_url'] ?? '',
            ':location' => $data['location'] ?? '',
            ':start_date' => $data['start_date'] ?? null,
            ':end_date' => $data['end_date'] ?? null
        ]);
        
        sendResponse(true, 'Project updated successfully');
    }
    
    // DELETE - Delete project
    elseif ($method === 'DELETE') {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if (!$id) {
            sendResponse(false, 'Project ID is required', 400);
        }
        
        $stmt = $db->prepare("DELETE FROM projects WHERE id = :id");
        $stmt->execute([':id' => $id]);
        
        sendResponse(true, 'Project deleted successfully');
    }
    
    else {
        sendResponse(false, 'Method not allowed', 405);
    }
    
} catch(PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage(), 500);
}
?>