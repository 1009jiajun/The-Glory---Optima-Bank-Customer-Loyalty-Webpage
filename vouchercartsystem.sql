-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 18, 2025 at 09:06 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `vouchercartsystem`
--

-- --------------------------------------------------------

--
-- Table structure for table `cartitemhistory`
--

CREATE TABLE `cartitemhistory` (
  `id` int(11) NOT NULL,
  `voucher_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `completed_date` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cartitemhistory`
--

INSERT INTO `cartitemhistory` (`id`, `voucher_id`, `user_id`, `quantity`, `completed_date`) VALUES
(1, 1, 4, 1, '2025-09-08 00:36:04');

-- --------------------------------------------------------

--
-- Table structure for table `cartitems`
--

CREATE TABLE `cartitems` (
  `id` int(11) NOT NULL,
  `voucher_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `added_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cartitems`
--

INSERT INTO `cartitems` (`id`, `voucher_id`, `user_id`, `quantity`, `added_at`) VALUES
(2, 1, 4, 1, '2025-09-08 00:35:31'),
(13, 6, 5, 1, '2025-09-18 02:49:44');

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`id`, `name`) VALUES
(1, 'Food & Beverage'),
(2, 'Shopping'),
(3, 'Travel'),
(4, 'Electronics'),
(5, 'Health & Beauty');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expiry` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `firebase_uid` varchar(128) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `points` int(11) DEFAULT 0,
  `address` text DEFAULT NULL,
  `about_me` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `firebase_uid`, `email`, `username`, `phone_number`, `password`, `profile_image`, `is_active`, `points`, `address`, `about_me`, `created_at`) VALUES
(2, NULL, 'alice@example.com', 'alice123', '0123456789', 'hashedpass1', NULL, 1, 1000, '123 Street, KL', 'Loves shopping and food deals.', '2025-09-08 00:18:55'),
(4, NULL, 'muhdfikrizaman@gmail.com', 'fikri11', '01116741728', 'Fikri11#', NULL, 1, NULL, 'DT312, Taman Bukit Tambun, Melaka', 'Loves travel and food deals.', '2025-09-08 00:30:11'),
(5, 'xscyEwgsl2UfooM5LccLhC8ElhH3', 'boojiajun98@gmail.com', 'BOO JIA JUN', NULL, NULL, 'assets/img/profile/6e66086560ee7fc74b41e3482515fcfd.jpg', 1, 0, NULL, NULL, '2025-09-16 07:25:13');

-- --------------------------------------------------------

--
-- Table structure for table `voucher`
--

CREATE TABLE `voucher` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `points` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `terms_conditions` text DEFAULT NULL,
  `created_datetime` timestamp NOT NULL DEFAULT current_timestamp(),
  `edited_datetime` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `voucher`
--

INSERT INTO `voucher` (`id`, `category_id`, `points`, `title`, `image`, `description`, `terms_conditions`, `created_datetime`, `edited_datetime`) VALUES
(1, 1, 100, 'KFC 20% Off', 'assets/img/voucher/KFC.png', 'Enjoy 20% discount at KFC Malaysia outlets.', 'Valid for dine-in only. Cannot combine with other promos.', '2025-09-16 16:00:00', '2025-09-17 07:28:21'),
(2, 2, 200, 'Lazada RM20 Off', 'assets/img/voucher/Lazada.png', 'Get RM20 discount on Lazada purchases above RM100.', 'Valid only on Lazada app.', '2025-09-16 16:00:00', '2025-09-17 07:28:21'),
(3, 3, 500, 'AirAsia RM100 Voucher', 'assets/img/voucher/airasia.png', 'Redeem RM100 off your next AirAsia flight booking.', 'Valid for flights within Southeast Asia.', '2025-09-16 16:00:00', '2025-09-17 07:31:04'),
(4, 4, 300, 'Samsung RM100 Discount', 'assets/img/voucher/SAMSUNG.png', 'Redeem RM100 discount on Samsung devices with min. spend of RM 150.', 'Valid at Samsung official stores only. Must spend at least RM 150 for Single purchase of Samsung devices to use the voucher.', '2025-09-16 16:00:00', '2025-09-17 07:34:15'),
(5, 5, 180, 'Guardian RM25 Voucher', 'assets/img/voucher/guardian.png', 'Redeem RM25 off at Guardian stores.', 'Valid on all items except prescriptions.', '2025-09-16 16:00:00', '2025-09-17 07:34:57'),
(6, 1, 120, 'Pizza Hut Free Drink', 'assets/img/voucher/pizzahut.png', 'Get a free drink with any large pizza.', 'Valid dine-in only.', '2025-09-17 07:41:09', '2025-09-17 07:41:09'),
(7, 2, 180, 'Shopee RM30 Off', 'assets/img/voucher/shopee.png', 'Get RM30 discount on orders above RM150.', 'Valid for Shopee Mall items only.', '2025-09-17 07:41:09', '2025-09-17 07:41:09'),
(8, 3, 450, 'Train RM50 Voucher', 'assets/img/voucher/KTM.png', 'Redeem RM50 discount for KTM train tickets.', 'Valid for ETS routes only.', '2025-09-17 07:41:09', '2025-09-17 07:41:09'),
(9, 4, 400, 'Apple RM150 Off', 'assets/img/voucher/Apple.png', 'Redeem RM150 discount on Apple products.', 'Valid in official Apple stores.', '2025-09-17 07:41:09', '2025-09-17 07:41:09'),
(10, 5, 200, 'Sephora RM30 Voucher', 'assets/img/voucher/sephora.png', 'Get RM30 off on Sephora products.', 'Valid in-store only. Expiry 30 days.', '2025-09-17 07:41:09', '2025-09-17 07:41:09');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cartitemhistory`
--
ALTER TABLE `cartitemhistory`
  ADD PRIMARY KEY (`id`),
  ADD KEY `voucher_id` (`voucher_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `cartitems`
--
ALTER TABLE `cartitems`
  ADD PRIMARY KEY (`id`),
  ADD KEY `voucher_id` (`voucher_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email` (`email`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `firebase_uid` (`firebase_uid`);

--
-- Indexes for table `voucher`
--
ALTER TABLE `voucher`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cartitemhistory`
--
ALTER TABLE `cartitemhistory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cartitems`
--
ALTER TABLE `cartitems`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `voucher`
--
ALTER TABLE `voucher`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cartitemhistory`
--
ALTER TABLE `cartitemhistory`
  ADD CONSTRAINT `cartitemhistory_ibfk_1` FOREIGN KEY (`voucher_id`) REFERENCES `voucher` (`id`),
  ADD CONSTRAINT `cartitemhistory_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `cartitems`
--
ALTER TABLE `cartitems`
  ADD CONSTRAINT `cartitems_ibfk_1` FOREIGN KEY (`voucher_id`) REFERENCES `voucher` (`id`),
  ADD CONSTRAINT `cartitems_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `voucher`
--
ALTER TABLE `voucher`
  ADD CONSTRAINT `voucher_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
