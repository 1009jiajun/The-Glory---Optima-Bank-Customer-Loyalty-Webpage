CREATE DATABASE IF NOT EXISTS `vouchercartsystem`
DEFAULT CHARACTER SET utf8mb4
COLLATE utf8mb4_general_ci;
USE `vouchercartsystem`;

-- ------------------------------
-- Table structure for `category`
-- ------------------------------
DROP TABLE IF EXISTS `category`;
CREATE TABLE `category` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `category` VALUES 
(1,'Food & Beverage'),
(2,'Shopping'),
(3,'Travel'),
(4,'Electronics'),
(5,'Health & Beauty');

-- ------------------------------
-- Table structure for `user`
-- ------------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `points` int DEFAULT '0',
  `address` text,
  `about_me` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `user` MODIFY `password` varchar(255) NULL;


INSERT INTO `user` VALUES 
(2,'alice@example.com','alice123','0123456789','hashedpass1',NULL,1,1000,'123 Street, KL','Loves shopping and food deals.','2025-09-08 08:18:55'),
(4,'muhdfikrizaman@gmail.com','fikri11','01116741728','Fikri11#',NULL,1,NULL,'DT312, Taman Bukit Tambun, Melaka','Loves travel and food deals.','2025-09-08 08:30:11');

ALTER TABLE `user` ADD COLUMN `firebase_uid` VARCHAR(128) UNIQUE AFTER `id`;
-- ------------------------------
-- Table structure for `voucher`
-- ------------------------------
DROP TABLE IF EXISTS `voucher`;
CREATE TABLE `voucher` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category_id` int DEFAULT NULL,
  `points` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `description` text,
  `terms_conditions` text,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `voucher_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `voucher` VALUES 
(1,1,100,'KFC 20% Off',NULL,'Enjoy 20% discount at KFC Malaysia outlets.','Valid for dine-in only. Cannot combine with other promos.'),
(2,2,200,'Lazada RM20 Off',NULL,'Get RM20 discount on Lazada purchases above RM100.','Valid only on Lazada app.'),
(3,3,500,'AirAsia RM100 Voucher',NULL,'Redeem RM100 off your next AirAsia flight booking.','Valid for flights within Southeast Asia.'),
(4,4,300,'Samsung RM100 Discount',NULL,'Redeem RM100 discount on Samsung devices.','Valid at Samsung official stores only.'),
(5,5,180,'Guardian RM25 Voucher',NULL,'Redeem RM25 off at Guardian stores.','Valid on all items except prescriptions.');

-- ------------------------------
-- Table structure for `cartitems`
-- ------------------------------
DROP TABLE IF EXISTS `cartitems`;
CREATE TABLE `cartitems` (
  `id` int NOT NULL AUTO_INCREMENT,
  `voucher_id` int NOT NULL,
  `user_id` int NOT NULL,
  `quantity` int DEFAULT '1',
  `added_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `voucher_id` (`voucher_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `cartitems_ibfk_1` FOREIGN KEY (`voucher_id`) REFERENCES `voucher` (`id`),
  CONSTRAINT `cartitems_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `cartitems` VALUES 
(2,1,4,1,'2025-09-08 08:35:31');

-- ------------------------------
-- Table structure for `cartitemhistory`
-- ------------------------------
DROP TABLE IF EXISTS `cartitemhistory`;
CREATE TABLE `cartitemhistory` (
  `id` int NOT NULL AUTO_INCREMENT,
  `voucher_id` int NOT NULL,
  `user_id` int NOT NULL,
  `quantity` int NOT NULL,
  `completed_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `voucher_id` (`voucher_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `cartitemhistory_ibfk_1` FOREIGN KEY (`voucher_id`) REFERENCES `voucher` (`id`),
  CONSTRAINT `cartitemhistory_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `cartitemhistory` VALUES 
(1,1,4,1,'2025-09-08 08:36:04');

CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expiry DATETIME NOT NULL,
    INDEX (email)
);

