<?php
ob_start();
session_start();

require_once 'app/DB.php';
require_once 'app/Util.php';
require_once 'app/models/StatusEnum.php';
require_once 'app/models/RequirementEnum.php';
require_once 'app/dao/CustomerDAO.php';
require_once 'app/dao/BookingDetailDAO.php';
require_once 'app/models/Customer.php';
require_once 'app/models/Booking.php';
require_once 'app/models/Reservation.php';
require_once 'app/handlers/CustomerHandler.php';
require_once 'app/handlers/BookingDetailHandler.php';

// Strict Admin Session Authentication
$isAdmin = false;
if (isset($_SESSION["isAdmin"]) && (
    (is_array($_SESSION["isAdmin"]) && ($_SESSION["isAdmin"][0] == 1 || $_SESSION["isAdmin"][1] === "true")) ||
    $_SESSION["isAdmin"] === 1 || $_SESSION["isAdmin"] === true
)) {
    $isAdmin = true;
}

if (!$isAdmin) {
    header("Location: sign-in.php");
    exit();
}

$username = isset($_SESSION["username"]) ? $_SESSION["username"] : "Administrator";
$bdHandler = new BookingDetailHandler();
$cHandler = new CustomerHandler();
$db = DB::getInstance();

$allBookings = $bdHandler->getAllBookings();
$allCustomers = $cHandler->getAllCustomer();
$pendingCount = $bdHandler->getPending();
$confirmedCount = $bdHandler->getConfirmed();
$cancelledCount = $bdHandler->getCancelled();
$totalReservations = count($allBookings);
$totalCustomers = count($allCustomers);

// Revenue and room stats
$revStmt = $db->query("SELECT COALESCE(SUM(total_price), 0) FROM pricing");
$totalRevenue = (float)$revStmt->fetchColumn();

$roomsStmt = $db->query("SELECT * FROM rooms ORDER BY room_number ASC");
$rooms = $roomsStmt ? $roomsStmt->fetchAll(PDO::FETCH_ASSOC) : [];
$totalRooms = count($rooms);
$availableRooms = 0;
foreach ($rooms as $r) {
    if ($r['status'] === 'Available') $availableRooms++;
}

// Room service orders
$ordersStmt = $db->query("SELECT o.*, c.fullname, c.phone FROM room_service_orders o LEFT JOIN customer c ON o.customer_id = c.cid ORDER BY o.order_id DESC");
$orders = $ordersStmt ? $ordersStmt->fetchAll(PDO::FETCH_ASSOC) : [];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Admin Operations Center - Grand Lux Hotel</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/select/1.3.3/css/select.bootstrap4.min.css">
    <link rel="stylesheet" href="css/main.css">
    <style>
        body {
            background-color: #f1f5f9;
        }
        .admin-nav {
            background: #0f172a;
            border-bottom: 2px solid var(--gold);
        }
        .stat-card {
            border-radius: 14px;
            border: 1px solid rgba(226, 232, 240, 0.8);
            background: #ffffff;
            padding: 22px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            transition: all 0.25s ease;
            position: relative;
            overflow: hidden;
        }
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        .stat-icon {
            position: absolute;
            right: 18px;
            top: 20px;
            font-size: 2.5rem;
            opacity: 0.15;
        }
        .stat-value {
            font-size: 2rem;
            font-weight: 800;
            color: #0f172a;
            margin: 4px 0;
        }
        .nav-tabs .nav-link {
            font-weight: 600;
            color: #64748b;
            border: none;
            padding: 12px 20px;
            border-radius: 8px 8px 0 0;
        }
        .nav-tabs .nav-link.active {
            color: #0f172a;
            border-bottom: 3px solid var(--gold);
            background: #ffffff;
        }
        .table-card {
            background: #ffffff;
            border-radius: 0 0 14px 14px;
            border: 1px solid #e2e8f0;
            border-top: none;
            padding: 24px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.04);
        }
        .badge-status {
            font-size: 0.78rem;
            padding: 5px 11px;
            border-radius: 50px;
            font-weight: 700;
            letter-spacing: 0.3px;
        }
    </style>
</head>
<body>

<!-- Admin Header Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark admin-nav px-4 py-2 sticky-top">
    <a class="navbar-brand font-weight-bold d-flex align-items-center" href="admin.php">
        <i class="fa-solid fa-hotel text-warning mr-2"></i>
        <span>Grand Lux <span class="badge badge-warning text-dark ml-1">Admin Ops</span></span>
    </a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#adminNavbar">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="adminNavbar">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item active"><a class="nav-link" href="admin.php"><i class="fa-solid fa-gauge mr-1"></i> Dashboard</a></li>
            <li class="nav-item"><a class="nav-link" href="index.php" target="_blank"><i class="fa-solid fa-arrow-up-right-from-square mr-1"></i> Live Website</a></li>
        </ul>
        <div class="d-flex align-items-center text-white">
            <div class="mr-3 text-right d-none d-md-block">
                <small class="text-muted d-block">Logged in as</small>
                <span class="font-weight-bold text-light"><?php echo htmlspecialchars($username); ?></span>
            </div>
            <a href="app/process_logout.php" id="admin-sign-out" class="btn btn-outline-danger btn-sm">
                <i class="fa-solid fa-sign-out-alt mr-1"></i> Sign Out
            </a>
        </div>
    </div>
</nav>

<div class="container-fluid px-4 py-4">
    <!-- Header Page Title -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h2 class="font-weight-bold text-dark mb-1">Hotel Operations Command Center</h2>
            <p class="text-muted mb-0">Real-time management of reservations, suites inventory, room service & analytics.</p>
        </div>
        <div class="mt-2 mt-md-0">
            <button onclick="window.print()" class="btn btn-light border mr-2">
                <i class="fa-solid fa-print mr-1"></i> Print Report
            </button>
            <a href="index.php" class="btn btn-dark">
                <i class="fa-solid fa-house mr-1"></i> Guest View
            </a>
        </div>
    </div>

    <!-- Metric Stat Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="stat-card">
                <i class="fa-solid fa-wallet stat-icon text-success"></i>
                <span class="small font-weight-bold text-muted text-uppercase">Total Revenue</span>
                <div class="stat-value text-success">₹<?php echo number_format($totalRevenue); ?></div>
                <small class="text-muted"><i class="fa-solid fa-arrow-trend-up text-success mr-1"></i> From <?php echo $totalReservations; ?> bookings</small>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="stat-card">
                <i class="fa-solid fa-calendar-check stat-icon text-primary"></i>
                <span class="small font-weight-bold text-muted text-uppercase">Total Reservations</span>
                <div class="stat-value text-primary"><?php echo $totalReservations; ?></div>
                <small class="text-muted">
                    <span class="badge badge-warning text-dark"><?php echo $pendingCount; ?> Pending</span>
                    <span class="badge badge-success"><?php echo $confirmedCount; ?> Confirmed</span>
                </small>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="stat-card">
                <i class="fa-solid fa-door-open stat-icon text-info"></i>
                <span class="small font-weight-bold text-muted text-uppercase">Rooms Inventory</span>
                <div class="stat-value text-info"><?php echo $availableRooms; ?> / <?php echo $totalRooms; ?></div>
                <small class="text-muted"><i class="fa-solid fa-circle-check text-success mr-1"></i> Available suites today</small>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="stat-card">
                <i class="fa-solid fa-users stat-icon text-warning"></i>
                <span class="small font-weight-bold text-muted text-uppercase">Registered Guests</span>
                <div class="stat-value text-dark"><?php echo $totalCustomers; ?></div>
                <small class="text-muted"><i class="fa-solid fa-user-shield text-info mr-1"></i> Verified accounts</small>
            </div>
        </div>
    </div>

    <div id="tableContainer">
        <!-- Main Navigation Tabs -->
        <ul class="nav nav-tabs" id="adminTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="reservations-tab" data-toggle="tab" href="#reservations" role="tab">
                    <i class="fa-solid fa-calendar-days mr-1"></i> Reservations (<?php echo $totalReservations; ?>)
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="roomservice-tab" data-toggle="tab" href="#roomservice" role="tab">
                    <i class="fa-solid fa-bell-concierge mr-1"></i> Room Service Orders (<?php echo count($orders); ?>)
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="rooms-tab" data-toggle="tab" href="#rooms" role="tab">
                    <i class="fa-solid fa-bed mr-1"></i> Suites & Inventory
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="customers-tab" data-toggle="tab" href="#customers" role="tab">
                    <i class="fa-solid fa-users mr-1"></i> Customers Directory
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="analytics-tab" data-toggle="tab" href="#analytics" role="tab">
                    <i class="fa-solid fa-chart-pie mr-1"></i> Performance Analytics
                </a>
            </li>
        </ul>

        <div class="tab-content">
            <!-- TAB 1: RESERVATIONS -->
            <div class="tab-pane fade show active table-card" id="reservations" role="tabpanel">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                    <div class="btn-group mb-2">
                        <button type="button" id="confirm-booking" class="btn btn-success btn-sm font-weight-bold">
                            <i class="fa-solid fa-check mr-1"></i> Confirm Selected
                        </button>
                        <button type="button" id="cancel-booking" class="btn btn-danger btn-sm font-weight-bold">
                            <i class="fa-solid fa-xmark mr-1"></i> Cancel Selected
                        </button>
                    </div>

                    <div class="d-flex align-items-center mb-2">
                        <span class="mr-2 font-weight-bold small text-muted">Quick Filter:</span>
                        <div class="btn-group btn-group-toggle btn-group-sm" data-toggle="buttons">
                            <label class="btn btn-outline-secondary active">
                                <input type="radio" name="viewOption" value="all" checked> All
                            </label>
                            <label class="btn btn-outline-warning">
                                <input type="radio" name="viewOption" value="pending"> Pending
                            </label>
                            <label class="btn btn-outline-success">
                                <input type="radio" name="viewOption" value="confirmed"> Confirmed
                            </label>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="reservationDataTable" class="table table-hover table-bordered" width="100%">
                        <thead class="thead-light">
                            <tr>
                                <th width="30">#</th>
                                <th class="text-hide p-0" width="1">ID</th>
                                <th>Booking ID</th>
                                <th>Guest Name</th>
                                <th>Email & Phone</th>
                                <th>Suite Type</th>
                                <th>Check-in / Out</th>
                                <th>Tariff</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allBookings as $k => $b): 
                                $statusUpper = strtoupper($b['status']);
                                $statusClass = 'badge-secondary';
                                if ($statusUpper === 'PENDING') $statusClass = 'badge-warning text-dark';
                                elseif ($statusUpper === 'CONFIRMED') $statusClass = 'badge-success';
                                elseif ($statusUpper === 'CHECKED_IN') $statusClass = 'badge-primary';
                                elseif ($statusUpper === 'CANCELLED') $statusClass = 'badge-danger';
                            ?>
                            <tr>
                                <td><?php echo ($k + 1); ?></td>
                                <td class="text-hide p-0" data-id="<?php echo $b["id"]; ?>"><?php echo $b["id"]; ?></td>
                                <td><span class="badge badge-dark">#<?php echo $b['id']; ?></span></td>
                                <td class="font-weight-bold"><?php echo htmlspecialchars($b['fullname'] ?: 'Guest #' . $b['cid']); ?></td>
                                <td>
                                    <div class="small text-muted"><?php echo htmlspecialchars($b['email']); ?></div>
                                    <div class="small text-muted"><?php echo htmlspecialchars($b['phone']); ?></div>
                                </td>
                                <td>
                                    <span class="badge badge-light border"><?php echo htmlspecialchars($b['type']); ?></span>
                                    <small class="d-block text-muted"><?php echo htmlspecialchars($b['requirement']); ?></small>
                                </td>
                                <td>
                                    <div class="small font-weight-bold"><?php echo $b['start']; ?> &rarr; <?php echo $b['end']; ?></div>
                                    <small class="text-muted"><?php echo $b['nights']; ?> night(s)</small>
                                </td>
                                <td class="font-weight-bold text-success">₹<?php echo number_format($b['total_price']); ?></td>
                                <td><span class="badge <?php echo $statusClass; ?> badge-status"><?php echo $statusUpper; ?></span></td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light border dropdown-toggle" type="button" data-toggle="dropdown">
                                            Status
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a class="dropdown-item text-success" href="#" onclick="quickStatus(<?php echo $b['id']; ?>, 'CONFIRMED')"><i class="fa-solid fa-check mr-2"></i> Confirm</a>
                                            <a class="dropdown-item text-primary" href="#" onclick="quickStatus(<?php echo $b['id']; ?>, 'CHECKED_IN')"><i class="fa-solid fa-key mr-2"></i> Check In</a>
                                            <a class="dropdown-item text-secondary" href="#" onclick="quickStatus(<?php echo $b['id']; ?>, 'CHECKED_OUT')"><i class="fa-solid fa-door-closed mr-2"></i> Check Out</a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item text-danger" href="#" onclick="quickStatus(<?php echo $b['id']; ?>, 'CANCELLED')"><i class="fa-solid fa-ban mr-2"></i> Cancel</a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 2: ROOM SERVICE KITCHEN ORDERS -->
            <div class="tab-pane fade table-card" id="roomservice" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="font-weight-bold mb-0"><i class="fa-solid fa-bell-concierge text-warning mr-2"></i> In-Room Dining & Kitchen Orders</h5>
                    <button class="btn btn-sm btn-outline-secondary" onclick="location.reload()"><i class="fa-solid fa-rotate-right mr-1"></i> Refresh</button>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>Order #</th>
                                <th>Room / Suite</th>
                                <th>Guest</th>
                                <th>Items Ordered</th>
                                <th>Special Notes</th>
                                <th>Total</th>
                                <th>Time</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($orders)): ?>
                                <tr><td colspan="9" class="text-center py-4 text-muted">No room service orders received yet.</td></tr>
                            <?php else: foreach ($orders as $ord): 
                                $items = json_decode($ord['items_json'], true) ?: [];
                            ?>
                            <tr>
                                <td class="font-weight-bold">#<?php echo $ord['order_id']; ?></td>
                                <td><span class="badge badge-dark"><?php echo htmlspecialchars($ord['room_number']); ?></span></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($ord['fullname'] ?: 'Customer #' . $ord['customer_id']); ?></strong>
                                    <div class="small text-muted"><?php echo htmlspecialchars($ord['phone']); ?></div>
                                </td>
                                <td>
                                    <ul class="list-unstyled mb-0 small">
                                        <?php foreach ($items as $it): ?>
                                            <li>• <?php echo htmlspecialchars($it['name']); ?> &times; <strong><?php echo $it['qty']; ?></strong> (₹<?php echo $it['price'] * $it['qty']; ?>)</li>
                                        <?php endforeach; ?>
                                    </ul>
                                </td>
                                <td class="small text-muted"><?php echo htmlspecialchars($ord['special_instructions'] ?: 'Standard service'); ?></td>
                                <td class="font-weight-bold text-success">₹<?php echo number_format($ord['total_amount']); ?></td>
                                <td class="small text-muted"><?php echo date('M d, h:i A', strtotime($ord['created_at'])); ?></td>
                                <td>
                                    <span class="badge <?php echo $ord['status'] === 'Delivered' ? 'badge-success' : ($ord['status'] === 'Preparing' ? 'badge-primary' : 'badge-warning text-dark'); ?> badge-status">
                                        <?php echo $ord['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" onclick="updateOrderStatus(<?php echo $ord['order_id']; ?>, 'Preparing')">Cooking</button>
                                        <button class="btn btn-outline-success" onclick="updateOrderStatus(<?php echo $ord['order_id']; ?>, 'Delivered')">Delivered</button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 3: ROOMS & SUITES INVENTORY -->
            <div class="tab-pane fade table-card" id="rooms" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="font-weight-bold mb-0"><i class="fa-solid fa-bed text-primary mr-2"></i> Suites Catalog & Status Management</h5>
                    <span class="badge badge-info"><?php echo count($rooms); ?> Suites in Catalog</span>
                </div>

                <div class="row">
                    <?php foreach ($rooms as $rm): 
                        $statusColor = $rm['status'] === 'Available' ? 'badge-success' : ($rm['status'] === 'Occupied' ? 'badge-danger' : 'badge-warning text-dark');
                    ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm border">
                            <img src="<?php echo htmlspecialchars($rm['image']); ?>" class="card-img-top" style="height: 180px; object-fit: cover;" alt="Suite">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="card-title font-weight-bold mb-0">Suite <?php echo htmlspecialchars($rm['room_number']); ?></h5>
                                    <span class="badge <?php echo $statusColor; ?>"><?php echo $rm['status']; ?></span>
                                </div>
                                <h6 class="text-muted small"><?php echo htmlspecialchars($rm['title']); ?> (<?php echo $rm['type']; ?>)</h6>
                                <p class="small text-muted mb-2"><?php echo htmlspecialchars($rm['description']); ?></p>
                                <div class="font-weight-bold text-success mb-3">₹<?php echo number_format($rm['price_per_night']); ?> <small class="text-muted">/ night</small></div>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted"><i class="fa-solid fa-user-group mr-1"></i> Up to <?php echo $rm['capacity_adults']; ?> Adults</small>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-success" onclick="updateRoomStatus(<?php echo $rm['room_id']; ?>, 'Available')">Available</button>
                                        <button class="btn btn-outline-danger" onclick="updateRoomStatus(<?php echo $rm['room_id']; ?>, 'Occupied')">Occupied</button>
                                        <button class="btn btn-outline-secondary" onclick="updateRoomStatus(<?php echo $rm['room_id']; ?>, 'Maintenance')">Maint.</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- TAB 4: CUSTOMERS DIRECTORY -->
            <div class="tab-pane fade table-card" id="customers" role="tabpanel">
                <div class="table-responsive">
                    <table id="customerTable" class="table table-bordered table-hover" width="100%">
                        <thead class="thead-light">
                            <tr>
                                <th width="40">#</th>
                                <th>Customer ID</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Role</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allCustomers as $key => $cust): ?>
                            <tr>
                                <td><?php echo ($key + 1); ?></td>
                                <td><span class="badge badge-secondary">CID-<?php echo $cust->getId(); ?></span></td>
                                <td class="font-weight-bold"><?php echo htmlspecialchars($cust->getFullName()); ?></td>
                                <td><?php echo htmlspecialchars($cust->getEmail()); ?></td>
                                <td><?php echo htmlspecialchars($cust->getPhone()); ?></td>
                                <td>
                                    <?php if ($cust->getIsAdmin()): ?>
                                        <span class="badge badge-dark"><i class="fa-solid fa-shield mr-1"></i> Administrator</span>
                                    <?php else: ?>
                                        <span class="badge badge-light border">Guest Customer</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 5: ANALYTICS -->
            <div class="tab-pane fade table-card" id="analytics" role="tabpanel">
                <h5 class="font-weight-bold mb-4"><i class="fa-solid fa-chart-column text-primary mr-2"></i> Performance & Occupancy Analytics</h5>
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card p-3 shadow-sm">
                            <h6 class="font-weight-bold text-center mb-3">Reservations by Room Category</h6>
                            <canvas id="roomTypeChart" height="220"></canvas>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card p-3 shadow-sm">
                            <h6 class="font-weight-bold text-center mb-3">Reservation Status Distribution</h6>
                            <canvas id="statusChart" height="220"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modals -->
<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fa-solid fa-circle-check mr-2"></i> Confirm Reservation(s)</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to approve and confirm the selected reservation(s)?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmTrue">Yes, Confirm</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="cancelModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fa-solid fa-triangle-exclamation mr-2"></i> Cancel Reservation(s)</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to cancel the selected reservation(s)?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" id="cancelTrue">Yes, Cancel</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.3.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/select/1.3.3/js/dataTables.select.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script src="js/admin.js"></script>
<script>
function quickStatus(id, status) {
    $.post('app/admin/manage_reservation.php', { booking_id: id, status: status, update_status: true }, function(resp) {
        location.reload();
    }, 'json');
}

function updateOrderStatus(orderId, status) {
    $.post('app/api/room_service.php', { action: 'update_order_status', order_id: orderId, status: status }, function(res) {
        location.reload();
    }, 'json');
}

function updateRoomStatus(roomId, status) {
    $.post('app/api/admin_ops.php', { action: 'update_room_status', room_id: roomId, status: status }, function(res) {
        location.reload();
    }, 'json');
}

// Chart.js visualizations
$(document).ready(function() {
    var ctx1 = document.getElementById('roomTypeChart');
    if (ctx1) {
        new Chart(ctx1, {
            type: 'doughnut',
            data: {
                labels: ['Single Suites', 'Double Rooms', 'Deluxe Penthouses'],
                datasets: [{
                    data: [
                        <?php 
                        $s = $d = $dl = 0;
                        foreach ($allBookings as $b) {
                            $t = strtolower($b['type']);
                            if (strpos($t, 'single') !== false) $s++;
                            elseif (strpos($t, 'double') !== false) $d++;
                            elseif (strpos($t, 'deluxe') !== false) $dl++;
                        }
                        echo "$s, $d, $dl";
                        ?>
                    ],
                    backgroundColor: ['#3b82f6', '#10b981', '#f59e0b']
                }]
            }
        });
    }

    var ctx2 = document.getElementById('statusChart');
    if (ctx2) {
        new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: ['Pending', 'Confirmed', 'Cancelled'],
                datasets: [{
                    label: 'Reservations',
                    data: [<?php echo "$pendingCount, $confirmedCount, $cancelledCount"; ?>],
                    backgroundColor: ['#f59e0b', '#10b981', '#ef4444']
                }]
            },
            options: {
                scales: { y: { beginAtZero: true } }
            }
        });
    }
});
</script>
</body>
</html>