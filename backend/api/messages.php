<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

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
    die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]));
}

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        $stmt = $pdo->query("SELECT * FROM messages ORDER BY id DESC");
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $messages]);
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        if (isset($data['reply_message'])) {
            // This is a reply
            $stmt = $pdo->prepare("UPDATE messages SET reply_message = ?, replied_at = NOW() WHERE id = ?");
            $stmt->execute([$data['reply_message'], $data['id']]);
            echo json_encode(['success' => true, 'message' => 'Reply sent']);
        } else {
            // New message
            $stmt = $pdo->prepare("INSERT INTO messages (from_department, subject, message) VALUES (?, ?, ?)");
            $stmt->execute([$data['from_department'], $data['subject'], $data['message']]);
            echo json_encode(['success' => true, 'message' => 'Message sent', 'id' => $pdo->lastInsertId()]);
        }
        break;
        
    case 'DELETE':
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        if ($id) {
            $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Message deleted']);
        } else {
            echo json_encode(['success' => false, 'message' => 'ID required']);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Method not supported']);
}
?>