<?php

ob_start();
session_start();

require 'DB.php';
require 'Util.php';
require 'dao/CustomerDAO.php';
require 'models/Customer.php';
require 'handlers/CustomerHandler.php';

if (isset($_SESSION["accountEmail"])) {
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submitBtn"])) {
        $errors_ = null;
        $pwd = null;

        if (!empty($_POST["newPassword"])) {
            if (strlen($_POST["newPassword"]) < 4) {
                $errors_ .= Util::displayAlertV1("At least 4 characters is required for password.", "info");
            } else {
                $pwd = $_POST["newPassword"];
            }
        } else {
            if (isset($_SESSION["password"])) {
                $pwd = $_SESSION["password"];
            }
        }

        if (!empty($errors_)) {
            echo $errors_;
        } else {
            $c = new Customer();
            $c->setId((int)$_POST["cid"]);
            $c->setFullName(Util::sanitize_xss($_POST["fullName"]));
            $c->setPhone(Util::sanitize_xss($_POST["phone"]));
            $c->setEmail(Util::sanitize_xss($_POST["email"]));
            if ($pwd) {
                $c->setPassword(Util::sanitize_xss($pwd));
            } else {
                $existing = (new CustomerHandler())->getCustomerObj($_SESSION["accountEmail"]);
                $c->setPassword($existing->getPassword());
            }

            $cHandler = new CustomerHandler();
            $cHandler->updateCustomer($c);
            echo Util::displayAlertV1($cHandler->getExecutionFeedback(), "success");

            if (!empty($_POST["fullName"])) {
                $_SESSION["username"] = $_POST["fullName"];
            }
            if (!empty($_POST["phone"])) {
                $_SESSION["phoneNumber"] = $_POST["phone"];
            }
        }
    }
} else {
    echo Util::displayAlertV1("Please log in to update your profile.", "warning");
}
