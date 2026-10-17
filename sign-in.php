<?php
ob_start();
session_start();

require_once 'app/DB.php';
require_once 'app/Util.php';
require_once 'app/models/Customer.php';
require_once 'app/dao/CustomerDAO.php';
require_once 'app/handlers/CustomerHandler.php';

// If already logged in, redirect
if (isset($_SESSION["accountEmail"])) {
    if (isset($_SESSION["isAdmin"]) && (
        (is_array($_SESSION["isAdmin"]) && ($_SESSION["isAdmin"][0] == 1 || $_SESSION["isAdmin"][1] === "true")) ||
        $_SESSION["isAdmin"] === 1 || $_SESSION["isAdmin"] === true
    )) {
        header("Location: admin.php");
        exit();
    } else {
        header("Location: index.php");
        exit();
    }
}

$alertMessage = "";
// Direct POST fallback support (in case JS is bypassed)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["loginSubmitBtn"])) {
    $email = isset($_POST["loginEmail"]) ? trim($_POST["loginEmail"]) : "";
    $password = isset($_POST["loginPassword"]) ? $_POST["loginPassword"] : "";

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $alertMessage = Util::displayAlertV1("Please enter a valid email address.", "warning");
    } elseif (empty($password)) {
        $alertMessage = Util::displayAlertV1("Password is required.", "warning");
    } else {
        $handler = new CustomerHandler();
        $customer = new Customer();
        $customer->setEmail($email);

        if ($handler->doesCustomerExists($email) == 0) {
            $alertMessage = Util::displayAlertV1("This email is not registered.", "warning");
        } elseif (!$handler->isPasswordMatchWithEmail($password, $customer)) {
            $alertMessage = Util::displayAlertV1("Incorrect password.", "warning");
        } else {
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
                setcookie("is_admin", "true", time() + 86400, "/");
                header("Location: admin.php");
                exit();
            } else {
                $_SESSION["isAdmin"] = [0, "false"];
                $_SESSION["authenticated"] = [1, "false"];
                $_SESSION["password"] = $password;
                setcookie("is_admin", "false", time() + 86400, "/");
                header("Location: index.php");
                exit();
            }
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Sign In - Grand Lux Hotel</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            max-width: 440px;
        }
        .auth-header {
            background: linear-gradient(135deg, #111827 0%, #1f2937 100%);
            color: #f3f4f6;
            padding: 2rem 1.5rem;
            text-align: center;
            position: relative;
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
        .demo-chip {
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 0.82rem;
            border-radius: 30px;
            padding: 6px 14px;
            display: inline-flex;
            align-items: center;
            margin: 4px;
        }
        .demo-chip:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.12);
        }
        .btn-gold {
            background: linear-gradient(135deg, #d4af37 0%, #c49b28 100%);
            color: #111827;
            font-weight: 600;
            border: none;
            padding: 11px;
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
            padding: 12px 14px;
            height: auto;
            border: 1px solid #d1d5db;
        }
        .form-control:focus {
            border-color: #d4af37;
            box-shadow: 0 0 0 0.2rem rgba(212, 175, 55, 0.25);
        }
    </style>
</head>
<body>

<div class="auth-card">
    <div class="auth-header">
        <div class="logo-badge"><i class="fa-solid fa-hotel"></i></div>
        <h4 class="mb-1 font-weight-bold">Grand Lux Hotel</h4>
        <p class="text-muted small mb-0">Sign in to manage your luxury stay & bookings</p>
    </div>

    <div class="p-4">
        <!-- Quick Demo Accounts -->
        <div class="bg-light p-3 rounded mb-3 border">
            <div class="small font-weight-bold text-secondary mb-2 text-uppercase">
                <i class="fa-solid fa-bolt text-warning mr-1"></i> 1-Click Demo Login:
            </div>
            <div class="d-flex flex-wrap">
                <span class="badge badge-primary demo-chip" onclick="fillCredentials('guest@hotel.com', 'guest123')">
                    <i class="fa-solid fa-user mr-1"></i> Guest Demo
                </span>
                <span class="badge badge-dark demo-chip" onclick="fillCredentials('admin@hotel.com', 'admin123')">
                    <i class="fa-solid fa-shield-halved mr-1"></i> Admin Demo
                </span>
            </div>
        </div>

        <div id="loginAlertBox"><?php echo $alertMessage; ?></div>

        <form id="login-form" method="post" autocomplete="on">
            <div class="form-group">
                <label class="small font-weight-bold text-muted mb-1">Email Address</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-white border-right-0"><i class="fa-regular fa-envelope text-muted"></i></span>
                    </div>
                    <input type="email" class="form-control border-left-0" id="loginEmail" name="loginEmail" placeholder="e.g. guest@hotel.com" required>
                </div>
            </div>

            <div class="form-group">
                <label class="small font-weight-bold text-muted mb-1">Password</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-white border-right-0"><i class="fa-solid fa-lock text-muted"></i></span>
                    </div>
                    <input type="password" class="form-control border-left-0" id="loginPassword" name="loginPassword" placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" name="loginSubmitBtn" value="1" class="btn btn-gold btn-block mt-4">
                <i class="fa-solid fa-arrow-right-to-bracket mr-2"></i> Sign In
            </button>
        </form>

        <div class="text-center mt-4 pt-3 border-top">
            <span class="text-muted small">Don't have an account yet?</span>
            <a href="register.php" class="font-weight-bold text-dark ml-1 small">Create Account</a>
            <div class="mt-2">
                <a href="index.php" class="text-secondary small"><i class="fa-solid fa-house mr-1"></i> Back to Hotel Home</a>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/utilityFunctions.js"></script>
<script src="js/form-submission.js"></script>
<script>
function fillCredentials(email, pwd) {
    document.getElementById('loginEmail').value = email;
    document.getElementById('loginPassword').value = pwd;
}
</script>
</body>
</html>