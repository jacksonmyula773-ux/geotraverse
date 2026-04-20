<?php
// backend/api/admin_dashboard.php
require_once '../config/database.php';
require_once '../includes/response.php';

// Get token from header (simplified for now)
$headers = getallheaders();
$token = str_replace('Bearer ', '', $headers['Authorization'] ?? '');

if (empty($token)) {
    // For demo, allow without token
    // sendError('Authentication required', 401);
}

try {
    $db = getDB();
    
    // Get counts
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
    
    sendSuccess([
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
    ]);
    
} catch(PDOException $e) {
    sendError('Failed to load dashboard: ' . $e->getMessage(), 500);
}
?>