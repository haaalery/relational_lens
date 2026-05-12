-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 05, 2026 at 04:36 PM
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
-- Database: `relational_lens`
--

-- --------------------------------------------------------

--
-- Table structure for table `articles`
--

CREATE TABLE `articles` (
  `id` int(11) NOT NULL,
  `author_id` int(11) NOT NULL COMMENT 'Links to users.id',
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `excerpt` text DEFAULT NULL COMMENT 'Brief summary for the Gallery grid',
  `content` longtext DEFAULT NULL COMMENT 'The full article/essay body',
  `image_url` varchar(500) DEFAULT NULL COMMENT 'Main photo for the article',
  `category_id` int(11) DEFAULT NULL COMMENT 'Links to categories.id (Grand Challenges)',
  `related_story_id` int(11) DEFAULT NULL COMMENT 'Optional link to a documentary (stories.id)',
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `articles`
--

INSERT INTO `articles` (`id`, `author_id`, `title`, `slug`, `excerpt`, `content`, `image_url`, `category_id`, `related_story_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, 'The Ethics of Visual Witnessing', 'ethics-of-visual-witnessing', 'Exploring the balance between representation and participation.', 'Full content...', 'https://images.unsplash.com/photo-1455390582262-044cdead277a?q=80&w=2000&auto=format&fit=crop', 2, NULL, 'approved', '2026-05-05 14:24:11', '2026-05-05 14:24:11'),
(2, 2, 'Decolonizing the Archive', 'decolonizing-the-archive', 'How indigenous communities are reclaiming their visual narratives.', 'Full content...', 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?q=80&w=2000&auto=format&fit=crop', 1, NULL, 'approved', '2026-05-05 14:24:11', '2026-05-05 14:24:11');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `slug` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Thematic categories for the Grand Challenges grid';

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `icon`, `slug`) VALUES
(1, 'Migration', 'bi-globe2', 'migration'),
(2, 'Mental Health', 'bi-heart-pulse', 'mental-health'),
(3, 'Climate Justice', 'bi-tree', 'climate-justice'),
(4, 'Economic Inequality', 'bi-bar-chart-line', 'economic-inequality'),
(5, 'Violence & Trauma', 'bi-shield-exclamation', 'violence-trauma'),
(6, 'Child Welfare', 'bi-people', 'child-welfare');

-- --------------------------------------------------------

--
-- Table structure for table `classrooms`
--

CREATE TABLE `classrooms` (
  `id` int(11) NOT NULL,
  `title` varchar(300) NOT NULL,
  `description` text DEFAULT NULL,
  `guide_url` varchar(500) DEFAULT NULL COMMENT 'Downloadable discussion guide PDF',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Curated educator collections';

-- --------------------------------------------------------

--
-- Table structure for table `consent_records`
--

CREATE TABLE `consent_records` (
  `id` int(11) NOT NULL,
  `story_id` int(11) NOT NULL,
  `filmmaker_confirmed` tinyint(1) DEFAULT 0,
  `community_informed` tinyint(1) DEFAULT 0,
  `consent_document_url` varchar(500) DEFAULT NULL COMMENT 'Optional upload of signed consent form',
  `confirmed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Ethical documentation and consent per story';

--
-- Dumping data for table `consent_records`
--

INSERT INTO `consent_records` (`id`, `story_id`, `filmmaker_confirmed`, `community_informed`, `consent_document_url`, `confirmed_at`) VALUES
(1, 5, 1, 1, NULL, '2026-05-05 07:41:55');

-- --------------------------------------------------------

--
-- Table structure for table `regions`
--

CREATE TABLE `regions` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `continent` varchar(100) DEFAULT NULL,
  `lat` decimal(10,7) DEFAULT NULL,
  `lng` decimal(10,7) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Geographic metadata for story mapping';

--
-- Dumping data for table `regions`
--

INSERT INTO `regions` (`id`, `name`, `continent`, `lat`, `lng`) VALUES
(1, 'Philippines', 'Asia', 12.8797000, 121.7740000),
(2, 'Kenya', 'Africa', -0.0236000, 37.9062000),
(3, 'Brazil', 'South America', -14.2350000, -51.9253000),
(4, 'United Kingdom', 'Europe', 55.3781000, -3.4360000),
(5, 'United States', 'North America', 37.0902000, -95.7129000),
(6, 'India', 'Asia', 20.5937000, 78.9629000);

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `story_id` int(11) NOT NULL,
  `reviewer_id` int(11) NOT NULL,
  `decision` enum('pending','approved','rejected','revision_requested') DEFAULT 'pending',
  `feedback` text DEFAULT NULL COMMENT 'Reviewer notes visible to filmmaker',
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Peer-review log per story';

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `story_id`, `reviewer_id`, `decision`, `feedback`, `reviewed_at`, `created_at`) VALUES
(1, 5, 2, 'approved', NULL, '2026-05-05 07:43:16', '2026-05-05 07:42:46');

-- --------------------------------------------------------

--
-- Table structure for table `stories`
--

CREATE TABLE `stories` (
  `id` int(11) NOT NULL,
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
  `community_credit` text DEFAULT NULL COMMENT 'Who the story is WITH, not about',
  `region_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `language` varchar(100) DEFAULT 'English',
  `subtitles_available` tinyint(1) DEFAULT 0,
  `status` enum('draft','pending','under_review','approved','rejected') DEFAULT 'draft',
  `ethics_consent` tinyint(1) DEFAULT 0 COMMENT 'Must be 1 before submission',
  `decolonial_tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'e.g. ["indigenous","land rights","anti-colonial"]',
  `views_count` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `approved_at` timestamp NULL DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stories`
--

INSERT INTO `stories` (`id`, `title`, `slug`, `description`, `transcript`, `video_url`, `thumbnail_url`, `duration_seconds`, `filmmaker_name`, `filmmaker_email`, `filmmaker_bio`, `community_credit`, `region_id`, `category_id`, `language`, `subtitles_available`, `status`, `ethics_consent`, `decolonial_tags`, `views_count`, `created_at`, `updated_at`, `approved_at`, `approved_by`, `is_featured`) VALUES
(1, 'Voices of the River', 'voices-of-the-river', 'A documentary exploring climate justice, land rights, and displacement among river communities navigating industrial changes.', NULL, 'https://vimeo.com/22439234', 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?q=80&w=1000&auto=format&fit=crop', NULL, 'Elena Rodriguez', 'elena@example.com', NULL, 'River Collective', 3, 3, 'English', 0, 'approved', 1, NULL, 9, '2026-05-03 19:10:18', '2026-05-04 11:05:55', '2026-05-03 19:10:18', NULL, 1),
(2, 'Healing Borders', 'healing-borders', 'Examining the mental health impacts of forced migration and the incredible resilience of transnational families fighting for dignity.', NULL, 'https://vimeo.com/22439234', 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?q=80&w=1000&auto=format&fit=crop', NULL, 'Dr. Samuel Chen', 'samuel@example.com', NULL, 'Borderlands Youth', 1, 1, 'English', 0, 'approved', 1, NULL, 5, '2026-05-02 19:10:18', '2026-05-05 06:44:44', '2026-05-03 19:10:18', NULL, 1),
(3, 'Urban Roots', 'urban-roots', 'Community-led agriculture as a powerful form of social intervention, providing food sovereignty and mental health support.', NULL, 'https://vimeo.com/22439234', 'https://images.unsplash.com/photo-1464226184884-fa280b87c399?q=80&w=1000&auto=format&fit=crop', NULL, 'Aisha Bello', 'aisha@example.com', NULL, 'City Farm Co-op', 2, 2, 'English', 0, 'approved', 1, NULL, 1, '2026-05-01 19:10:18', '2026-05-04 10:06:29', '2026-05-03 19:10:18', NULL, 1),
(4, 'The Silent Pandemic', 'the-silent-pandemic', 'An investigative look into how economic inequality disproportionately affects child welfare systems in urban centers.', NULL, 'https://vimeo.com/22439234', 'https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?q=80&w=1000&auto=format&fit=crop', NULL, 'Marcus Doe', 'marcus@example.com', NULL, 'Global Youth Org', 1, 2, 'English', 0, 'approved', 1, NULL, 3, '2026-04-30 19:10:18', '2026-05-05 07:28:20', '2026-05-03 19:10:18', NULL, 0),
(5, 'The Resilience of the Magdalena ', 'the-resilience-of-the-magdalena-', 'A short documentary exploring how fishing communities along the Magdalena River use collective storytelling to heal from the trauma of displacement.', '', ' https://vimeo.com/22439234', 'https://images.unsplash.com/photo-1504196606672-aef5c9cefc92?q=80&w=1000', NULL, 'Elena Rodriguez', 'elena.docs@example.com', 'Elena is a social worker and documentary filmmaker based in Bogotá, focusing on urban displacement and community resilience.', 'Created with the [Asociación de Pescadores de Magdalena] collective.', 3, 4, 'English', 0, 'approved', 1, '[\"Indigenous\",\"Resistance\",\"Healing\"]', 2, '2026-05-05 07:41:55', '2026-05-05 07:43:55', '2026-05-05 07:43:16', 2, 0);

-- --------------------------------------------------------

--
-- Table structure for table `story_classroom`
--

CREATE TABLE `story_classroom` (
  `story_id` int(11) NOT NULL,
  `classroom_id` int(11) NOT NULL,
  `sort_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Many-to-many: stories in classroom collections';

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('filmmaker','reviewer','admin') DEFAULT 'filmmaker',
  `bio` text DEFAULT NULL,
  `avatar_url` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Platform users — filmmakers, reviewers, admins';

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role`, `bio`, `avatar_url`, `created_at`) VALUES
(1, 'Kitt Harley Sy', 'kittharleysy@gmail.com', '$2y$10$l2/yAXQzEYIFvhMjbOXmy.pkyVz6X3QFFQPz1U.751Y8wf00Fm9d2', 'filmmaker', 'I AM KITT', NULL, '2026-05-03 06:30:03'),
(2, 'Iris Abdulla', 'irisjuhraaa@gmail.com', '$2y$10$fKqkL2WMgL2jndQmGnbnlOHjXqfGDUz8ZKNagWEHuTcrTLN2.mwZS', 'admin', 'Hello World b ', NULL, '2026-05-03 17:16:43'),
(3, 'Arbie Morales', 'arbie@gmail.com', '$2y$10$gupqcQOH/5R2Oj3FxVCQgeDjZkEyPV9mBzYJ3QgiuCwCA6h9IqJeK', 'filmmaker', 'I am a professional photographer', NULL, '2026-05-05 06:25:06'),
(4, 'Elena Rodriguez', 'elena.docs@example.com', '$2y$10$l04MQVdCox0kq5aILeHxluP6UqyQg2YPL/A.GfekZt/BsK/dg312q', 'filmmaker', 'Elena is a social worker and documentary filmmaker based in Bogotá, focusing on urban displacement and community resilience.', NULL, '2026-05-05 07:31:26');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `articles`
--
ALTER TABLE `articles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `author_id` (`author_id`),
  ADD KEY `related_story_id` (`related_story_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `classrooms`
--
ALTER TABLE `classrooms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `consent_records`
--
ALTER TABLE `consent_records`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `story_id` (`story_id`);

--
-- Indexes for table `regions`
--
ALTER TABLE `regions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `story_id` (`story_id`),
  ADD KEY `reviewer_id` (`reviewer_id`);

--
-- Indexes for table `stories`
--
ALTER TABLE `stories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `region_id` (`region_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `story_classroom`
--
ALTER TABLE `story_classroom`
  ADD PRIMARY KEY (`story_id`,`classroom_id`),
  ADD KEY `classroom_id` (`classroom_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `articles`
--
ALTER TABLE `articles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `classrooms`
--
ALTER TABLE `classrooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `consent_records`
--
ALTER TABLE `consent_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `regions`
--
ALTER TABLE `regions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `stories`
--
ALTER TABLE `stories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `classrooms`
--
ALTER TABLE `classrooms`
  ADD CONSTRAINT `classrooms_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `consent_records`
--
ALTER TABLE `consent_records`
  ADD CONSTRAINT `consent_records_ibfk_1` FOREIGN KEY (`story_id`) REFERENCES `stories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`story_id`) REFERENCES `stories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `story_classroom`
--
ALTER TABLE `story_classroom`
  ADD CONSTRAINT `story_classroom_ibfk_1` FOREIGN KEY (`story_id`) REFERENCES `stories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `story_classroom_ibfk_2` FOREIGN KEY (`classroom_id`) REFERENCES `classrooms` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
