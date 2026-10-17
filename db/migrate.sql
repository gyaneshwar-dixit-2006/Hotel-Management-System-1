-- Database migration and additions for Hotel Management System

CREATE TABLE IF NOT EXISTS `pricing` (
    `pricing_id` int(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
    `booking_id` int(11) UNSIGNED NOT NULL,
    `nights` int(11) NOT NULL,
    `total_price` double NOT NULL,
    `booked_date` DATE NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample pricing records for existing bookings if empty
INSERT IGNORE INTO `pricing` (`pricing_id`, `booking_id`, `nights`, `total_price`, `booked_date`) VALUES
(1, 12, 2, 33314, '2024-04-19'),
(2, 13, 1, 23135, '2024-04-23'),
(3, 14, 3, 69405, '2024-04-24'),
(4, 15, 10, 120300, '2024-02-26'),
(5, 16, 8, 133256, '2024-02-26'),
(6, 17, 12, 199884, '2024-02-26'),
(7, 18, 6, 72180, '2024-02-26'),
(8, 19, 13, 156390, '2024-02-27'),
(9, 20, 13, 216541, '2024-02-27');

-- Additional tables for unique features

-- 1. Rooms Catalog & Inventory
CREATE TABLE IF NOT EXISTS `rooms` (
    `room_id` int(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
    `room_number` varchar(20) NOT NULL UNIQUE,
    `type` varchar(50) NOT NULL,
    `title` varchar(100) NOT NULL,
    `price_per_night` double NOT NULL,
    `capacity_adults` int(2) NOT NULL DEFAULT 2,
    `capacity_children` int(2) NOT NULL DEFAULT 1,
    `bed_type` varchar(50) NOT NULL DEFAULT 'King Bed',
    `size_sqft` int(6) NOT NULL DEFAULT 350,
    `image` varchar(255) NOT NULL DEFAULT 'image/deluxe.jpg',
    `description` text,
    `amenities` text, -- JSON or comma separated
    `status` varchar(30) NOT NULL DEFAULT 'Available' -- Available, Occupied, Maintenance
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `rooms` (`room_id`, `room_number`, `type`, `title`, `price_per_night`, `capacity_adults`, `capacity_children`, `bed_type`, `size_sqft`, `image`, `description`, `amenities`, `status`) VALUES
(1, '101', 'Single', 'Classic Cozy Single', 12030, 1, 0, 'Single Twin Bed', 220, 'image/single.jpg', 'A modern, quiet retreat designed for solo travelers and business professionals. Features high-speed fiber Wi-Fi, ergonomic work desk, plush bedding, and an Italian rain shower.', 'High-Speed Wi-Fi, Work Desk, Italian Rain Shower, Smart TV, Minibar, Climate Control', 'Available'),
(2, '102', 'Single', 'Executive Single Suite', 13500, 1, 1, 'Queen Bed', 260, 'image/single.jpg', 'Spacious executive room with city view, soundproof windows, luxury espresso maker, and dedicated concierge access.', 'City View, Espresso Machine, High-Speed Wi-Fi, Smart TV, 24/7 Room Service, In-room Safe', 'Available'),
(3, '201', 'Double', 'Grand Standard Double', 16657, 2, 1, 'Two Double Beds', 380, 'image/double.jpg', 'Ideal for couples or friends traveling together. Beautiful interior decor with luxury Egyptian cotton sheets, modern dual vanity bathroom, and ambient lighting.', 'Dual Twin Beds, Egyptian Cotton, Free Breakfast, Wi-Fi, Smart 55-inch TV, Mini Bar, Balcony', 'Available'),
(4, '202', 'Double', 'Deluxe Double Terrace', 18500, 2, 2, 'King Bed', 420, 'image/double.jpg', 'Spacious room featuring private terrace garden view, oversized bathtub, ambient lounge seating, and welcome complimentary wine.', 'Garden Terrace, King Bed, Deep Soak Tub, Complimentary Wine, Wi-Fi, 4K TV, Sound System', 'Available'),
(5, '301', 'Deluxe', 'Presidential Deluxe Suite', 23135, 3, 2, 'California King', 650, 'image/deluxe.jpg', 'Our crown jewel. Elevated penthouse ceilings with floor-to-ceiling panoramic glass windows, private jacuzzi, separate living lounge, luxury bathrobes, and butler service.', 'Panoramic Skyline View, California King, Private Jacuzzi, Butler Service, Living Lounge, Free Airport Transfer', 'Available'),
(6, '302', 'Deluxe', 'Royal Penthouse Suite', 29500, 4, 2, 'Master King & Queen', 850, 'image/deluxe.jpg', 'The ultimate luxury experience. Two master bedrooms, dining area, private cocktail bar, infinity bath, and exclusive VIP lounge privilege.', 'VIP Lounge Access, 2 Master Bedrooms, Private Cocktail Bar, Infinity Bath, Chauffeur Service, Chef Breakfast', 'Available');

-- 2. Room Service & F&B Menu
CREATE TABLE IF NOT EXISTS `room_service_menu` (
    `item_id` int(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
    `category` varchar(50) NOT NULL,
    `name` varchar(100) NOT NULL,
    `description` varchar(255) DEFAULT NULL,
    `price` double NOT NULL,
    `badge` varchar(50) DEFAULT NULL,
    `is_available` int(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `room_service_menu` (`item_id`, `category`, `name`, `description`, `price`, `badge`, `is_available`) VALUES
(1, 'Breakfast', 'Continental Royale Breakfast', 'Freshly baked croissants, seasonal fruits, organic eggs to order, freshly squeezed juice & artisan coffee', 650, 'Chef Special', 1),
(2, 'Breakfast', 'Full English Grand Breakfast', 'Sausages, smoked bacon, baked beans, grilled tomatoes, hash browns, and eggs', 750, 'Popular', 1),
(3, 'Main Course', 'Grilled Norwegian Salmon', 'Pan-seared Atlantic salmon with lemon dill butter sauce, asparagus, and roasted baby potatoes', 1450, 'Bestseller', 1),
(4, 'Main Course', 'Wood-Fired Truffle Pizza', 'Crispy artisan crust, black truffle paste, buffalo mozzarella, and fresh arugula', 980, 'Vegetarian', 1),
(5, 'Main Course', 'Butter Chicken with Garlic Naan', 'Tender tandoori chicken simmered in rich velvety tomato cashew gravy with hot butter naan', 850, 'Signature', 1),
(6, 'Beverages', 'Exotic Berry Mojito', 'Fresh mint, muddled wild berries, sparkling water, and crushed lime', 380, 'Refreshing', 1),
(7, 'Beverages', 'Artisan Single Origin Cappuccino', 'Specialty Ethiopian roast with creamy textured oat or dairy milk', 280, 'Hot', 1),
(8, 'Desserts', 'Molten Belgian Chocolate Lava Cake', 'Rich warm chocolate fondant with Madagascar vanilla bean gelato', 480, 'Must Try', 1);

-- 3. Room Service Orders
CREATE TABLE IF NOT EXISTS `room_service_orders` (
    `order_id` int(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
    `booking_id` int(11) UNSIGNED NOT NULL,
    `customer_id` int(11) UNSIGNED NOT NULL,
    `room_number` varchar(50) DEFAULT 'Room',
    `items_json` text NOT NULL,
    `total_amount` double NOT NULL,
    `special_instructions` text,
    `status` varchar(50) NOT NULL DEFAULT 'Placed', -- Placed, Preparing, Delivered, Cancelled
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Reviews & Ratings
CREATE TABLE IF NOT EXISTS `reviews` (
    `review_id` int(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
    `customer_id` int(11) UNSIGNED NOT NULL,
    `booking_id` int(11) UNSIGNED DEFAULT NULL,
    `customer_name` varchar(100) NOT NULL,
    `rating` int(1) NOT NULL DEFAULT 5,
    `comment` text NOT NULL,
    `status` varchar(20) NOT NULL DEFAULT 'Approved',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `reviews` (`review_id`, `customer_id`, `customer_name`, `rating`, `comment`, `status`) VALUES
(1, 10, 'Martha Smith', 5, 'Exceptional experience from start to finish! The Deluxe Suite view is breathtaking, and the contactless QR check-in made arrival so effortless.', 'Approved'),
(2, 13, 'Leo Raymart', 5, 'The room service grilled salmon and morning breakfast were world class. Staff was extraordinarily attentive and courteous. 10/10 recommendation!', 'Approved'),
(3, 14, 'Aarav Sharma', 5, 'Super clean rooms, top-of-the-line amenities, ultra-fast Wi-Fi. Truly a 5-star standard hotel management experience.', 'Approved');
