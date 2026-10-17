<?php

ob_start();
session_start();

require '../DB.php';
require '../Util.php';
require '../dao/BookingDetailDAO.php';
require '../handlers/BookingDetailHandler.php';

$isAdmin = false;
if (isset($_SESSION["isAdmin"]) && (
    (is_array($_SESSION["isAdmin"]) && ($_SESSION["isAdmin"][0] == 1 || $_SESSION["isAdmin"][1] === "true")) ||
    $_SESSION["isAdmin"] === 1 || $_SESSION["isAdmin"] === true
)) {
    $isAdmin = true;
}

if ($isAdmin) {
    $bdh = new BookingDetailHandler();

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["confirm"])) {
        $item = isset($_POST["item"]) ? $_POST["item"] : [];
        $bdh->confirmSelection($item);
        echo Util::displayAlertV1($bdh->getExecutionFeedback(), "success");
        exit;
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["cancel"])) {
        $item = isset($_POST["item"]) ? $_POST["item"] : [];
        $bdh->cancelSelection($item);
        echo Util::displayAlertV1($bdh->getExecutionFeedback(), "warning");
        exit;
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_status"]) && isset($_POST["booking_id"])) {
        $bookingId = (int)$_POST["booking_id"];
        $status = strtoupper(trim($_POST["status"]));
        if ($bdh->updateBookingStatus($bookingId, $status)) {
            echo json_encode(["status" => "success", "message" => "Booking #{$bookingId} marked as {$status}."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to update booking status."]);
        }
        exit;
    }

    echo Util::displayAlertV1("No action performed.", "info");
} else {
    header("HTTP/1.1 403 Forbidden");
    echo Util::displayAlertV1("Unauthorized: Admin privileges required.", "danger");
}
