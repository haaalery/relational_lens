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

INSERT INTO `stories` (`id`, `title`, `slug`, `description`, `video_url`, `thumbnail_url`, `region_id`, `category_id`, `status`, `ethics_consent`, `is_featured`, `created_at`) VALUES
(1, 'Voices of the River', 'voices-of-the-river', 'A documentary exploring climate justice, land rights, and displacement among river communities navigating industrial changes. We dive deep into the daily lives of those whose survival is directly tied to the changing rhythms of the water.', 'https://vimeo.com/22439234', 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?q=80&w=1000&auto=format&fit=crop', 3, 3, 'approved', 1, 1, '2026-05-10 10:00:00'),
(2, 'Healing Borders', 'healing-borders', 'Examining the mental health impacts of forced migration and the incredible resilience of transnational families fighting for dignity in a new land.', 'https://vimeo.com/22439234', 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?q=80&w=1000&auto=format&fit=crop', 1, 1, 'approved', 1, 1, '2026-05-11 11:30:00'),
(3, 'Urban Roots', 'urban-roots', 'Community-led agriculture as a powerful form of social intervention, providing food sovereignty and mental health support to urban populations facing economic inequality.', 'https://vimeo.com/22439234', 'https://images.unsplash.com/photo-1464226184884-fa280b87c399?q=80&w=1000&auto=format&fit=crop', 2, 2, 'approved', 1, 1, '2026-05-12 09:15:00'),
(4, 'The Silent Pandemic', 'the-silent-pandemic', 'An investigative look into how economic inequality disproportionately affects child welfare systems in urban centers around the globe.', 'https://vimeo.com/22439234', 'https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?q=80&w=1000&auto=format&fit=crop', 4, 4, 'approved', 1, 0, '2026-05-13 14:20:00');

INSERT INTO `articles` (`id`, `author_id`, `title`, `slug`, `excerpt`, `content`, `image_url`, `category_id`, `status`, `created_at`) VALUES
(1, 2, 'The Ethics of Visual Witnessing', 'ethics-of-visual-witnessing', 'Exploring the delicate balance between authentic representation and the risk of exploitation in documentary filmmaking.', 'The documentary camera is a powerful tool. It has the ability to illuminate hidden truths, challenge dominant narratives, and foster profound empathy across cultural divides. However, this power is inherently asymmetrical. The person behind the lens—the filmmaker, the researcher, the social worker—wields significant control over how a story is framed, edited, and ultimately consumed by an audience. The subjects of these stories, often individuals from marginalized or vulnerable communities, entrust their lived experiences to the storyteller, hoping that their truth will be handled with dignity and care. \n\nThis dynamic is the core concern of "visual witnessing." To witness is not merely to observe passively; it is an active, ethical stance that demands accountability and responsibility. The ethics of visual witnessing revolve around a central question: How can we represent the experiences of others authentically without reducing them to spectacles or exploiting their suffering?', 'https://images.unsplash.com/photo-1455390582262-044cdead277a?q=80&w=2000&auto=format&fit=crop', 2, 'approved', '2026-05-10 08:00:00'),
(2, 2, 'Decolonizing the Archive', 'decolonizing-the-archive', 'How indigenous communities are reclaiming their visual narratives and challenging traditional ethnographic approaches.', 'For centuries, the archive has been a repository of colonial power. Museums, libraries, and academic institutions in the Global North have historically collected, categorized, and displayed the cultural artifacts and narratives of Indigenous peoples without their consent or participation. Traditional ethnography often treated Indigenous communities as objects of study—"the other"—rather than as active participants in the creation of knowledge. These colonial practices not only dispossessed communities of their heritage but also entrenched damaging stereotypes and power imbalances. \n\nToday, a vital movement is underway to "decolonize the archive." This is not simply about returning stolen artifacts, though repatriation is a crucial component. Decolonizing the archive is a profound paradigm shift that challenges the underlying epistemologies and structures of these institutions. It involves reimagining the archive not as a static vault controlled by experts, but as a living, community-led space where Indigenous peoples have the authority to shape their own narratives.', 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?q=80&w=2000&auto=format&fit=crop', 1, 'approved', '2026-05-11 09:00:00'),
(3, 2, 'Narrative Therapy in Practice', 'narrative-therapy-in-practice', 'Using storytelling as a direct clinical intervention for trauma survivors in post-conflict regions.', 'In the aftermath of conflict, the wounds are often profound and invisible. Trauma shatters a person\'s sense of safety, trust, and identity, leaving them entangled in stories of victimhood and despair. Traditional clinical interventions, while valuable, can sometimes fall short in addressing the complex, culturally rooted experiences of survivors in post-conflict regions. \n\nNarrative therapy offers a powerful alternative—a deeply respectful and empowering approach that centers the survivor as the expert of their own life. At its core, narrative therapy posits that our identities are shaped by the stories we tell about ourselves and the stories others tell about us. When a person has experienced severe trauma, the "problem-saturated narrative" often becomes the dominant story, eclipsing their strengths, resilience, and values. The goal of narrative therapy is not to "fix" the person, but to help them separate from the problem and re-author their story in a way that is empowering and meaningful.', 'https://images.unsplash.com/photo-1473186578172-c141e6798cf4?q=80&w=2000&auto=format&fit=crop', 5, 'approved', '2026-05-12 10:00:00');

COMMIT;
SET FOREIGN_KEY_CHECKS = 1;
