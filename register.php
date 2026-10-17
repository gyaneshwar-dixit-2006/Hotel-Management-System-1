<?php
ob_start();
session_start();

require_once 'app/DB.php';
require_once 'app/Util.php';
require_once 'app/dao/CustomerDAO.php';
require_once 'app/models/Customer.php';
require_once 'app/handlers/CustomerHandler.php';

if (isset($_SESSION["accountEmail"])) {
    header("Location: index.php");
    exit();
}

$alertMessage = "";
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["registerSubmitBtn"])) {
    $fullName = isset($_POST["registrationFullName"]) ? trim($_POST["registrationFullName"]) : "";
    $phoneNumber = isset($_POST["registrationPhoneNumber"]) ? trim($_POST["registrationPhoneNumber"]) : "";
    $email = isset($_POST["registrationEmail"]) ? trim($_POST["registrationEmail"]) : "";
    $password = isset($_POST["registrationPassword"]) ? $_POST["registrationPassword"] : "";
    $password2 = isset($_POST["registrationPassword2"]) ? $_POST["registrationPassword2"] : "";

    if (empty($fullName)) {
        $alertMessage = Util::displayAlertV1("Please enter your full name.", "warning");
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $alertMessage = Util::displayAlertV1("Please enter a valid email address.", "warning");
    } elseif (strlen($password) < 4) {
        $alertMessage = Util::displayAlertV1("A password of at least 4 characters is required.", "warning");
    } elseif ($password !== $password2) {
        $alertMessage = Util::displayAlertV1("Passwords do not match.", "warning");
    } else {
        $handler = new CustomerHandler();
        if ($handler->doesCustomerExists($email) > 0) {
            $alertMessage = Util::displayAlertV1("Email already registered. <a href='sign-in.php' class='alert-link'>Sign in here</a>.", "warning");
        } else {
            $customer = new Customer();
            $customer->setFullName(Util::sanitize_xss($fullName));
            $customer->setEmail(Util::sanitize_xss($email));
            $customer->setPhone(Util::sanitize_xss($phoneNumber));
            $customer->setPassword(Util::sanitize_xss($password));

            $handler->insertCustomer($customer);
            $alertMessage = Util::displayAlertV1("Registration successful! <a href='sign-in.php' class='alert-link'>Click here to Sign In</a>.", "success");
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Create Account - Grand Lux Hotel</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/css/intlTelInput.css" />
    <link rel="stylesheet" href="css/main.css">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #0b132b 0%, #1c2541 50%, #3a506b 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            padding: 20px;
        }
        .auth-card {
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(16px);
            border-radius: 18px;
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.35);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
            width: 100%;
            max-width: 500px;
        }
        .auth-header {
            background: linear-gradient(135deg, #111827 0%, #1f2937 100%);
            color: #f3f4f6;
            padding: 2rem 1.5rem;
            text-align: center;
        }
        .auth-header .logo-badge {
            width: 60px;
            height: 60px;
            background: #d4af37;
            color: #111827;
            font-size: 26px;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            box-shadow: 0 4px 15px rgba(212, 175, 55, 0.4);
            margin-bottom: 0.75rem;
        }
        .btn-gold {
            background: linear-gradient(135deg, #d4af37 0%, #c49b28 100%);
            color: #111827;
            font-weight: 600;
            border: none;
            padding: 12px;
            border-radius: 8px;
            transition: all 0.25s ease;
        }
        .btn-gold:hover {
            background: linear-gradient(135deg, #e5bf45 0%, #d4af37 100%);
            color: #000;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(212, 175, 55, 0.35);
        }
        .form-control {
            border-radius: 8px;
            padding: 10px 14px;
            border: 1px solid #d1d5db;
        }
        .form-control:focus {
            border-color: #d4af37;
            box-shadow: 0 0 0 0.2rem rgba(212, 175, 55, 0.25);
        }
        .iti { width: 100%; }
    </style>
</head>
<body>

<div class="auth-card">
    <div class="auth-header">
        <div class="logo-badge"><i class="fa-solid fa-hotel"></i></div>
        <h4 class="mb-1 font-weight-bold">Grand Lux Hotel</h4>
        <p class="text-muted small mb-0">Join our luxury privilege program & book instantly</p>
    </div>

    <div class="p-4">
        <div id="registerAlert"><?php echo $alertMessage; ?></div>

        <form id="registration-form" method="post" autocomplete="on">
            <div class="form-group mb-3">
                <label class="small font-weight-bold text-muted mb-1">Full Name *</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-white border-right-0"><i class="fa-regular fa-user text-muted"></i></span>
                    </div>
                    <input type="text" class="form-control border-left-0" name="registrationFullName" placeholder="e.g. Martha Smith" required>
                </div>
            </div>

            <div class="form-group mb-3">
                <label class="small font-weight-bold text-muted mb-1">Phone Number *</label>
                <input type="tel" class="form-control" id="registrationPhoneNumber" placeholder="Phone number" required>
                <input type="hidden" id="fullPhoneNumber" name="registrationPhoneNumber">
            </div>

            <div class="form-group mb-3">
                <label class="small font-weight-bold text-muted mb-1">Email Address *</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-white border-right-0"><i class="fa-regular fa-envelope text-muted"></i></span>
                    </div>
                    <input type="email" class="form-control border-left-0" name="registrationEmail" placeholder="e.g. guest@example.com" required>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-6 form-group mb-3">
                    <label class="small font-weight-bold text-muted mb-1">Password *</label>
                    <input type="password" class="form-control" name="registrationPassword" placeholder="Min. 4 chars" required>
                </div>
                <div class="col-sm-6 form-group mb-3">
                    <label class="small font-weight-bold text-muted mb-1">Confirm Password *</label>
                    <input type="password" class="form-control" name="registrationPassword2" placeholder="Repeat password" required>
                </div>
            </div>

            <button type="submit" name="registerSubmitBtn" value="updatebtn" class="btn btn-gold btn-block mt-3">
                <i class="fa-solid fa-user-plus mr-2"></i> Register Account
            </button>
        </form>

        <div class="text-center mt-4 pt-3 border-top">
            <span class="text-muted small">Already have an account?</span>
            <a href="sign-in.php" class="font-weight-bold text-dark ml-1 small">Sign In</a>
            <div class="mt-2">
                <a href="index.php" class="text-secondary small"><i class="fa-solid fa-house mr-1"></i> Back to Hotel Home</a>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.3.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/intlTelInput.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.js"></script>
<script>
    var input = document.querySelector("#registrationPhoneNumber");
    var iti = window.intlTelInput(input, {
        initialCountry: "in",
        separateDialCode: true,
        preferredCountries: ["in", "us", "gb", "ca", "ae"],
    });

    document.querySelector("#registration-form").addEventListener("submit", function (e) {
        var num = iti.getNumber();
        if (num) {
            document.querySelector("#fullPhoneNumber").value = num;
        } else {
            document.querySelector("#fullPhoneNumber").value = input.value;
        }
    });
</script>
</body>
</html>