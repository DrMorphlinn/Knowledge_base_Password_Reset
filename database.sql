-- ============================================================
-- Knowledge Base Portal - Database Schema
-- ============================================================

CREATE DATABASE IF NOT EXISTS `knowledge_base`;
USE `knowledge_base`;

-- ------------------------------------------------------------
-- Table: users
-- Roles: admin, teacher, student
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'teacher', 'student') NOT NULL DEFAULT 'student',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: categories
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: lessons
-- type: video / pdf
-- status: pending / approved / rejected
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lessons` (
    `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(200) NOT NULL,
    `description` TEXT,
    `type` ENUM('video', 'pdf') NOT NULL,
    `file_path` VARCHAR(255) DEFAULT NULL,
    `youtube_url` VARCHAR(255) DEFAULT NULL,
    `category_id` INT(11) UNSIGNED DEFAULT NULL,
    `user_id` INT(11) UNSIGNED NOT NULL,
    `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: comments
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `comments` (
    `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `lesson_id` INT(11) UNSIGNED NOT NULL,
    `user_id` INT(11) UNSIGNED NOT NULL,
    `comment_text` TEXT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`lesson_id`) REFERENCES `lessons`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Seed: Default Admin User (password = admin123)
-- ------------------------------------------------------------
INSERT INTO `users` (`name`, `email`, `password`, `role`) VALUES
('Admin', 'admin@knowledgebase.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- ------------------------------------------------------------
-- Seed: Sample Categories
-- ------------------------------------------------------------
INSERT INTO `categories` (`name`, `slug`) VALUES
('Web Development', 'web-development'),
('Data Science', 'data-science'),
('Mobile Development', 'mobile-development'),
('DevOps', 'devops');

