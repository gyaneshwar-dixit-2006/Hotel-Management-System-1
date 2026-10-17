<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

require_once '../DB.php';
$db = DB::getInstance();

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : 'list_menu');

if ($action === 'list_menu') {
    $stmt = $db->query("SELECT * FROM room_service_menu WHERE is_available = 1 ORDER BY category, price ASC");
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['status' => 'success', 'data' => $items]);
    exit;
}

if ($action === 'place_order') {
    if (!isset($_SESSION["accountEmail"])) {
        echo json_encode(['status' => 'error', 'message' => 'Please log in to place a room service order.']);
        exit;
    }

    $cid = isset($_SESSION["cid"]) ? (int)$_SESSION["cid"] : 0;
    if ($cid <= 0) {
        $stmt = $db->prepare("SELECT cid FROM customer WHERE email = ?");
        $stmt->execute([$_SESSION["accountEmail"]]);
        $cid = (int)$stmt->fetchColumn();
    }

    $bookingId = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;
    $roomNumber = isset($_POST['room_number']) ? trim($_POST['room_number']) : 'Room (Front Desk)';
    $itemsJson = isset($_POST['items_json']) ? $_POST['items_json'] : '[]';
    $totalAmount = isset($_POST['total_amount']) ? (double)$_POST['total_amount'] : 0.0;
    $specialInstructions = isset($_POST['instructions']) ? trim($_POST['instructions']) : '';

    if ($totalAmount <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Please select at least one item from the menu.']);
        exit;
    }

    $sql = "INSERT INTO room_service_orders (booking_id, customer_id, room_number, items_json, total_amount, special_instructions, status) VALUES (?, ?, ?, ?, ?, ?, 'Placed')";
    $stmt = $db->prepare($sql);
    $success = $stmt->execute([$bookingId, $cid, $roomNumber, $itemsJson, $totalAmount, $specialInstructions]);

    if ($success) {
        $orderId = $db->lastInsertId();
        echo json_encode(['status' => 'success', 'message' => "Order #{$orderId} placed successfully! Our chef is preparing your delicacies.", 'order_id' => $orderId]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Unable to place order. Please try again.']);
    }
    exit;
}

if ($action === 'get_orders') {
    $isAdmin = false;
    if (isset($_SESSION["isAdmin"]) && (
        (is_array($_SESSION["isAdmin"]) && ($_SESSION["isAdmin"][0] == 1 || $_SESSION["isAdmin"][1] === "true")) ||
        $_SESSION["isAdmin"] === 1 || $_SESSION["isAdmin"] === true
    )) {
        $isAdmin = true;
    }

    if ($isAdmin) {
        $sql = "SELECT o.*, c.fullname, c.email, c.phone 
                FROM room_service_orders o 
                LEFT JOIN customer c ON o.customer_id = c.cid 
                ORDER BY o.order_id DESC";
        $stmt = $db->query($sql);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $orders]);
    } elseif (isset($_SESSION["accountEmail"])) {
        $stmt = $db->prepare("SELECT o.* FROM room_service_orders o JOIN customer c ON o.customer_id = c.cid WHERE c.email = ? ORDER BY o.order_id DESC");
        $stmt->execute([$_SESSION["accountEmail"]]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $orders]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    }
    exit;
}

if ($action === 'update_order_status') {
    $isAdmin = false;
    if (isset($_SESSION["isAdmin"]) && (
        (is_array($_SESSION["isAdmin"]) && ($_SESSION["isAdmin"][0] == 1 || $_SESSION["isAdmin"][1] === "true")) ||
        $_SESSION["isAdmin"] === 1 || $_SESSION["isAdmin"] === true
    )) {
        $isAdmin = true;
    }

    if (!$isAdmin) {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Admin required']);
        exit;
    }

    $orderId = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
    $status = isset($_POST['status']) ? trim($_POST['status']) : '';

    $stmt = $db->prepare("UPDATE room_service_orders SET status = ? WHERE order_id = ?");
    if ($stmt->execute([$status, $orderId])) {
        echo json_encode(['status' => 'success', 'message' => "Order #{$orderId} status updated to {$status}."]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update order status.']);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
