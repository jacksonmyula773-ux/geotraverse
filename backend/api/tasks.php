<?php
// backend/api/tasks.php
require_once '../config/database.php';
require_once '../includes/response.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    $db = getDB();
    
    if ($method === 'GET') {
        $stmt = $db->query("SELECT * FROM tasks ORDER BY id DESC");
        $tasks = $stmt->fetchAll();
        sendSuccess($tasks);
    }
    
    elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data['title'])) {
            sendError('Task title is required', 400);
        }
        
        $stmt = $db->prepare("INSERT INTO tasks (title, description, department, completed) VALUES (:title, :description, :department, 0)");
        $stmt->execute([
            ':title' => $data['title'],
            ':description' => $data['description'] ?? '',
            ':department' => $data['department'] ?? 'General'
        ]);
        
        // Log activity
        $log = $db->prepare("INSERT INTO activity_logs (action, user_email) VALUES ('Added new task: " . $data['title'] . "', 'admin')");
        $log->execute();
        
        sendSuccess(['id' => $db->lastInsertId()], 'Task created successfully', 201);
    }
    
    elseif ($method === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data['id'])) {
            sendError('Task ID is required', 400);
        }
        
        $stmt = $db->prepare("UPDATE tasks SET completed = :completed WHERE id = :id");
        $stmt->execute([
            ':id' => $data['id'],
            ':completed' => $data['completed'] ? 1 : 0
        ]);
        
        sendSuccess(null, 'Task updated successfully');
    }
    
    elseif ($method === 'DELETE') {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if (!$id) {
            sendError('Task ID is required', 400);
        }
        
        $stmt = $db->prepare("DELETE FROM tasks WHERE id = :id");
        $stmt->execute([':id' => $id]);
        
        sendSuccess(null, 'Task deleted successfully');
    }
    
} catch(PDOException $e) {
    sendError('Database error: ' . $e->getMessage(), 500);
}
?>