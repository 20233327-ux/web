-- ============================================================
-- PhimWeb - Hệ thống xem phim trực tuyến
-- Database Schema
-- ============================================================

-- InfinityFree/phpMyAdmin: Chon DB o panel ben trai roi import file nay.
-- Khong can (va khong nen) CREATE DATABASE/USE trong goi shared hosting.

-- Bảng người dùng
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100) DEFAULT NULL,
  `role` ENUM('user', 'editor', 'moderator', 'admin') NOT NULL DEFAULT 'user',
  `avatar` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('active', 'banned') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bảng thể loại phim
CREATE TABLE IF NOT EXISTS `genres` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bảng quốc gia
CREATE TABLE IF NOT EXISTS `countries` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bảng phim
CREATE TABLE IF NOT EXISTS `movies` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL UNIQUE,
  `original_title` VARCHAR(255) DEFAULT NULL,
  `description` TEXT,
  `genre_id` INT UNSIGNED DEFAULT NULL,
  `country_id` INT UNSIGNED DEFAULT NULL,
  `year` YEAR DEFAULT NULL,
  `director` VARCHAR(200) DEFAULT NULL,
  `cast_members` TEXT DEFAULT NULL,
  `thumbnail` VARCHAR(500) DEFAULT NULL,
  `trailer_url` VARCHAR(500) DEFAULT NULL,
  `video_type` ENUM('file', 'url', 'youtube', 'embed') NOT NULL DEFAULT 'url',
  `video_path` TEXT DEFAULT NULL,
  `duration` VARCHAR(20) DEFAULT NULL,
  `quality` VARCHAR(20) DEFAULT 'HD',
  `language` VARCHAR(50) DEFAULT 'Vietsub',
  `views` INT UNSIGNED DEFAULT 0,
  `rating` DECIMAL(3,1) DEFAULT 0.0,
  `rating_count` INT UNSIGNED DEFAULT 0,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `featured` TINYINT(1) UNSIGNED DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_movies_genre_id` (`genre_id`),
  KEY `idx_movies_country_id` (`country_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bảng tập phim (cho series)
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
  KEY `idx_episode_movie_id` (`movie_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bảng đánh giá
CREATE TABLE IF NOT EXISTS `ratings` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `movie_id` INT UNSIGNED NOT NULL,
  `rating` TINYINT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_rating` (`user_id`, `movie_id`),
  KEY `idx_ratings_user_id` (`user_id`),
  KEY `idx_ratings_movie_id` (`movie_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Lịch sử xem
CREATE TABLE IF NOT EXISTS `watch_history` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `movie_id` INT UNSIGNED NOT NULL,
  `watched_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_watch` (`user_id`, `movie_id`),
  KEY `idx_watch_user_id` (`user_id`),
  KEY `idx_watch_movie_id` (`movie_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bảng bình luận
CREATE TABLE IF NOT EXISTS `comments` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `movie_id` INT UNSIGNED NOT NULL,
  `content` TEXT NOT NULL,
  `status` ENUM('active', 'hidden') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_comments_user_id` (`user_id`),
  KEY `idx_comments_movie_id` (`movie_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Dữ liệu mẫu
-- ============================================================

INSERT INTO `genres` (`name`, `slug`) VALUES
('Hành động', 'hanh-dong'),
('Tình cảm', 'tinh-cam'),
('Hài hước', 'hai-huoc'),
('Kinh dị', 'kinh-di'),
('Khoa học viễn tưởng', 'khoa-hoc-vien-tuong'),
('Hoạt hình', 'hoat-hinh'),
('Phiêu lưu', 'phieu-luu'),
('Tâm lý', 'tam-ly'),
('Tội phạm', 'toi-pham'),
('Lịch sử', 'lich-su'),
('Chiến tranh', 'chien-tranh'),
('Thể thao', 'the-thao'),
('Âm nhạc', 'am-nhac'),
('Gia đình', 'gia-dinh');

INSERT INTO `countries` (`name`, `slug`) VALUES
('Việt Nam', 'viet-nam'),
('Mỹ', 'my'),
('Hàn Quốc', 'han-quoc'),
('Nhật Bản', 'nhat-ban'),
('Trung Quốc', 'trung-quoc'),
('Thái Lan', 'thai-lan'),
('Pháp', 'phap'),
('Anh', 'anh'),
('Ấn Độ', 'an-do'),
('Hồng Kông', 'hong-kong');

-- Tài khoản mặc định (xem setup.php để tạo tài khoản với mật khẩu tùy chỉnh)
-- Admin: admin / Admin@123
-- User: demo / User@123
INSERT INTO `users` (`username`, `email`, `password`, `full_name`, `role`) VALUES
('admin', 'admin@phimweb.vn', '$2y$10$8K1p/a0dL1LXMIgoEDFrwOfMQkLmCkFbfJRmMuvh2xPvj4YcAIoHS', 'Quản trị viên', 'admin'),
('demo', 'demo@phimweb.vn', '$2y$10$TKh8H1.PFbuS35e5lg0oMuSd1oGAEdGzMkB8b.VXdABvKbcmGEppu', 'Người dùng Demo', 'user');
-- Password: Admin@123 và User@123
