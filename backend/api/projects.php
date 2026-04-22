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
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        $stmt = $pdo->query("SELECT * FROM projects ORDER BY id DESC");
        $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Add full image URL
        foreach ($projects as &$project) {
            if (!empty($project['image_path'])) {
                $project['image_url'] = 'http://localhost/geotraverse/' . $project['image_path'];
            } else {
                $project['image_url'] = '';
            }
        }
        
        echo json_encode(['success' => true, 'data' => $projects]);
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            echo json_encode(['success' => false, 'message' => 'Invalid data received']);
            exit();
        }
        
        $stmt = $pdo->prepare("INSERT INTO projects (name, client_name, client_phone, client_email, description, amount, location, status, progress, start_date, end_date, image_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $result = $stmt->execute([
            $data['name'] ?? '',
            $data['client_name'] ?? '',
            $data['client_phone'] ?? '',
            $data['client_email'] ?? '',
            $data['description'] ?? '',
            $data['amount'] ?? 0,
            $data['location'] ?? '',
            $data['status'] ?? 'pending',
            $data['progress'] ?? 0,
            $data['start_date'] ?? null,
            $data['end_date'] ?? null,
            $data['image_path'] ?? ''
        ]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Project added successfully', 'id' => $pdo->lastInsertId()]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add project']);
        }
        break;
        
    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data || !isset($data['id'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid data received']);
            exit();
        }
        
        $stmt = $pdo->prepare("UPDATE projects SET name = ?, client_name = ?, client_phone = ?, client_email = ?, description = ?, amount = ?, location = ?, status = ?, progress = ?, start_date = ?, end_date = ?, image_path = ? WHERE id = ?");
        $result = $stmt->execute([
            $data['name'] ?? '',
            $data['client_name'] ?? '',
            $data['client_phone'] ?? '',
            $data['client_email'] ?? '',
            $data['description'] ?? '',
            $data['amount'] ?? 0,
            $data['location'] ?? '',
            $data['status'] ?? 'pending',
            $data['progress'] ?? 0,
            $data['start_date'] ?? null,
            $data['end_date'] ?? null,
            $data['image_path'] ?? '',
            $data['id']
        ]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Project updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update project']);
        }
        break;
        
    case 'DELETE':
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID required']);
            exit();
        }
        
        // Get image path to delete file
        $stmt = $pdo->prepare("SELECT image_path FROM projects WHERE id = ?");
        $stmt->execute([$id]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($project && !empty($project['image_path'])) {
            $imageFile = '../' . $project['image_path'];
            if (file_exists($imageFile)) {
                unlink($imageFile);
            }
        }
        
        $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
        $result = $stmt->execute([$id]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Project deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete project']);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Method not supported']);
}
?>