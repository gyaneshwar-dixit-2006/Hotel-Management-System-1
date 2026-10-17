<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

$isAdmin = false;
if (isset($_SESSION["isAdmin"]) && (
    (is_array($_SESSION["isAdmin"]) && ($_SESSION["isAdmin"][0] == 1 || $_SESSION["isAdmin"][1] === "true")) ||
    $_SESSION["isAdmin"] === 1 || $_SESSION["isAdmin"] === true
)) {
    $isAdmin = true;
}

if (!$isAdmin) {
    echo json_encode(['status' => 'error', 'message' => 'Admin authorization required.']);
    exit;
}

require_once '../DB.php';
$db = DB::getInstance();

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : 'dashboard_stats');

if ($action === 'dashboard_stats') {
    // 1. Total revenue
    $revStmt = $db->query("SELECT COALESCE(SUM(total_price), 0) FROM pricing");
    $totalRevenue = (float)$revStmt->fetchColumn();

    // 2. Reservation counts
    $resStmt = $db->query("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN UPPER(status) = 'PENDING' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN UPPER(status) IN ('CONFIRMED', 'CHECKED_IN') THEN 1 ELSE 0 END) as confirmed,
        SUM(CASE WHEN UPPER(status) = 'CANCELLED' THEN 1 ELSE 0 END) as cancelled
        FROM booking");
    $counts = $resStmt->fetch(PDO::FETCH_ASSOC);

    // 3. Customers count
    $custStmt = $db->query("SELECT COUNT(*) FROM customer");
    $totalCustomers = (int)$custStmt->fetchColumn();

    // 4. Rooms count & occupancy
    $roomStmt = $db->query("SELECT 
        COUNT(*) as total_rooms,
        SUM(CASE WHEN status = 'Available' THEN 1 ELSE 0 END) as available_rooms,
        SUM(CASE WHEN status = 'Occupied' THEN 1 ELSE 0 END) as occupied_rooms
        FROM rooms");
    $roomStats = $roomStmt->fetch(PDO::FETCH_ASSOC);

    // 5. Room service orders count
    $ordersStmt = $db->query("SELECT 
        COUNT(*) as total_orders,
        SUM(CASE WHEN status = 'Placed' THEN 1 ELSE 0 END) as active_orders,
        COALESCE(SUM(total_amount), 0) as food_revenue
        FROM room_service_orders");
    $orderStats = $ordersStmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'data' => [
            'revenue' => $totalRevenue + (float)$orderStats['food_revenue'],
            'room_revenue' => $totalRevenue,
            'fnb_revenue' => (float)$orderStats['food_revenue'],
            'bookings' => [
                'total' => (int)$counts['total'],
                'pending' => (int)$counts['pending'],
                'confirmed' => (int)$counts['confirmed'],
                'cancelled' => (int)$counts['cancelled'],
            ],
            'customers' => $totalCustomers,
            'rooms' => [
                'total' => (int)$roomStats['total_rooms'],
                'available' => (int)$roomStats['available_rooms'],
                'occupied' => (int)$roomStats['occupied_rooms'],
            ],
            'room_service' => [
                'total' => (int)$orderStats['total_orders'],
                'active' => (int)$orderStats['active_orders']
            ]
        ]
    ]);
    exit;
}

if ($action === 'list_rooms') {
    $stmt = $db->query("SELECT * FROM rooms ORDER BY room_number ASC");
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['status' => 'success', 'data' => $rooms]);
    exit;
}

if ($action === 'update_room_status') {
    $roomId = isset($_POST['room_id']) ? (int)$_POST['room_id'] : 0;
    $status = isset($_POST['status']) ? trim($_POST['status']) : 'Available';

    $stmt = $db->prepare("UPDATE rooms SET status = ? WHERE room_id = ?");
    if ($stmt->execute([$status, $roomId])) {
        echo json_encode(['status' => 'success', 'message' => "Room status updated to {$status}."]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update room.']);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
