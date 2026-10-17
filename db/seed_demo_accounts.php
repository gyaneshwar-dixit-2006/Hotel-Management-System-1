<?php
$pdo = new PDO('mysql:host=localhost;port=3306;dbname=hotel;charset=utf8mb4', 'root', 'root', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

// 1. Check or insert guest@hotel.com
$guestHash = password_hash('guest123', PASSWORD_BCRYPT);
$stmt = $pdo->prepare("SELECT cid FROM customer WHERE email = ?");
$stmt->execute(['guest@hotel.com']);
if (!$stmt->fetch()) {
    $stmt = $pdo->prepare("INSERT INTO customer (fullname, email, password, phone, isadmin) VALUES (?, ?, ?, ?, 0)");
    $stmt->execute(['Guest Demo', 'guest@hotel.com', $guestHash, '+91 9876543210']);
    echo "Added guest@hotel.com\n";
} else {
    $stmt = $pdo->prepare("UPDATE customer SET password = ? WHERE email = ?");
    $stmt->execute([$guestHash, 'guest@hotel.com']);
    echo "Updated guest@hotel.com\n";
}

// 2. Check or insert admin@hotel.com
$adminHash = password_hash('admin123', PASSWORD_BCRYPT);
$stmt = $pdo->prepare("SELECT cid FROM customer WHERE email = ?");
$stmt->execute(['admin@hotel.com']);
if (!$stmt->fetch()) {
    $stmt = $pdo->prepare("INSERT INTO customer (fullname, email, password, phone, isadmin) VALUES (?, ?, ?, ?, 1)");
    $stmt->execute(['Grand Lux Hotel Admin', 'admin@hotel.com', $adminHash, '+91 9876543211']);
    echo "Added admin@hotel.com\n";
} else {
    $stmt = $pdo->prepare("UPDATE customer SET password = ?, isadmin = 1 WHERE email = ?");
    $stmt->execute([$adminHash, 'admin@hotel.com']);
    echo "Updated admin@hotel.com\n";
}

// 3. Also ensure martha@hotmail.com has password user123 for testing existing bookings
$userHash = password_hash('user123', PASSWORD_BCRYPT);
$stmt = $pdo->prepare("UPDATE customer SET password = ? WHERE email = 'martha@hotmail.com'");
$stmt->execute([$userHash]);
echo "Updated martha@hotmail.com password to user123\n";
