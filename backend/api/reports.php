<?php
// backend/api/reports.php
require_once __DIR__ . '/../config/database.php';
require_once '../includes/response.php';

$department = $_GET['department'] ?? '';
$period = $_GET['period'] ?? 'daily';

if (empty($department)) {
    sendError('Department is required', 400);
}

try {
    $db = getDB();
    $data = [];
    
    if ($department === 'Managers') {
        // Get project stats
        $completed = $db->query("SELECT COUNT(*) as count FROM projects WHERE status = 'completed'")->fetch()['count'];
        $inProgress = $db->query("SELECT COUNT(*) as count FROM projects WHERE status = 'in_progress'")->fetch()['count'];
        $total = $db->query("SELECT COUNT(*) as count FROM projects")->fetch()['count'];
        $revenue = $db->query("SELECT COALESCE(SUM(amount), 0) as total FROM projects WHERE status = 'completed'")->fetch()['total'];
        
        $data = [
            'completed_projects' => (int)$completed,
            'in_progress_projects' => (int)$inProgress,
            'total_projects' => (int)$total,
            'total_revenue' => (float)$revenue
        ];
    }
    elseif ($department === 'Finance') {
        $income = $db->query("SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE type = 'income'")->fetch()['total'];
        $expenses = $db->query("SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE type = 'expense'")->fetch()['total'];
        $pendingInvoices = $db->query("SELECT COUNT(*) as count FROM invoices WHERE status = 'pending'")->fetch()['count'];
        
        $data = [
            'total_income' => (float)$income,
            'total_expenses' => (float)$expenses,
            'net_profit' => (float)($income - $expenses),
            'pending_invoices' => (int)$pendingInvoices
        ];
    }
    elseif ($department === 'Sales') {
        $orders = $db->query("SELECT COUNT(*) as count FROM orders")->fetch()['count'];
        $customers = $db->query("SELECT COUNT(*) as count FROM customers")->fetch()['count'];
        
        $data = [
            'total_orders' => (int)$orders,
            'revenue' => 0,
            'new_leads' => (int)$customers,
            'conversions' => 0
        ];
    }
    elseif ($department === 'Secretary') {
        $messages = $db->query("SELECT COUNT(*) as count FROM messages")->fetch()['count'];
        $appointments = $db->query("SELECT COUNT(*) as count FROM appointments")->fetch()['count'];
        
        $data = [
            'messages' => (int)$messages,
            'appointments' => (int)$appointments,
            'documents' => 0,
            'pending_tasks' => 0
        ];
    }
    else {
        sendError('Invalid department', 400);
    }
    
    sendSuccess($data);
    
} catch(PDOException $e) {
    sendError('Failed to load report: ' . $e->getMessage(), 500);
}
?>