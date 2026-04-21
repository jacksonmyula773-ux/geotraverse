<?php
// backend/api/projects.php
require_once __DIR__ . '/../config/database.php';
require_once '../includes/response.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    $db = getDB();
    
    if ($method === 'GET') {
        $stmt = $db->query("SELECT * FROM projects ORDER BY id DESC");
        $projects = $stmt->fetchAll();
        sendSuccess($projects);
    }
    
    elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data['name'])) {
            sendError('Project name is required', 400);
        }
        
        $stmt = $db->prepare("INSERT INTO projects (name, client_name, amount, status, progress) VALUES (:name, :client, :amount, :status, :progress)");
        $stmt->execute([
            ':name' => $data['name'],
            ':client' => $data['client_name'] ?? '',
            ':amount' => $data['amount'] ?? 0,
            ':status' => $data['status'] ?? 'pending',
            ':progress' => $data['progress'] ?? 0
        ]);
        
        sendSuccess(['id' => $db->lastInsertId()], 'Project created successfully', 201);
    }
    
    elseif ($method === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data['id'])) {
            sendError('Project ID is required', 400);
        }
        
        $stmt = $db->prepare("UPDATE projects SET name = :name, client_name = :client, amount = :amount, status = :status, progress = :progress WHERE id = :id");
        $stmt->execute([
            ':id' => $data['id'],
            ':name' => $data['name'],
            ':client' => $data['client_name'] ?? '',
            ':amount' => $data['amount'] ?? 0,
            ':status' => $data['status'] ?? 'pending',
            ':progress' => $data['progress'] ?? 0
        ]);
        
        sendSuccess(null, 'Project updated successfully');
    }
    
    elseif ($method === 'DELETE') {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if (!$id) {
            sendError('Project ID is required', 400);
        }
        
        $stmt = $db->prepare("DELETE FROM projects WHERE id = :id");
        $stmt->execute([':id' => $id]);
        
        sendSuccess(null, 'Project deleted successfully');
    }
    
} catch(PDOException $e) {
    sendError('Database error: ' . $e->getMessage(), 500);
}
?>