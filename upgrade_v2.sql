-- Upgrade script v2: roles + episodes
USE `phimweb`;

ALTER TABLE `users`
MODIFY COLUMN `role` ENUM('user', 'editor', 'moderator', 'admin') NOT NULL DEFAULT 'user';

CREATE TABLE IF NOT EXISTS `movie_episodes` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `movie_id` INT UNSIGNED NOT NULL,
  `episode_number` INT UNSIGNED NOT NULL,
  `title` VARCHAR(255) DEFAULT NULL,
  `video_type` ENUM('file', 'url', 'youtube') NOT NULL DEFAULT 'url',
  `video_path` TEXT NOT NULL,
  `duration` VARCHAR(20) DEFAULT NULL,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_episode_number` (`movie_id`, `episode_number`),
  FOREIGN KEY (`movie_id`) REFERENCES `movies`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
