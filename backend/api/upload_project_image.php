<?php
// backend/api/upload_project_image.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$uploadDir = '../../uploads/projects/';

// Create directory if not exists
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if (isset($_FILES['image'])) {
    $file = $_FILES['image'];
    $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9\._-]/', '', $file['name']);
    $targetFile = $uploadDir . $fileName;
    
    // Check if image file is actual image
    $check = getimagesize($file['tmp_name']);
    if ($check === false) {
        echo json_encode(['success' => false, 'message' => 'File is not an image']);
        exit();
    }
    
    // Check file size (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'File too large (max 5MB)']);
        exit();
    }
    
    // Allow certain file formats
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
        echo json_encode(['success' => false, 'message' => 'Only JPG, JPEG, PNG, GIF & WEBP files are allowed']);
        exit();
    }
    
    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        $imageUrl = '/geotraverse/uploads/projects/' . $fileName;
        echo json_encode(['success' => true, 'image_url' => $imageUrl, 'message' => 'Image uploaded successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to upload image']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No file uploaded']);
}
?>