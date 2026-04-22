<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$uploadDir = '../uploads/projects/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}
echo json_encode(['success' => true, 'message' => 'Folder created']);
?>