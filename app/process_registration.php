<?php

require 'DB.php';
require 'Util.php';
require 'dao/CustomerDAO.php';
require 'models/Customer.php';
require 'handlers/CustomerHandler.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submitBtn"])) {
    $errors_ = null;

    $fullName = isset($_POST["fullName"]) ? trim($_POST["fullName"]) : "";
    $email = isset($_POST["email"]) ? trim($_POST["email"]) : "";
    $phoneNumber = isset($_POST["phoneNumber"]) ? trim($_POST["phoneNumber"]) : "";
    $password = isset($_POST["password"]) ? $_POST["password"] : "";
    $password2 = isset($_POST["password2"]) ? $_POST["password2"] : "";

    if (empty($fullName)) {
        $errors_ .= Util::displayAlertV1("Please enter your full name.", "warning");
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors_ .= Util::displayAlertV1("Please enter a valid email address.", "warning");
    }
    if (strlen($password) < 4 || strlen($password2) < 4) {
        $errors_ .= Util::displayAlertV1("A password of at least 4 characters is required.", "warning");
    }
    if ($password !== $password2) {
        $errors_ .= Util::displayAlertV1("Passwords do not match.", "warning");
    }

    if (!empty($errors_)) {
        echo $errors_;
    } else {
        $handler = new CustomerHandler();
        if ($handler->doesCustomerExists($email) > 0) {
            echo Util::displayAlertV1("This email is already registered. Please sign in instead.", "warning");
        } else {
            $customer = new Customer();
            $customer->setFullName(Util::sanitize_xss($fullName));
            $customer->setEmail(Util::sanitize_xss($email));
            $customer->setPhone(Util::sanitize_xss($phoneNumber));
            $customer->setPassword(Util::sanitize_xss($password));

            $handler->insertCustomer($customer);
            echo Util::displayAlertV1($handler->getExecutionFeedback(), "success");
        }
    }
}
