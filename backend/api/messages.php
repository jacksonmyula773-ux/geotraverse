<?php
// backend/api/messages.php
require_once '../config/database.php';
require_once '../includes/response.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    $db = getDB();
    
    if ($method === 'GET') {
        $stmt = $db->query("SELECT * FROM messages ORDER BY created_at DESC");
        $messages = $stmt->fetchAll();
        sendSuccess($messages);
    }
    
    elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data['reply_message']) || empty($data['id'])) {
            sendError('Message ID and reply are required', 400);
        }
        
        $stmt = $db->prepare("UPDATE messages SET reply_message = :reply, replied_at = NOW(), is_read = 1 WHERE id = :id");
        $stmt->execute([
            ':id' => $data['id'],
            ':reply' => $data['reply_message']
        ]);
        
        // Log activity
        $log = $db->prepare("INSERT INTO activity_logs (action, user_email) VALUES ('Replied to message ID " . $data['id'] . "', 'admin')");
        $log->execute();
        
        sendSuccess(null, 'Reply sent successfully');
    }
    
} catch(PDOException $e) {
    sendError('Database error: ' . $e->getMessage(), 500);
}
?>