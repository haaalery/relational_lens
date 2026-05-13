-- Relational Lens: Clean Database Reset Script
-- This script drops all existing tables and recreates them to ensure a clean state.
-- Use this to resolve #1813 (Tablespace exists) or #1050 (Table exists) errors.

-- Disable foreign keys to allow dropping/recreating in any order
SET FOREIGN_KEY_CHECKS = 0;

-- 1. DROP ALL EXISTING TABLES
DROP TABLE IF EXISTS `articles`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `classrooms`;
DROP TABLE IF EXISTS `consent_records`;
DROP TABLE IF EXISTS `password_resets`;
DROP TABLE IF EXISTS `regions`;
DROP TABLE IF EXISTS `reviews`;
DROP TABLE IF EXISTS `stories`;
DROP TABLE IF EXISTS `story_classroom`;
DROP TABLE IF EXISTS `users`;

-- 2. START FRESH
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- [TABLE: users]
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('filmmaker','reviewer','admin') DEFAULT 'filmmaker',
  `bio` text DEFAULT NULL,
  `avatar_url` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- [TABLE: categories]
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `slug` varchar(150) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- [TABLE: regions]
CREATE TABLE `regions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `continent` varchar(100) DEFAULT NULL,
  `lat` decimal(10,7) DEFAULT NULL,
  `lng` decimal(10,7) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- [TABLE: stories]
CREATE TABLE `stories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(300) NOT NULL,
  `slug` varchar(300) NOT NULL,
  `description` text DEFAULT NULL,
  `transcript` longtext DEFAULT NULL,
  `video_url` varchar(500) DEFAULT NULL,
  `thumbnail_url` varchar(500) DEFAULT NULL,
  `duration_seconds` int(11) DEFAULT NULL,
  `filmmaker_name` varchar(200) DEFAULT NULL,
  `filmmaker_email` varchar(255) DEFAULT NULL,
  `filmmaker_bio` text DEFAULT NULL,
  `community_credit` text DEFAULT NULL,
  `region_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `language` varchar(100) DEFAULT 'English',
  `subtitles_available` tinyint(1) DEFAULT 0,
  `status` enum('draft','pending','under_review','approved','rejected') DEFAULT 'draft',
  `ethics_consent` tinyint(1) DEFAULT 0,
  `decolonial_tags` longtext DEFAULT NULL,
  `views_count` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `approved_at` timestamp NULL DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `region_id` (`region_id`),
  KEY `category_id` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- [TABLE: articles]
CREATE TABLE `articles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `author_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `excerpt` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `related_story_id` int(11) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `author_id` (`author_id`),
  KEY `related_story_id` (`related_story_id`),
  KEY `category_id` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- [TABLE: classrooms]
CREATE TABLE `classrooms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(300) NOT NULL,
  `description` text DEFAULT NULL,
  `guide_url` varchar(500) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- [TABLE: consent_records`
CREATE TABLE `consent_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `story_id` int(11) NOT NULL,
  `filmmaker_confirmed` tinyint(1) DEFAULT 0,
  `community_informed` tinyint(1) DEFAULT 0,
  `consent_document_url` varchar(500) DEFAULT NULL,
  `confirmed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `story_id` (`story_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- [TABLE: password_resets]
CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- [TABLE: reviews]
CREATE TABLE `reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `story_id` int(11) NOT NULL,
  `reviewer_id` int(11) NOT NULL,
  `decision` enum('pending','approved','rejected','revision_requested') DEFAULT 'pending',
  `feedback` text DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `story_id` (`story_id`),
  KEY `reviewer_id` (`reviewer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- [TABLE: story_classroom]
CREATE TABLE `story_classroom` (
  `story_id` int(11) NOT NULL,
  `classroom_id` int(11) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  PRIMARY KEY (`story_id`,`classroom_id`),
  KEY `classroom_id` (`classroom_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. INSERT SAMPLE DATA
INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role`, `bio`) VALUES
(1, 'Kitt Harley Sy', 'kittharleysy@gmail.com', '$2y$10$l2/yAXQzEYIFvhMjbOXmy.pkyVz6X3QFFQPz1U.751Y8wf00Fm9d2', 'filmmaker', 'I AM KITT'),
(2, 'Iris Abdulla', 'irisjuhraaa@gmail.com', '$2y$10$fKqkL2WMgL2jndQmGnbnlOHjXqfGDUz8ZKNagWEHuTcrTLN2.mwZS', 'admin', 'Hello World'),
(3, 'Arbie Morales', 'arbie@gmail.com', '$2y$10$gupqcQOH/5R2Oj3FxVCQgeDjZkEyPV9mBzYJ3QgiuCwCA6h9IqJeK', 'filmmaker', 'I am a photographer');

INSERT INTO `categories` (`id`, `name`, `icon`, `slug`) VALUES
(1, 'Migration', 'bi-globe2', 'migration'),
(2, 'Mental Health', 'bi-heart-pulse', 'mental-health'),
(3, 'Climate Justice', 'bi-tree', 'climate-justice'),
(4, 'Economic Inequality', 'bi-bar-chart-line', 'economic-inequality'),
(5, 'Violence & Trauma', 'bi-shield-exclamation', 'violence-trauma'),
(6, 'Child Welfare', 'bi-people', 'child-welfare');

INSERT INTO `regions` (`id`, `name`, `continent`, `lat`, `lng`) VALUES
(1, 'Philippines', 'Asia', 12.8797, 121.774),
(2, 'Kenya', 'Africa', -0.0236, 37.9062),
(3, 'Brazil', 'South America', -14.235, -51.9253),
(4, 'United Kingdom', 'Europe', 55.3781, -3.436);

INSERT INTO `stories` (`id`, `title`, `slug`, `video_url`, `thumbnail_url`, `region_id`, `category_id`, `status`, `ethics_consent`) VALUES
(1, 'Voices of the River', 'voices-of-the-river', 'https://vimeo.com/22439234', 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32', 3, 3, 'approved', 1);

INSERT INTO `articles` (`id`, `author_id`, `title`, `slug`, `excerpt`, `content`, `image_url`, `category_id`, `status`) VALUES
(1, 2, 'The Ethics of Visual Witnessing', 'ethics-of-visual-witnessing', 'Summary here...', 'Full content...', 'https://images.unsplash.com/photo-1455390582262-044cdead277a', 2, 'approved');

COMMIT;
SET FOREIGN_KEY_CHECKS = 1;
