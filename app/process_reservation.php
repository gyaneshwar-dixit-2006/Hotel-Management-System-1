<?php

ob_start();
session_start();

require 'DB.php';
require 'Util.php';
require 'dao/BookingReservationDAO.php';
require 'dao/CustomerDAO.php';
require 'models/Customer.php';
require 'models/Booking.php';
require 'models/Reservation.php';
require 'models/Pricing.php';
require 'models/StatusEnum.php';
require 'handlers/BookingReservationHandler.php';
require 'handlers/CustomerHandler.php';

if (isset($_SESSION["accountEmail"]) || isset($_SESSION["authenticated"])) {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $startDate = $endDate = null;
        $errors_ = null;

        $startStr = isset($_POST["start"]) ? trim($_POST["start"]) : "";
        $endStr = isset($_POST["end"]) ? trim($_POST["end"]) : "";
        $type = isset($_POST["type"]) ? trim($_POST["type"]) : "";
        $adults = isset($_POST["adults"]) ? (int)$_POST["adults"] : 1;
        $children = isset($_POST["children"]) ? (int)$_POST["children"] : 0;
        $requirement = isset($_POST["requirement"]) ? trim($_POST["requirement"]) : "no preference";
        $requests = isset($_POST["requests"]) ? trim($_POST["requests"]) : "";

        if (empty($startStr)) {
            $errors_ .= Util::displayAlertV1("Please select a check-in date.", "info");
        }
        if (empty($endStr)) {
            $errors_ .= Util::displayAlertV1("Please select a check-out date.", "info");
        }
        if (empty($type)) {
            $errors_ .= Util::displayAlertV1("Please select a room type.", "info");
        }
        if ($adults < 1) {
            $errors_ .= Util::displayAlertV1("Please select at least 1 adult guest.", "info");
        }

        $nights = 1;
        if (!empty($startStr) && !empty($endStr)) {
            try {
                $startDate = new DateTime($startStr);
                $endDate = new DateTime($endStr);
                if ($endDate <= $startDate) {
                    $errors_ .= Util::displayAlertV1("Check-out date must be after check-in date.", "info");
                } else {
                    $diff = $startDate->diff($endDate);
                    $nights = max(1, $diff->days);
                }
            } catch (Exception $e) {
                $errors_ .= Util::displayAlertV1("Invalid date format provided.", "info");
            }
        }

        if (!empty($errors_)) {
            echo $errors_;
            exit;
        }

        // Determine customer ID
        $cid = 0;
        if (!empty($_POST["cid"])) {
            $cid = (int)$_POST["cid"];
        } elseif (isset($_SESSION["cid"])) {
            $cid = (int)$_SESSION["cid"];
        } elseif (isset($_SESSION["accountEmail"])) {
            $cHandler = new CustomerHandler();
            $cust = $cHandler->getCustomerObj($_SESSION["accountEmail"]);
            $cid = $cust->getId();
        }

        if ($cid <= 0) {
            echo Util::displayAlertV1("Unable to locate customer profile. Please sign in again.", "danger");
            exit;
        }

        // Room pricing reference
        $rates = [
            'Single' => 12030,
            'Double' => 16657,
            'Deluxe' => 23135
        ];
        $ratePerNight = isset($rates[$type]) ? $rates[$type] : 15000;

        $totalPrice = !empty($_POST["totalPrice"]) ? (double)$_POST["totalPrice"] : ($ratePerNight * $nights);
        $bookedDate = !empty($_POST["bookedDate"]) ? $_POST["bookedDate"] : date('Y-m-d');

        $r = new Reservation();
        $r->setCid($cid);
        $r->setStatus(\models\StatusEnum::PENDING_STR);
        $r->setNotes(null);
        $r->setStart(Util::sanitize_xss($startStr));
        $r->setEnd(Util::sanitize_xss($endStr));
        $r->setType(Util::sanitize_xss($type));
        $r->setRequirement(Util::sanitize_xss($requirement));
        $r->setAdults($adults);
        $r->setChildren($children);
        $r->setRequests(Util::sanitize_xss($requests));
        $unique = uniqid();
        $r->setHash($unique);

        $p = new Pricing();
        $p->setBookedDate(Util::sanitize_xss($bookedDate));
        $p->setNights($nights);
        $p->setTotalPrice($totalPrice);

        $brh = new BookingReservationHandler($r, $p);
        $result = $brh->create();

        $feedback = $brh->getExecutionFeedback();
        $out = array(
            "success" => ($result === "success") ? "true" : "false",
            "hash" => $unique,
            "response" => Util::displayAlertV2($feedback, $result)
        );
        echo json_encode($out, JSON_PRETTY_PRINT);
    }
} else {
    echo Util::displayAlertV1("Please log in to make a reservation.", "warning");
}
