-- ============================================================
-- PhimWeb - Hệ thống xem phim trực tuyến
-- Database Schema
-- ============================================================

-- InfinityFree/phpMyAdmin: Chon DB o panel ben trai roi import file nay.
-- Khong can (va khong nen) CREATE DATABASE/USE trong goi shared hosting.
-- NOTE: Nếu import lần thứ 2, hãy xóa các phim cũ bằng cách chạy trước:
-- DELETE FROM `movie_episodes`;
-- DELETE FROM `ratings`;
-- DELETE FROM `movies`;
-- Sau đó import file này

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

INSERT IGNORE INTO `genres` (`name`, `slug`) VALUES
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

INSERT IGNORE INTO `countries` (`name`, `slug`) VALUES
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
INSERT IGNORE INTO `users` (`username`, `email`, `password`, `full_name`, `role`) VALUES
('admin', 'admin@phimweb.vn', '$2y$10$8K1p/a0dL1LXMIgoEDFrwOfMQkLmCkFbfJRmMuvh2xPvj4YcAIoHS', 'Quản trị viên', 'admin'),
('demo', 'demo@phimweb.vn', '$2y$10$TKh8H1.PFbuS35e5lg0oMuSd1oGAEdGzMkB8b.VXdABvKbcmGEppu', 'Người dùng Demo', 'user');
-- Password: Admin@123 và User@123

-- Phim mẫu
INSERT INTO `movies` (`title`, `slug`, `original_title`, `description`, `genre_id`, `country_id`, `year`, `director`, `cast_members`, `trailer_url`, `video_type`, `video_path`, `duration`, `quality`, `language`, `status`, `featured`) VALUES
('Avengers: Cuộc Chiến Vô Cực', 'avengers-cuoc-chien-vo-cuc', 'Avengers: Infinity War', 'Các siêu anh hùng Avengers phải hợp tác để ngăn chặn kẻ ác mộng Thanos.', 1, 2, 2018, 'Anthony Russo, Joe Russo', 'Robert Downey Jr., Chris Evans, Tom Holland', 'https://www.youtube.com/embed/6ZfuNTqWZN0', 'youtube', NULL, ' 149 phút', 'Full HD', 'Vietsub', 'active', 1),
('Chàng Trai Năm Ấy', 'chang-trai-nam-ay', 'The Boy', 'Một cô gái trẻ được thuê để chăm sóc một cậu bé bí ẩn và phát hiện ra những bí mật kinh hoàng.', 4, 9, 2016, 'William Brent Bell', 'Lauren Cohan, Rupert Evans, James Russell', NULL, 'url', 'https://example.com/the-boy.mp4', '97 phút', 'Full HD', 'Vietsub', 'active', 0),
('Spider-Man: Không Có Nhà', 'spider-man-khong-co-nha', 'Spider-Man: No Way Home', 'Peter Parker phải đối mặt với các sở hữu quyền lực từ những vũ trụ song song.', 5, 2, 2021, 'Jon Watts', 'Tom Holland, Zendaya, Tobey Maguire', 'https://www.youtube.com/embed/JfVOs4VSpmA', 'youtube', NULL, '148 phút', 'Full HD', 'Vietsub', 'active', 1),
('Joker', 'joker', 'Joker', 'Câu chuyện về sự biến đổi của một người đàn ông tẻ nhạt thành kẻ cuồng loạn.', 9, 2, 2019, 'Todd Phillips', 'Joaquin Phoenix, Robert De Niro, Zazie Beetz', NULL, 'youtube', 'https://www.youtube.com/embed/t433PEQvMmc', '122 phút', '4K', 'Vietsub', 'active', 0),
('Inception', 'inception', 'Inception', 'Một tên cướp tinh vi được giao nhiệm vụ thực hiện điều bất khả thi.', 5, 2, 2010, 'Christopher Nolan', 'Leonardo DiCaprio, Marion Cotillard, Tom Hardy', NULL, 'youtube', 'https://www.youtube.com/embed/8ZcmTl_1ER8', '148 phút', 'Full HD', 'Vietsub', 'active', 1),
('Kỳ Dị Ở Thứ 3', 'ky-di-o-thu-ba', 'Kung Fu Panda', 'Một chú gấu trúc vô dụng được chọn để trở thành chiến sĩ của thôn.', 6, 2, 2008, 'Mark Osborne, John Stevenson', 'Jack Black, Dustin Hoffman, Angelina Jolie', NULL, 'youtube', 'https://www.youtube.com/embed/sTsHZI_SEzw', '92 phút', 'Full HD', 'Vietsub', 'active', 0);

-- Tập phim mẫu (cho Kung Fu Panda - series)
INSERT INTO `movie_episodes` (`movie_id`, `episode_number`, `title`, `video_type`, `video_path`, `duration`, `status`) VALUES
(6, 1, 'Tập 1: Chiến sĩ Trong Mơ', 'youtube', 'https://www.youtube.com/embed/sTsHZI_SEzw', '92 phút', 'active');

-- Dữ liệu đánh giá mẫu
INSERT INTO `ratings` (`user_id`, `movie_id`, `rating`) VALUES
(2, 1, 9),
(2, 3, 8),
(2, 5, 9);
