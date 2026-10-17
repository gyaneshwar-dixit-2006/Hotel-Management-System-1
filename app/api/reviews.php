<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

require_once '../DB.php';
$db = DB::getInstance();

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : 'list');

if ($action === 'list') {
    $stmt = $db->query("SELECT * FROM reviews WHERE status = 'Approved' ORDER BY review_id DESC LIMIT 12");
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate average rating
    $avgStmt = $db->query("SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM reviews WHERE status = 'Approved'");
    $stats = $avgStmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'data' => $reviews,
        'stats' => [
            'avg_rating' => round((float)($stats['avg_rating'] ?: 5), 1),
            'total_reviews' => (int)($stats['total_reviews'] ?: 0)
        ]
    ]);
    exit;
}

if ($action === 'submit') {
    if (!isset($_SESSION["accountEmail"])) {
        echo json_encode(['status' => 'error', 'message' => 'Please log in to submit a review.']);
        exit;
    }

    $cid = isset($_SESSION["cid"]) ? (int)$_SESSION["cid"] : 0;
    $authorName = isset($_SESSION["username"]) ? $_SESSION["username"] : "Valued Guest";
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 5;
    $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';

    if ($rating < 1 || $rating > 5) {
        $rating = 5;
    }

    if (empty($comment)) {
        echo json_encode(['status' => 'error', 'message' => 'Please share a few words about your experience.']);
        exit;
    }

    $stmt = $db->prepare("INSERT INTO reviews (customer_id, customer_name, rating, comment, status) VALUES (?, ?, ?, ?, 'Approved')");
    if ($stmt->execute([$cid, $authorName, $rating, htmlspecialchars($comment, ENT_QUOTES, 'UTF-8')])) {
        echo json_encode(['status' => 'success', 'message' => 'Thank you for your review! Your feedback helps us deliver an unforgettable experience.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Unable to save review. Please try again.']);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
