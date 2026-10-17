<?php
ob_start();
session_start();

require_once 'app/DB.php';
require_once 'app/Util.php';
require_once 'app/dao/CustomerDAO.php';
require_once 'app/dao/BookingDetailDAO.php';
require_once 'app/models/RequirementEnum.php';
require_once 'app/models/Customer.php';
require_once 'app/models/Booking.php';
require_once 'app/models/Reservation.php';
require_once 'app/handlers/CustomerHandler.php';
require_once 'app/handlers/BookingDetailHandler.php';

$isSessionExists = false;
$isAdminUser = false;
$username = "";
$cHandler = null;
$cBookings = [];
$db = DB::getInstance();

if (isset($_SESSION["accountEmail"])) {
    $isSessionExists = true;
    $cHandler = (new CustomerHandler())->getCustomerObj($_SESSION["accountEmail"]);
    $username = !empty($_SESSION["username"]) ? $_SESSION["username"] : $cHandler->getFullName();
    if (empty($username)) {
        $username = $cHandler->getEmail();
    }

    if ($cHandler->getId()) {
        $bdHandler = new BookingDetailHandler();
        $cBookings = $bdHandler->getCustomerBookings($cHandler);
    }

    if (isset($_SESSION["isAdmin"]) && (
        (is_array($_SESSION["isAdmin"]) && ($_SESSION["isAdmin"][0] == 1 || $_SESSION["isAdmin"][1] === "true")) ||
        $_SESSION["isAdmin"] === 1 || $_SESSION["isAdmin"] === true
    )) {
        $isAdminUser = true;
    }
}

// Fetch room catalog
$roomsStmt = $db->query("SELECT * FROM rooms ORDER BY price_per_night ASC");
$roomsList = $roomsStmt ? $roomsStmt->fetchAll(PDO::FETCH_ASSOC) : [];

// Fetch room service menu
$menuStmt = $db->query("SELECT * FROM room_service_menu WHERE is_available = 1 ORDER BY category, price ASC");
$menuList = $menuStmt ? $menuStmt->fetchAll(PDO::FETCH_ASSOC) : [];

// Fetch guest reviews
$reviewsStmt = $db->query("SELECT * FROM reviews WHERE status = 'Approved' ORDER BY review_id DESC LIMIT 6");
$reviewsList = $reviewsStmt ? $reviewsStmt->fetchAll(PDO::FETCH_ASSOC) : [];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Grand Lux Hotel & Suites | Ultra-Luxury Boutique Stays</title>

    <link rel="apple-touch-icon" sizes="180x180" href="image/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="image/favicon/favicon-32x32.png">

    <!-- CSS Runtimes -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/main.css">
</head>
<body>

<!-- Luxury Header & Navigation -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top py-3">
    <div class="container">
        <a class="navbar-brand navbar-brand-gold d-flex align-items-center" href="index.php">
            <i class="fa-solid fa-crown mr-2"></i>
            <span>GRAND LUX <small class="text-uppercase font-weight-normal text-light" style="font-size: 0.65rem; letter-spacing: 2px; display: block;">Hotel & Suites</small></span>
        </a>

        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#mainNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item"><a class="nav-link nav-link-custom" href="#suites">Suites & Rooms</a></li>
                <li class="nav-item"><a class="nav-link nav-link-custom" href="#amenities">Privileges</a></li>
                <li class="nav-item"><a class="nav-link nav-link-custom" href="#dining">In-Room Dining</a></li>
                <li class="nav-item"><a class="nav-link nav-link-custom" href="#reviews">Guest Reviews</a></li>
            </ul>

            <div class="d-flex align-items-center">
                <?php if ($isSessionExists): ?>
                    <div class="dropdown">
                        <button class="btn btn-outline-light dropdown-toggle font-weight-bold d-flex align-items-center py-2 px-3" type="button" data-toggle="dropdown" style="border-radius: 50px;">
                            <i class="fa-solid fa-circle-user text-warning mr-2" style="font-size: 1.2rem;"></i>
                            <span><?php echo htmlspecialchars($username); ?></span>
                            <?php if (!empty($cBookings)): ?>
                                <span class="badge badge-warning text-dark ml-2"><?php echo count($cBookings); ?></span>
                            <?php endif; ?>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right shadow-lg border-0 py-2 mt-2" style="border-radius: 12px; min-width: 230px;">
                            <h6 class="dropdown-header text-uppercase font-weight-bold small text-muted">Guest Portal</h6>
                            <a class="dropdown-item py-2" href="#" data-toggle="modal" data-target="#myBookingsModal">
                                <i class="fa-solid fa-suitcase mr-2 text-primary"></i> View My Bookings
                            </a>
                            <a class="dropdown-item py-2" href="#" data-toggle="modal" data-target="#roomServiceModal">
                                <i class="fa-solid fa-utensils mr-2 text-warning"></i> Order Room Service
                            </a>
                            <a class="dropdown-item py-2" href="#" data-toggle="modal" data-target="#reviewModal">
                                <i class="fa-solid fa-star mr-2 text-warning"></i> Write Stay Review
                            </a>
                            <a class="dropdown-item py-2" href="#" data-toggle="modal" data-target="#myProfileModal">
                                <i class="fa-solid fa-id-card mr-2 text-info"></i> Update Profile
                            </a>
                            <?php if ($isAdminUser): ?>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item py-2 font-weight-bold text-danger" href="admin.php">
                                    <i class="fa-solid fa-shield-halved mr-2"></i> Admin Operations
                                </a>
                            <?php endif; ?>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item py-2 text-danger" href="#" id="sign-out-link">
                                <i class="fa-solid fa-arrow-right-from-bracket mr-2"></i> Sign Out
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="sign-in.php" class="btn btn-outline-light mr-2 font-weight-bold px-3 py-2" style="border-radius: 8px;">
                        <i class="fa-solid fa-arrow-right-to-bracket mr-1"></i> Sign In
                    </a>
                    <a href="register.php" class="btn btn-gold px-3 py-2">
                        <i class="fa-solid fa-user-plus mr-1"></i> Join Privilege
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<!-- Luxury Hero Banner -->
<header class="hero-luxury text-center">
    <div class="container">
        <span class="badge-gold mb-3 text-uppercase"><i class="fa-solid fa-sparkles mr-1"></i> Voted Best Boutique Hotel 2026</span>
        <h1 class="hero-title mb-3">A Sanctuary Beyond Ordinary</h1>
        <p class="hero-subtitle">Immerse yourself in world-class architecture, Michelin-inspired in-room gastronomy, personalized 24/7 butler service, and digital contactless stays.</p>
        <div>
            <?php if ($isSessionExists): ?>
                <button class="btn btn-gold btn-lg mr-2" data-toggle="modal" data-target=".book-now-modal-lg">
                    <i class="fa-solid fa-calendar-check mr-2"></i> Reserve Your Suite
                </button>
            <?php else: ?>
                <button class="btn btn-gold btn-lg mr-2" data-toggle="modal" data-target=".sign-in-to-book-modal">
                    <i class="fa-solid fa-calendar-check mr-2"></i> Reserve Your Suite
                </button>
            <?php endif; ?>
            <a href="#suites" class="btn btn-outline-light btn-lg">Explore Suites</a>
        </div>
    </div>
</header>

<!-- Quick Booking Bar -->
<div class="container">
    <div class="search-widget-card">
        <form onsubmit="handleQuickSearch(event)" class="row align-items-end">
            <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
                <label class="small font-weight-bold text-muted mb-1"><i class="fa-solid fa-calendar-day text-warning mr-1"></i> Check-in Date</label>
                <input type="date" id="quickCheckIn" class="form-control" min="<?php echo Util::dateToday('0'); ?>" value="<?php echo Util::dateToday('0'); ?>" required>
            </div>
            <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
                <label class="small font-weight-bold text-muted mb-1"><i class="fa-solid fa-calendar-check text-warning mr-1"></i> Check-out Date</label>
                <input type="date" id="quickCheckOut" class="form-control" min="<?php echo Util::dateToday('1'); ?>" value="<?php echo date('Y-m-d', strtotime('+2 days')); ?>" required>
            </div>
            <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
                <label class="small font-weight-bold text-muted mb-1"><i class="fa-solid fa-door-open text-warning mr-1"></i> Preferred Suite</label>
                <select id="quickRoomType" class="custom-select">
                    <option value="Single">Classic Single Suite (₹12,030)</option>
                    <option value="Double" selected>Grand Double Room (₹16,657)</option>
                    <option value="Deluxe">Presidential Deluxe Suite (₹23,135)</option>
                </select>
            </div>
            <div class="col-lg-3 col-md-6">
                <button type="submit" class="btn btn-gold btn-block py-2">
                    <i class="fa-solid fa-magnifying-glass mr-1"></i> Check & Book
                </button>
            </div>
        </form>
    </div>
</div>

<!-- SECTION 1: SUITES & ROOMS -->
<section id="suites" class="py-5 mt-4">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge badge-light border text-uppercase font-weight-bold text-muted px-3 py-2">Luxury Accommodations</span>
            <h2 class="display-4 font-weight-bold mt-2">Curated Suites & Penthouses</h2>
            <p class="text-muted max-w-700 mx-auto">Every room is acoustically soundproofed, outfitted with bespoke king mattresses, marble rain showers, and high-floor panoramic skylines.</p>
        </div>

        <div class="row">
            <?php foreach ($roomsList as $room): 
                $amenitiesArr = explode(',', $room['amenities'] ?: '');
            ?>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="room-card">
                    <div class="room-card-img-wrapper">
                        <img src="<?php echo htmlspecialchars($room['image']); ?>" alt="<?php echo htmlspecialchars($room['title']); ?>">
                        <span class="room-tag"><i class="fa-solid fa-door-closed mr-1"></i> Suite <?php echo $room['room_number']; ?></span>
                        <span class="room-price-badge">₹<?php echo number_format($room['price_per_night']); ?> <small class="text-muted">/ night</small></span>
                    </div>
                    <div class="p-4 d-flex flex-column flex-grow-1">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="badge badge-warning text-dark font-weight-bold"><?php echo htmlspecialchars($room['type']); ?> Class</span>
                            <small class="text-muted"><i class="fa-solid fa-arrows-left-right-to-line mr-1"></i> <?php echo $room['size_sqft']; ?> sq.ft</small>
                        </div>
                        <h4 class="font-weight-bold mb-2"><?php echo htmlspecialchars($room['title']); ?></h4>
                        <p class="text-muted small mb-3 flex-grow-1"><?php echo htmlspecialchars($room['description']); ?></p>
                        
                        <div class="mb-3">
                            <?php foreach (array_slice($amenitiesArr, 0, 4) as $am): ?>
                                <span class="room-amenity-pill"><?php echo trim($am); ?></span>
                            <?php endforeach; ?>
                        </div>

                        <div class="pt-3 border-top d-flex justify-content-between align-items-center">
                            <small class="text-muted"><i class="fa-solid fa-user-group mr-1"></i> Up to <?php echo $room['capacity_adults']; ?> Adults</small>
                            <?php if ($isSessionExists): ?>
                                <button type="button" class="btn btn-gold btn-sm" onclick="openReservationModal('<?php echo $room['type']; ?>')">
                                    Book Suite <i class="fa-solid fa-arrow-right ml-1"></i>
                                </button>
                            <?php else: ?>
                                <button type="button" class="btn btn-gold btn-sm" data-toggle="modal" data-target=".sign-in-to-book-modal">
                                    Book Suite <i class="fa-solid fa-arrow-right ml-1"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- SECTION 2: PRIVILEGES & AMENITIES -->
<section id="amenities" class="py-5 bg-white border-top border-bottom">
    <div class="container py-4">
        <div class="text-center mb-5">
            <span class="badge badge-light border text-uppercase font-weight-bold text-muted px-3 py-2">The Grand Experience</span>
            <h2 class="display-4 font-weight-bold mt-2">Bespoke 5-Star Privileges</h2>
            <p class="text-muted max-w-700 mx-auto">Complimentary elite hospitality crafted to elevate every moment of your stay.</p>
        </div>

        <div class="row text-center">
            <div class="col-md-4 col-sm-6 mb-4">
                <div class="p-4 rounded-lg border h-100 bg-light">
                    <div class="mb-3 text-warning" style="font-size: 2.5rem;"><i class="fa-solid fa-water-ladder"></i></div>
                    <h5 class="font-weight-bold">Rooftop Infinity Pool</h5>
                    <p class="text-muted small mb-0">Heated 360-degree skyline pool with private cabanas and sunset mixology service.</p>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 mb-4">
                <div class="p-4 rounded-lg border h-100 bg-light">
                    <div class="mb-3 text-warning" style="font-size: 2.5rem;"><i class="fa-solid fa-qrcode"></i></div>
                    <h5 class="font-weight-bold">Contactless QR Check-In</h5>
                    <p class="text-muted small mb-0">Skip the lobby queue! Instant digital boarding pass & QR keycard right on your phone.</p>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 mb-4">
                <div class="p-4 rounded-lg border h-100 bg-light">
                    <div class="mb-3 text-warning" style="font-size: 2.5rem;"><i class="fa-solid fa-wifi"></i></div>
                    <h5 class="font-weight-bold">Gigabit Fiber Wi-Fi</h5>
                    <p class="text-muted small mb-0">Dedicated high-speed fiber internet in all suites with zero latency for work and streaming.</p>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 mb-4">
                <div class="p-4 rounded-lg border h-100 bg-light">
                    <div class="mb-3 text-warning" style="font-size: 2.5rem;"><i class="fa-solid fa-spa"></i></div>
                    <h5 class="font-weight-bold">Ayurvedic Spa & Hydro</h5>
                    <p class="text-muted small mb-0">Holistic rejuvenation treatments, eucalyptus steam rooms, and hot stone therapy.</p>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 mb-4">
                <div class="p-4 rounded-lg border h-100 bg-light">
                    <div class="mb-3 text-warning" style="font-size: 2.5rem;"><i class="fa-solid fa-bell-concierge"></i></div>
                    <h5 class="font-weight-bold">24/7 Butler & Dining</h5>
                    <p class="text-muted small mb-0">Personalized packing, garment pressing, and 24-hour gourmet chef room service.</p>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 mb-4">
                <div class="p-4 rounded-lg border h-100 bg-light">
                    <div class="mb-3 text-warning" style="font-size: 2.5rem;"><i class="fa-solid fa-car"></i></div>
                    <h5 class="font-weight-bold">Chauffeur Airport Transfer</h5>
                    <p class="text-muted small mb-0">Luxury Mercedes fleet available for executive airport pickups and city tours on request.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- SECTION 3: IN-ROOM DINING & CULINARY -->
<section id="dining" class="py-5">
    <div class="container py-4">
        <div class="d-flex flex-wrap justify-content-between align-items-end mb-4">
            <div>
                <span class="badge badge-light border text-uppercase font-weight-bold text-muted px-3 py-2">Gastronomic Excellence</span>
                <h2 class="display-4 font-weight-bold mt-2">In-Room Dining Menu</h2>
                <p class="text-muted mb-0">Indulge in artisanal delicacies served piping hot right inside your suite.</p>
            </div>
            <div class="mt-3 mt-md-0">
                <?php if ($isSessionExists): ?>
                    <button class="btn btn-gold" data-toggle="modal" data-target="#roomServiceModal">
                        <i class="fa-solid fa-utensils mr-2"></i> Order Room Service Now
                    </button>
                <?php else: ?>
                    <button class="btn btn-gold" data-toggle="modal" data-target=".sign-in-to-book-modal">
                        <i class="fa-solid fa-utensils mr-2"></i> Order Room Service
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <div class="row">
            <?php foreach (array_slice($menuList, 0, 4) as $item): ?>
            <div class="col-md-6 mb-3">
                <div class="menu-item-card d-flex justify-content-between align-items-center p-3">
                    <div>
                        <div class="d-flex align-items-center mb-1">
                            <h5 class="font-weight-bold mb-0 mr-2"><?php echo htmlspecialchars($item['name']); ?></h5>
                            <?php if ($item['badge']): ?>
                                <span class="badge badge-warning text-dark small font-weight-bold"><?php echo $item['badge']; ?></span>
                            <?php endif; ?>
                        </div>
                        <p class="text-muted small mb-0"><?php echo htmlspecialchars($item['description']); ?></p>
                        <small class="text-secondary font-weight-bold text-uppercase"><?php echo $item['category']; ?></small>
                    </div>
                    <div class="text-right pl-3">
                        <div class="font-weight-bold text-success h5 mb-2">₹<?php echo number_format($item['price']); ?></div>
                        <?php if ($isSessionExists): ?>
                            <button class="btn btn-outline-dark btn-sm" data-toggle="modal" data-target="#roomServiceModal">Order</button>
                        <?php else: ?>
                            <button class="btn btn-outline-dark btn-sm" data-toggle="modal" data-target=".sign-in-to-book-modal">Order</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- SECTION 4: GUEST REVIEWS -->
<section id="reviews" class="py-5 bg-white border-top">
    <div class="container py-4">
        <div class="d-flex flex-wrap justify-content-between align-items-end mb-4">
            <div>
                <span class="badge badge-light border text-uppercase font-weight-bold text-muted px-3 py-2">Testimonials</span>
                <h2 class="display-4 font-weight-bold mt-2">Verified Guest Reviews</h2>
                <div class="text-warning">
                    <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                    <span class="text-dark font-weight-bold ml-2">4.9 / 5.0 (Aggregate Rating)</span>
                </div>
            </div>
            <div class="mt-3 mt-md-0">
                <?php if ($isSessionExists): ?>
                    <button class="btn btn-outline-dark" data-toggle="modal" data-target="#reviewModal">
                        <i class="fa-regular fa-pen-to-square mr-1"></i> Write a Review
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <div class="row">
            <?php foreach ($reviewsList as $rev): ?>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100 p-4 border shadow-sm rounded-lg">
                    <div class="text-warning mb-2">
                        <?php for ($i = 0; $i < (int)$rev['rating']; $i++): ?>
                            <i class="fa-solid fa-star"></i>
                        <?php endfor; ?>
                    </div>
                    <p class="text-dark italic mb-3 flex-grow-1">"<?php echo htmlspecialchars($rev['comment']); ?>"</p>
                    <div class="d-flex align-items-center pt-3 border-top">
                        <div class="rounded-circle bg-dark text-warning font-weight-bold d-flex align-items-center justify-content-center mr-3" style="width: 42px; height: 42px;">
                            <?php echo strtoupper(substr($rev['customer_name'], 0, 1)); ?>
                        </div>
                        <div>
                            <h6 class="font-weight-bold mb-0"><?php echo htmlspecialchars($rev['customer_name']); ?></h6>
                            <small class="text-success"><i class="fa-solid fa-circle-check mr-1"></i> Verified Stay</small>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="bg-dark text-light py-5 border-top" style="background-color: #0b132b !important;">
    <div class="container">
        <div class="row">
            <div class="col-md-5 mb-4 mb-md-0">
                <h4 class="font-weight-bold text-warning mb-2"><i class="fa-solid fa-crown mr-2"></i> Grand Lux Hotel</h4>
                <p class="text-muted small">Where world-class architecture meets personalized luxury. A sanctuary beyond ordinary designed for unforgettable experiences.</p>
                <div class="text-muted small">&copy; <?php echo date('Y'); ?> Grand Lux Hotel & Suites. All rights reserved.</div>
            </div>
            <div class="col-md-3 col-6 mb-4 mb-md-0">
                <h6 class="text-uppercase text-light font-weight-bold mb-3">Hotel Navigation</h6>
                <ul class="list-unstyled small">
                    <li><a href="#suites" class="text-muted">Suites & Rooms</a></li>
                    <li><a href="#amenities" class="text-muted">Privileges & Spa</a></li>
                    <li><a href="#dining" class="text-muted">In-Room Dining</a></li>
                    <li><a href="#reviews" class="text-muted">Guest Reviews</a></li>
                </ul>
            </div>
            <div class="col-md-4 col-6">
                <h6 class="text-uppercase text-light font-weight-bold mb-3">Concierge Desk</h6>
                <div class="text-muted small mb-1"><i class="fa-solid fa-location-dot text-warning mr-2"></i> Grand Lux Boulevard, Luxury Mile</div>
                <div class="text-muted small mb-1"><i class="fa-solid fa-phone text-warning mr-2"></i> +91 (0) 11 4890 1200</div>
                <div class="text-muted small mb-3"><i class="fa-solid fa-envelope text-warning mr-2"></i> concierge@grandluxhotel.com</div>
                <button class="btn btn-outline-warning btn-sm" onclick="toggleConcierge()"><i class="fa-solid fa-comments mr-1"></i> Live Virtual Assistant</button>
            </div>
        </div>
    </div>
</footer>

<!-- ============================================== -->
<!-- MODALS SECTION                                 -->
<!-- ============================================== -->

<!-- 1. RESERVATION FORM MODAL -->
<?php if ($isSessionExists): ?>
<div class="modal fade book-now-modal-lg" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg border-0" style="border-radius: 18px; overflow: hidden;">
            <div class="modal-header bg-dark text-white px-4 py-3" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
                <h5 class="modal-title font-weight-bold"><i class="fa-solid fa-calendar-check text-warning mr-2"></i> Reserve Your Stay</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body p-4" id="reservationModalBody">
                <form role="form" autocomplete="off" method="post" id="multiStepRsvnForm">
                    <input type="hidden" name="cid" value="<?php echo $cHandler ? $cHandler->getId() : ''; ?>">
                    
                    <!-- TAB 1: Dates & Suite -->
                    <div class="rsvnTab">
                        <h6 class="font-weight-bold text-dark border-bottom pb-2 mb-3">Step 1: Check-in Dates & Suite Preference</h6>
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label font-weight-bold">Check-in <span class="red-asterisk">*</span></label>
                            <div class="col-sm-9">
                                <input type="date" class="form-control" name="startDate" id="modalStartDate" min="<?php echo Util::dateToday('0'); ?>" value="<?php echo Util::dateToday('0'); ?>" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label font-weight-bold">Check-out <span class="red-asterisk">*</span></label>
                            <div class="col-sm-9">
                                <input type="date" class="form-control" name="endDate" id="modalEndDate" min="<?php echo Util::dateToday('1'); ?>" value="<?php echo date('Y-m-d', strtotime('+2 days')); ?>" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label font-weight-bold">Suite Category <span class="red-asterisk">*</span></label>
                            <div class="col-sm-9">
                                <select required class="custom-select" name="roomType" id="modalRoomType">
                                    <option value="Single">Classic Single Suite (₹12,030 / night)</option>
                                    <option value="Double" selected>Grand Double Room (₹16,657 / night)</option>
                                    <option value="Deluxe">Presidential Deluxe Suite (₹23,135 / night)</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label font-weight-bold">Smoking Preference</label>
                            <div class="col-sm-9">
                                <select class="custom-select" name="roomRequirement">
                                    <option value="no preference" selected>No preference</option>
                                    <option value="non smoking">Non-smoking suite</option>
                                    <option value="smoking">Smoking permitted terrace</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 2: Guests & Special Requests -->
                    <div class="rsvnTab">
                        <h6 class="font-weight-bold text-dark border-bottom pb-2 mb-3">Step 2: Guests & Special Requests</h6>
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label font-weight-bold">Adults (12+ yrs) <span class="red-asterisk">*</span></label>
                            <div class="col-sm-9">
                                <select required class="custom-select" name="adults">
                                    <option value="1">1 Adult</option>
                                    <option value="2" selected>2 Adults</option>
                                    <option value="3">3 Adults</option>
                                    <option value="4">4 Adults</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label font-weight-bold">Children (0-11 yrs)</label>
                            <div class="col-sm-9">
                                <select class="custom-select" name="children">
                                    <option value="0" selected>0 Children</option>
                                    <option value="1">1 Child</option>
                                    <option value="2">2 Children</option>
                                    <option value="3">3 Children</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label font-weight-bold">Special Requests</label>
                            <div class="col-sm-9">
                                <textarea rows="3" name="specialRequests" class="form-control" placeholder="e.g. High floor, anniversary cake, late check-in, feather pillows"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 3: Cost Summary -->
                    <div class="rsvnTab">
                        <h6 class="font-weight-bold text-dark border-bottom pb-2 mb-3">Step 3: Review Tariff & Confirm</h6>
                        <div class="bg-light p-3 rounded mb-3 border">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Stay Duration:</span>
                                <span class="font-weight-bold"><span class="numNightsTxt">2</span> Night(s) (<span class="fromToTxt"></span>)</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Base Rate:</span>
                                <span class="font-weight-bold">₹<span class="roomPricePerNightTxt">16,657</span> / night</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Room Charges:</span>
                                <span class="font-weight-bold">₹<span class="roomPriceTxt">33,314</span></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">GST & Hospitality Taxes:</span>
                                <span class="font-weight-bold">₹<span class="taxesTxt">0</span> (Included)</span>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between h5 font-weight-bold text-dark mb-0">
                                <span>Total Estimated Amount:</span>
                                <span class="text-success">₹<span class="totalTxt">33,314</span></span>
                            </div>
                        </div>
                        <div class="small text-muted mb-2">
                            <i class="fa-solid fa-shield-halved text-success mr-1"></i> Free cancellation up to 24 hours prior to check-in. Digital QR check-in pass will be issued immediately upon booking.
                        </div>
                        <div class="bookedDateTxt d-none"><?php echo date('Y-m-d'); ?></div>
                    </div>

                    <!-- Step Dots -->
                    <div class="text-center my-3">
                        <span class="step"></span>
                        <span class="step"></span>
                        <span class="step"></span>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="d-flex justify-content-between border-top pt-3">
                        <button type="button" class="btn btn-outline-secondary" id="rsvnPrevBtn" onclick="rsvnNextPrev(-1)">Previous</button>
                        <button type="button" class="btn btn-gold" id="rsvnNextBtn" onclick="rsvnNextPrev(1)" readySubmit="false">Next</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- 2. SIGN IN REQUIRED MODAL (FOR GUEST VISITORS) -->
<div class="modal fade sign-in-to-book-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content text-center p-4 shadow-lg border-0" style="border-radius: 16px;">
            <div class="mb-3 text-warning" style="font-size: 3rem;"><i class="fa-solid fa-hotel"></i></div>
            <h4 class="font-weight-bold">Authentication Required</h4>
            <p class="text-muted">Please sign in or create a guest account to reserve your suite, view live tariffs, and unlock contactless QR check-in.</p>
            <div class="d-flex justify-content-center mt-3">
                <a href="sign-in.php" class="btn btn-gold mr-2 px-4"><i class="fa-solid fa-arrow-right-to-bracket mr-1"></i> Sign In</a>
                <a href="register.php" class="btn btn-outline-dark px-4"><i class="fa-solid fa-user-plus mr-1"></i> Register</a>
            </div>
        </div>
    </div>
</div>

<!-- 3. MY BOOKINGS MODAL -->
<?php if ($isSessionExists): ?>
<div class="modal fade" id="myBookingsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg border-0" style="border-radius: 18px; overflow: hidden;">
            <div class="modal-header bg-dark text-white px-4 py-3" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
                <h5 class="modal-title font-weight-bold"><i class="fa-solid fa-suitcase text-warning mr-2"></i> My Reservations & Stays</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-4">
                <?php if (empty($cBookings)): ?>
                    <div class="text-center py-5">
                        <i class="fa-regular fa-calendar-xmark text-muted mb-3" style="font-size: 3.5rem;"></i>
                        <h5>No reservations found</h5>
                        <p class="text-muted">You haven't reserved any suites yet. Experience Grand Lux today!</p>
                        <button class="btn btn-gold" data-dismiss="modal" data-toggle="modal" data-target=".book-now-modal-lg">Book a Suite Now</button>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th>Booking #</th>
                                    <th>Suite Type</th>
                                    <th>Stay Dates</th>
                                    <th>Nights</th>
                                    <th>Total Tariff</th>
                                    <th>Status</th>
                                    <th>Smart Passes & Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cBookings as $bk): 
                                    $st = strtoupper($bk['status']);
                                    $badge = 'badge-warning text-dark';
                                    if ($st === 'CONFIRMED') $badge = 'badge-success';
                                    elseif ($st === 'CHECKED_IN') $badge = 'badge-primary';
                                    elseif ($st === 'CANCELLED') $badge = 'badge-danger';
                                ?>
                                <tr>
                                    <td class="font-weight-bold">#<?php echo $bk['id']; ?></td>
                                    <td>
                                        <span class="badge badge-light border"><?php echo htmlspecialchars($bk['type']); ?></span>
                                        <div class="small text-muted"><?php echo htmlspecialchars($bk['requirement']); ?></div>
                                    </td>
                                    <td>
                                        <div class="small font-weight-bold"><?php echo $bk['start']; ?> &rarr; <?php echo $bk['end']; ?></div>
                                        <small class="text-muted">Booked: <?php echo $bk['booked_date']; ?></small>
                                    </td>
                                    <td><?php echo $bk['nights']; ?> Night(s)</td>
                                    <td class="font-weight-bold text-success">₹<?php echo number_format($bk['total_price']); ?></td>
                                    <td><span class="badge <?php echo $badge; ?> badge-status"><?php echo $st; ?></span></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-dark" onclick="openBoardingPass(<?php echo htmlspecialchars(json_encode($bk)); ?>)">
                                                <i class="fa-solid fa-qrcode mr-1"></i> QR Pass
                                            </button>
                                            <button class="btn btn-outline-secondary" onclick="openInvoice(<?php echo htmlspecialchars(json_encode($bk)); ?>)">
                                                <i class="fa-solid fa-file-invoice mr-1"></i> Invoice
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- 4. DIGITAL BOARDING PASS & QR PASS MODAL -->
<div class="modal fade" id="boardingPassModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 18px; overflow: hidden;">
            <div class="boarding-pass-card">
                <div class="boarding-pass-header text-center">
                    <span class="badge badge-warning text-dark font-weight-bold text-uppercase mb-1">Digital Smart Keycard</span>
                    <h4 class="font-weight-bold mb-0">Grand Lux Hotel & Suites</h4>
                    <small class="text-light">Contactless Front-Desk Verification Pass</small>
                </div>
                <div class="p-4 text-center">
                    <div class="d-flex justify-content-between mb-3 text-left">
                        <div>
                            <small class="text-muted d-block">Guest Name</small>
                            <strong id="passGuestName"><?php echo htmlspecialchars($username); ?></strong>
                        </div>
                        <div class="text-right">
                            <small class="text-muted d-block">Booking Reference</small>
                            <strong id="passBookingId">#12</strong>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mb-4 text-left">
                        <div>
                            <small class="text-muted d-block">Suite Category</small>
                            <span class="badge badge-dark" id="passSuiteType">Deluxe</span>
                        </div>
                        <div class="text-right">
                            <small class="text-muted d-block">Check-in Date</small>
                            <strong id="passCheckIn">2026-11-01</strong>
                        </div>
                    </div>

                    <div class="boarding-qr-box my-2">
                        <div id="qrcodeCanvas" class="d-flex justify-content-center"></div>
                        <small class="text-muted d-block mt-2 font-weight-bold">Scan at reception for instant express check-in</small>
                    </div>

                    <div class="mt-4 pt-3 border-top">
                        <button type="button" class="btn btn-outline-dark btn-sm" onclick="window.print()">
                            <i class="fa-solid fa-print mr-1"></i> Print Keycard
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 5. PRINTABLE INVOICE / RECEIPT MODAL -->
<div class="modal fade" id="invoiceModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content p-4 shadow-lg border-0" style="border-radius: 16px;">
            <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-3">
                <div>
                    <h3 class="font-weight-bold mb-0 text-dark"><i class="fa-solid fa-crown text-warning mr-2"></i> Grand Lux Hotel</h3>
                    <small class="text-muted">Boulevard Luxury Mile, New Delhi | GSTIN: 07AAACH7409R1ZZ</small>
                </div>
                <div class="text-right">
                    <span class="badge badge-success px-3 py-2 text-uppercase font-weight-bold">Official Receipt</span>
                    <div class="small font-weight-bold mt-1" id="invNumber">INV-2026-0012</div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-sm-6">
                    <h6 class="font-weight-bold text-muted small text-uppercase">Billed To:</h6>
                    <div class="font-weight-bold" id="invGuestName"><?php echo htmlspecialchars($username); ?></div>
                    <div class="small text-muted" id="invGuestEmail"><?php echo htmlspecialchars($cHandler ? $cHandler->getEmail() : ''); ?></div>
                    <div class="small text-muted" id="invGuestPhone"><?php echo htmlspecialchars($cHandler ? $cHandler->getPhone() : ''); ?></div>
                </div>
                <div class="col-sm-6 text-sm-right">
                    <h6 class="font-weight-bold text-muted small text-uppercase">Reservation Details:</h6>
                    <div class="small"><span class="text-muted">Stay Dates:</span> <strong id="invDates">2026-11-01 to 2026-11-03</strong></div>
                    <div class="small"><span class="text-muted">Duration:</span> <strong id="invNights">2 Night(s)</strong></div>
                    <div class="small"><span class="text-muted">Suite:</span> <strong id="invSuite">Deluxe Suite</strong></div>
                </div>
            </div>

            <table class="table table-bordered mb-4">
                <thead class="thead-light">
                    <tr>
                        <th>Item Description</th>
                        <th width="100" class="text-center">Rate</th>
                        <th width="80" class="text-center">Nights</th>
                        <th width="120" class="text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <strong id="invItemTitle">Luxury Suite Accommodation</strong>
                            <div class="small text-muted">Complimentary breakfast, high-speed Wi-Fi & pool access included</div>
                        </td>
                        <td class="text-center" id="invRate">₹16,657</td>
                        <td class="text-center" id="invNightsCount">2</td>
                        <td class="text-right font-weight-bold" id="invRoomTotal">₹33,314</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="text-right text-muted small">CGST (6%) & SGST (6%)</td>
                        <td class="text-right small">Included</td>
                    </tr>
                    <tr class="bg-light">
                        <td colspan="3" class="text-right font-weight-bold">Grand Total (INR):</td>
                        <td class="text-right font-weight-bold text-success h5 mb-0" id="invGrandTotal">₹33,314</td>
                    </tr>
                </tbody>
            </table>

            <div class="d-flex justify-content-between align-items-center border-top pt-3">
                <small class="text-muted"><i class="fa-solid fa-lock text-success mr-1"></i> Digitally generated by Grand Lux Central Reservation System</small>
                <div>
                    <button class="btn btn-dark btn-sm" onclick="window.print()"><i class="fa-solid fa-print mr-1"></i> Print Invoice</button>
                    <button class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 6. IN-ROOM DINING ORDER MODAL -->
<div class="modal fade" id="roomServiceModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg border-0" style="border-radius: 18px; overflow: hidden;">
            <div class="modal-header bg-dark text-white px-4 py-3" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
                <h5 class="modal-title font-weight-bold"><i class="fa-solid fa-bell-concierge text-warning mr-2"></i> In-Room Dining & Kitchen Order</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-7 border-right">
                        <h6 class="font-weight-bold mb-3">Select Culinary Items:</h6>
                        <div style="max-height: 380px; overflow-y: auto; padding-right: 8px;">
                            <?php foreach ($menuList as $mItem): ?>
                            <div class="d-flex justify-content-between align-items-center p-2 mb-2 border rounded bg-white">
                                <div>
                                    <div class="font-weight-bold"><?php echo htmlspecialchars($mItem['name']); ?></div>
                                    <small class="text-success font-weight-bold">₹<?php echo number_format($mItem['price']); ?></small>
                                    <span class="badge badge-light border small"><?php echo $mItem['category']; ?></span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <button class="btn btn-sm btn-outline-secondary px-2" onclick="changeQty(<?php echo $mItem['item_id']; ?>, -1, '<?php echo addslashes($mItem['name']); ?>', <?php echo $mItem['price']; ?>)">-</button>
                                    <span class="mx-2 font-weight-bold item-qty-display" id="qty_<?php echo $mItem['item_id']; ?>">0</span>
                                    <button class="btn btn-sm btn-outline-dark px-2" onclick="changeQty(<?php echo $mItem['item_id']; ?>, 1, '<?php echo addslashes($mItem['name']); ?>', <?php echo $mItem['price']; ?>)">+</button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="col-md-5">
                        <h6 class="font-weight-bold mb-3">Order Summary:</h6>
                        <div class="form-group mb-2">
                            <label class="small font-weight-bold text-muted mb-1">Deliver to Suite / Room:</label>
                            <input type="text" id="orderRoomNumber" class="form-control form-control-sm" placeholder="e.g. Suite 201 or Front Desk" value="Suite 201">
                        </div>
                        <div class="form-group mb-2">
                            <label class="small font-weight-bold text-muted mb-1">Chef Instructions:</label>
                            <textarea id="orderInstructions" class="form-control form-control-sm" rows="2" placeholder="e.g. Extra spicy, less butter, cutlery for 2"></textarea>
                        </div>

                        <div class="bg-light p-3 rounded border my-3">
                            <div id="cartItemsList" class="small text-muted mb-2">No items selected yet.</div>
                            <div class="d-flex justify-content-between font-weight-bold h6 text-dark border-top pt-2 mb-0">
                                <span>Total Amount:</span>
                                <span class="text-success" id="cartTotalText">₹0</span>
                            </div>
                        </div>

                        <button type="button" class="btn btn-gold btn-block" onclick="submitRoomServiceOrder()">
                            <i class="fa-solid fa-paper-plane mr-1"></i> Place Kitchen Order
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 7. GUEST REVIEW SUBMISSION MODAL -->
<div class="modal fade" id="reviewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg border-0" style="border-radius: 16px;">
            <div class="modal-header bg-dark text-white px-4 py-3">
                <h5 class="modal-title font-weight-bold"><i class="fa-solid fa-star text-warning mr-2"></i> Share Your Experience</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-4">
                <form id="reviewSubmitForm" onsubmit="submitGuestReview(event)">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold small text-muted">Your Star Rating:</label>
                        <select id="reviewRating" class="custom-select">
                            <option value="5" selected>⭐⭐⭐⭐⭐ (5/5 Exceptional)</option>
                            <option value="4">⭐⭐⭐⭐ (4/5 Very Good)</option>
                            <option value="3">⭐⭐⭐ (3/5 Average)</option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold small text-muted">Your Comments & Feedback:</label>
                        <textarea id="reviewComment" class="form-control" rows="4" placeholder="Tell us about the suite ambiance, staff service, food, and amenities..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-gold btn-block">
                        <i class="fa-solid fa-check mr-1"></i> Post Verified Review
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- 8. UPDATE PROFILE MODAL -->
<?php if ($isSessionExists): ?>
<div class="modal fade" id="myProfileModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg border-0" style="border-radius: 16px;">
            <div class="modal-header bg-dark text-white px-4 py-3">
                <h5 class="modal-title font-weight-bold"><i class="fa-solid fa-user-pen text-warning mr-2"></i> Update Your Profile</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-4">
                <form id="update-profile-form" method="post">
                    <input type="hidden" name="customerId" value="<?php echo $cHandler ? $cHandler->getId() : ''; ?>">
                    <input type="hidden" name="updateProfileSubmitBtn" value="updatebtn">
                    <div class="form-group">
                        <label class="small font-weight-bold text-muted">Full Name</label>
                        <input type="text" class="form-control" name="updateFullName" value="<?php echo htmlspecialchars($cHandler ? $cHandler->getFullName() : ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold text-muted">Phone Number</label>
                        <input type="text" class="form-control" name="updatePhoneNumber" value="<?php echo htmlspecialchars($cHandler ? $cHandler->getPhone() : ''); ?>">
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold text-muted">Email (Read Only)</label>
                        <input type="email" class="form-control bg-light" name="updateEmail" value="<?php echo htmlspecialchars($cHandler ? $cHandler->getEmail() : ''); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold text-muted">New Password (leave empty to keep current)</label>
                        <input type="password" class="form-control" name="updatePassword" placeholder="••••••••">
                    </div>
                    <button type="submit" class="btn btn-gold btn-block mt-3">
                        <i class="fa-solid fa-floppy-disk mr-1"></i> Save Changes
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ============================================== -->
<!-- AI VIRTUAL CONCIERGE ASSISTANT CHATBOT WIDGET  -->
<!-- ============================================== -->
<div class="concierge-launcher" onclick="toggleConcierge()" title="Talk to AI Hotel Concierge">
    <i class="fa-solid fa-robot"></i>
    <span class="concierge-pulse"></span>
</div>

<div class="concierge-window" id="conciergeWindow">
    <div class="concierge-header">
        <div class="d-flex align-items-center">
            <div class="rounded-circle bg-warning text-dark font-weight-bold d-flex align-items-center justify-content-center mr-2" style="width: 34px; height: 34px;">
                <i class="fa-solid fa-crown small"></i>
            </div>
            <div>
                <strong class="d-block text-white" style="font-size: 0.95rem;">Grand Lux Concierge</strong>
                <small class="text-success" style="font-size: 0.72rem;"><i class="fa-solid fa-circle text-success mr-1"></i> Online & Ready to Help</small>
            </div>
        </div>
        <button class="btn btn-link text-white p-0" onclick="toggleConcierge()"><i class="fa-solid fa-xmark"></i></button>
    </div>

    <div class="concierge-messages" id="conciergeMessages">
        <div class="concierge-msg concierge-msg-bot">
            Namaste & Welcome! 🙏 I am your <strong>Grand Lux Virtual Concierge</strong>. How may I elevate your stay today?
            <div class="mt-2">
                <span class="concierge-chip" onclick="askConcierge('Recommend a room for couple')">✨ Best for Couples</span>
                <span class="concierge-chip" onclick="askConcierge('What is room service menu?')">🍽️ Dining Menu</span>
                <span class="concierge-chip" onclick="askConcierge('Check in check out timings')">🕒 Check-in Timing</span>
                <span class="concierge-chip" onclick="askConcierge('What are the suite tariffs?')">💰 Suite Tariffs</span>
            </div>
        </div>
    </div>

    <div class="concierge-input-area">
        <input type="text" id="conciergeInput" class="form-control form-control-sm" placeholder="Ask about suites, tariffs, khana, policies..." onkeypress="if(event.key === 'Enter') sendConciergeMessage()">
        <button class="btn btn-gold btn-sm px-3" onclick="sendConciergeMessage()"><i class="fa-solid fa-paper-plane"></i></button>
    </div>
</div>

<!-- SCRIPTS -->
<script src="https://code.jquery.com/jquery-3.6.3.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script src="js/utilityFunctions.js"></script>
<script src="js/multiStepsRsvn.js"></script>
<script src="js/form-submission.js"></script>

<script>
// Open Reservation Modal with preselected room
function openReservationModal(roomType) {
    if (roomType) {
        $('#modalRoomType').val(roomType);
    }
    $('.book-now-modal-lg').modal('show');
}

// Quick Search from Hero Widget
function handleQuickSearch(e) {
    e.preventDefault();
    let cin = $('#quickCheckIn').val();
    let cout = $('#quickCheckOut').val();
    let rtype = $('#quickRoomType').val();

    $('#modalStartDate').val(cin);
    $('#modalEndDate').val(cout);
    $('#modalRoomType').val(rtype);

    <?php if ($isSessionExists): ?>
        $('.book-now-modal-lg').modal('show');
    <?php else: ?>
        $('.sign-in-to-book-modal').modal('show');
    <?php endif; ?>
}

// Digital Boarding Pass & QR Code
function openBoardingPass(booking) {
    $('#passGuestName').text('<?php echo htmlspecialchars($username); ?>');
    $('#passBookingId').text('#' + booking.id);
    $('#passSuiteType').text(booking.type + ' Suite');
    $('#passCheckIn').text(booking.start);

    $('#qrcodeCanvas').empty();
    let qrData = "GRANDLUX-PASS|BOOKING:" + booking.id + "|GUEST:<?php echo htmlspecialchars($username); ?>|START:" + booking.start + "|HASH:" + (booking.hash || 'GLX' + booking.id);
    new QRCode(document.getElementById("qrcodeCanvas"), {
        text: qrData,
        width: 140,
        height: 140,
        colorDark : "#0f172a",
        colorLight : "#f8fafc",
        correctLevel : QRCode.CorrectLevel.H
    });

    $('#boardingPassModal').modal('show');
}

// Invoice Generator
function openInvoice(booking) {
    $('#invNumber').text('INV-' + new Date().getFullYear() + '-' + booking.id.toString().padStart(4, '0'));
    $('#invDates').text(booking.start + ' to ' + booking.end);
    $('#invNights').text(booking.nights + ' Night(s)');
    $('#invSuite').text(booking.type + ' Suite');
    $('#invItemTitle').text(booking.type + ' Suite Accommodation');
    
    let rate = booking.total_price / (booking.nights || 1);
    $('#invRate').text('₹' + Math.round(rate).toLocaleString());
    $('#invNightsCount').text(booking.nights);
    $('#invRoomTotal').text('₹' + Number(booking.total_price).toLocaleString());
    $('#invGrandTotal').text('₹' + Number(booking.total_price).toLocaleString());

    $('#invoiceModal').modal('show');
}

// In-Room Dining Shopping Cart state
let cart = {};
function changeQty(id, delta, name, price) {
    if (!cart[id]) cart[id] = { id: id, name: name, price: price, qty: 0 };
    cart[id].qty = Math.max(0, cart[id].qty + delta);
    $('#qty_' + id).text(cart[id].qty);
    updateCartUI();
}

function updateCartUI() {
    let html = '';
    let total = 0;
    let count = 0;
    for (let id in cart) {
        if (cart[id].qty > 0) {
            count++;
            let line = cart[id].qty * cart[id].price;
            total += line;
            html += '<div class="d-flex justify-content-between mb-1"><span>' + cart[id].name + ' &times; ' + cart[id].qty + '</span><span>₹' + line.toLocaleString() + '</span></div>';
        }
    }
    if (count === 0) {
        $('#cartItemsList').html('No items selected yet.');
    } else {
        $('#cartItemsList').html(html);
    }
    $('#cartTotalText').text('₹' + total.toLocaleString());
}

function submitRoomServiceOrder() {
    let itemsArr = [];
    let total = 0;
    for (let id in cart) {
        if (cart[id].qty > 0) {
            itemsArr.push(cart[id]);
            total += cart[id].qty * cart[id].price;
        }
    }
    if (itemsArr.length === 0) {
        alert('Please add at least 1 culinary item to your order.');
        return;
    }

    let roomNo = $('#orderRoomNumber').val();
    let notes = $('#orderInstructions').val();

    $.post('app/api/room_service.php', {
        action: 'place_order',
        room_number: roomNo,
        items_json: JSON.stringify(itemsArr),
        total_amount: total,
        instructions: notes
    }, function(res) {
        if (res.status === 'success') {
            alert(res.message);
            $('#roomServiceModal').modal('hide');
            cart = {};
            $('.item-qty-display').text('0');
            updateCartUI();
        } else {
            alert(res.message);
        }
    }, 'json');
}

// Review Submission
function submitGuestReview(e) {
    e.preventDefault();
    let rating = $('#reviewRating').val();
    let comment = $('#reviewComment').val();

    $.post('app/api/reviews.php', {
        action: 'submit',
        rating: rating,
        comment: comment
    }, function(res) {
        alert(res.message);
        if (res.status === 'success') {
            $('#reviewModal').modal('hide');
            location.reload();
        }
    }, 'json');
}

// AI Virtual Concierge Chatbot
function toggleConcierge() {
    let w = document.getElementById('conciergeWindow');
    if (w.style.display === 'flex') {
        w.style.display = 'none';
    } else {
        w.style.display = 'flex';
        $('#conciergeInput').focus();
    }
}

function askConcierge(text) {
    $('#conciergeInput').val(text);
    sendConciergeMessage();
}

function sendConciergeMessage() {
    let input = $('#conciergeInput');
    let msg = input.val().trim();
    if (!msg) return;

    // Append user message
    $('#conciergeMessages').append('<div class="concierge-msg concierge-msg-user">' + $('<div>').text(msg).html() + '</div>');
    input.val('');

    let container = document.getElementById('conciergeMessages');
    container.scrollTop = container.scrollHeight;

    // AJAX to concierge API
    $.post('app/api/concierge.php', { message: msg }, function(data) {
        let replyHtml = data.reply.replace(/\n/g, '<br>').replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        let actionBtn = '';
        if (data.action && data.action.type === 'recommend_room') {
            actionBtn = '<div class="mt-2"><button class="btn btn-gold btn-sm" onclick="openReservationModal(\'' + data.action.room + '\')"><i class="fa-solid fa-calendar-check mr-1"></i> Book ' + data.action.room + ' Suite</button></div>';
        } else if (data.action && data.action.type === 'view_menu') {
            actionBtn = '<div class="mt-2"><button class="btn btn-gold btn-sm" data-toggle="modal" data-target="#roomServiceModal"><i class="fa-solid fa-utensils mr-1"></i> Open Dining Menu</button></div>';
        }
        $('#conciergeMessages').append('<div class="concierge-msg concierge-msg-bot">' + replyHtml + actionBtn + '</div>');
        container.scrollTop = container.scrollHeight;
    }, 'json');
}
</script>
</body>
</html>