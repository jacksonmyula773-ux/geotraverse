<?php
// backend/api/admin_dashboard.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../config/database.php';

try {
    $db = getDB();
    
    // Get counts from database
    $totalEmployees = $db->query("SELECT COUNT(*) as count FROM employees")->fetch()['count'];
    $pendingTasks = $db->query("SELECT COUNT(*) as count FROM tasks WHERE completed = 0")->fetch()['count'];
    $unreadMessages = $db->query("SELECT COUNT(*) as count FROM messages WHERE is_read = 0")->fetch()['count'];
    $totalProjects = $db->query("SELECT COUNT(*) as count FROM projects")->fetch()['count'];
    
    // Get financial stats
    $income = $db->query("SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE type = 'income'")->fetch()['total'];
    $expenses = $db->query("SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE type = 'expense'")->fetch()['total'];
    $totalRevenue = $income - $expenses;
    
    // Get invoice count
    $totalInvoices = $db->query("SELECT COUNT(*) as count FROM invoices")->fetch()['count'];
    $totalOrders = $db->query("SELECT COUNT(*) as count FROM orders")->fetch()['count'];
    $totalAppointments = $db->query("SELECT COUNT(*) as count FROM appointments")->fetch()['count'];
    
    // Get recent activities
    $activities = $db->query("SELECT action, created_at as time FROM activity_logs ORDER BY created_at DESC LIMIT 5")->fetchAll();
    
    $response = [
        'success' => true,
        'data' => [
            'stats' => [
                'total_employees' => (int)$totalEmployees,
                'pending_tasks' => (int)$pendingTasks,
                'unread_messages' => (int)$unreadMessages,
                'total_projects' => (int)$totalProjects,
                'total_revenue' => 'TZS ' . number_format($totalRevenue),
                'total_invoices' => (int)$totalInvoices,
                'total_orders' => (int)$totalOrders,
                'total_meetings' => (int)$totalAppointments
            ],
            'recent_activities' => $activities
        ]
    ];
    
    echo json_encode($response);
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>