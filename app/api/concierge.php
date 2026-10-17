<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

$query = isset($_POST['message']) ? trim($_POST['message']) : '';
if (empty($query)) {
    echo json_encode(['reply' => 'Namaste! How may I assist your stay at Grand Lux Hotel today? You can ask about room prices, recommendations, dining menu, or check-in rules.']);
    exit;
}

$qLower = strtolower($query);

// Knowledge base and AI rule patterns
$reply = "";
$action = null;

if (preg_match('/(single|solo|budget|sasta|1 person|one person|executive)/i', $qLower)) {
    $reply = "For solo travelers or business trips, our **Classic Cozy Single (₹12,030/night)** is ideal. It includes a plush twin bed, high-speed fiber Wi-Fi, work desk, and Italian rain shower. If you prefer extra space, our **Executive Single Suite (₹13,500/night)** comes with panoramic city views and an in-room espresso machine.";
    $action = ['type' => 'recommend_room', 'room' => 'Single'];
} elseif (preg_match('/(deluxe|luxury|presidential|jacuzzi|honeymoon|anniversary|romantic|vip)/i', $qLower)) {
    $reply = "For the ultimate luxury experience, our **Presidential Deluxe Suite (₹23,135/night)** features elevated penthouse ceilings, private jacuzzi, panoramic skyline windows, and complimentary butler service. Or indulge in our **Royal Penthouse Suite (₹29,500/night)** with exclusive VIP lounge access, private cocktail bar, and chauffeur service!";
    $action = ['type' => 'recommend_room', 'room' => 'Deluxe'];
} elseif (preg_match('/(double|couple|two person|2 person|friends|standard)/i', $qLower)) {
    $reply = "For couples and pairs, our **Grand Standard Double (₹16,657/night)** offers twin or king bed configurations, Egyptian cotton linens, dual vanity bathroom, and complimentary breakfast. Or upgrade to the **Deluxe Double Terrace (₹18,500/night)** with private garden terrace and deep-soaking bathtub.";
    $action = ['type' => 'recommend_room', 'room' => 'Double'];
} elseif (preg_match('/(check\s*in|check\s*out|timing|time|samay|arrive|departure)/i', $qLower)) {
    $reply = "Standard **Check-in time is 2:00 PM** and **Check-out time is 11:00 AM**. Early check-in or late check-out is available on request depending on room availability. We also offer 24/7 express contactless QR check-in!";
} elseif (preg_match('/(food|dinner|breakfast|lunch|room service|khana|menu|restaurant|coffee)/i', $qLower)) {
    $reply = "Our in-house 24/7 dining offers gourmet delicacies! Popular favorites include our **Continental Royale Breakfast (₹650)**, **Pan-seared Norwegian Salmon (₹1,450)**, **Wood-Fired Truffle Pizza (₹980)**, and authentic **Butter Chicken with Garlic Naan (₹850)**. You can order room service directly from your dashboard!";
    $action = ['type' => 'view_menu'];
} elseif (preg_match('/(wifi|internet|pool|gym|spa|amenities|facility|parking|airport|cab)/i', $qLower)) {
    $reply = "Grand Lux features 5-star amenities: Ultra-fast Gigabit Wi-Fi in all suites, rooftop infinity pool, 24/7 state-of-the-art fitness center, luxury Ayurvedic spa, valet parking, and luxury airport chauffeur transfers on request.";
} elseif (preg_match('/(cancel|refund|policy|rules)/i', $qLower)) {
    $reply = "Cancellations made at least 24 hours prior to the check-in date are eligible for a 100% full refund. You can review or cancel your reservations directly under **View my bookings**.";
} elseif (preg_match('/(hi|hello|hey|namaste|kaisa|help|madad)/i', $qLower)) {
    $reply = "Hello & Welcome to Grand Lux Hotel & Suites! I am your AI Virtual Concierge. I can help you find the best room, provide price estimates, recommend dining items, or check your reservation details. What are you looking for today?";
} elseif (preg_match('/(price|rate|cost|kitna|discount|offer)/i', $qLower)) {
    $reply = "Our suite tariffs start at:\n- **Single Suite:** ₹12,030 / night\n- **Double Room:** ₹16,657 / night\n- **Deluxe Penthouse:** ₹23,135 / night\nAll bookings include complimentary high-speed Wi-Fi, swimming pool & gym access, and digital QR express pass!";
} else {
    $reply = "Thank you for asking! Grand Lux Hotel is an ultra-luxury boutique hotel offering personalized hospitality, gourmet dining, and digital contactless stays. Would you like me to recommend a room for your upcoming dates, or explore our room service menu?";
}

echo json_encode([
    'status' => 'success',
    'reply' => $reply,
    'action' => $action
], JSON_UNESCAPED_UNICODE);
