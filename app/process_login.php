<?php

ob_start();
session_start();

require 'DB.php';
require 'Util.php';
require 'dao/CustomerDAO.php';
require 'dao/AdminDAO.php';
require 'models/Customer.php';
require 'models/Admin.php';
require 'handlers/CustomerHandler.php';
require 'handlers/AdminHandler.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submitBtn"])) {
    $errors_ = null;
    $email = isset($_POST["email"]) ? trim($_POST["email"]) : "";
    $password = isset($_POST["password"]) ? $_POST["password"] : "";

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors_ .= Util::displayAlertV1("Please enter a valid email address.", "warning");
    }
    if (empty($password)) {
        $errors_ .= Util::displayAlertV1("Password is required.", "warning");
    }

    if (!empty($errors_)) {
        echo $errors_;
        exit;
    }

    $handler = new CustomerHandler();
    $customer = new Customer();
    $customer->setEmail($email);

    if ($handler->doesCustomerExists($email) == 0) {
        echo Util::displayAlertV1("This email is not registered.", "warning");
        exit;
    }

    if (!$handler->isPasswordMatchWithEmail($password, $customer)) {
        echo Util::displayAlertV1("Incorrect password.", "warning");
        exit;
    }

    $customerObj = $handler->getCustomerObj($email);
    $isAdmin = $handler->handleIsAdmin($email);

    $_SESSION["username"] = $handler->getUsername($email);
    $_SESSION["accountEmail"] = $email;
    $_SESSION["cid"] = $customerObj->getId();
    if ($customerObj->getPhone()) {
        $_SESSION["phoneNumber"] = $customerObj->getPhone();
    }

    if ($isAdmin) {
        $_SESSION["isAdmin"] = [1, "true"];
        $_SESSION["authenticated"] = [1, "true"];
        echo json_encode($_SESSION["isAdmin"]);
    } else {
        $_SESSION["isAdmin"] = [0, "false"];
        $_SESSION["authenticated"] = [1, "false"];
        $_SESSION["password"] = $password;
        echo json_encode($_SESSION["authenticated"]);
    }
}
