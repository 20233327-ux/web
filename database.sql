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
('Titanic', 'titanic', 'Titanic', 'Một tình yêu giữa hai người thuộc các tầng lớp xã hội khác nhau trên chiếc tàu "bất khả xâm phạm".', 2, 2, 1997, 'James Cameron', 'Leonardo DiCaprio, Kate Winslet, Billy Zane', NULL, 'youtube', 'https://www.youtube.com/embed/CHYggFEqXQ4', '194 phút', 'Full HD', 'Vietsub', 'active', 0),
('Bữa Tiệc Trác Táo', 'bua-tiec-trac-tao', 'The Hangover', 'Ba người bạn thức dậy mà không nhớ gì về đêm hôm trước và phải tìm kiếm bạn mình bị mất.', 3, 2, 2009, 'Todd Phillips', 'Bradley Cooper, Ed Helms, Zach Galifianakis', NULL, 'youtube', 'https://www.youtube.com/embed/tmeJBRiAqVE', '100 phút', 'Full HD', 'Vietsub', 'active', 0),
('War Horse', 'war-horse', 'War Horse', 'Câu chuyện về một chú ngựa và những người bạn của nó trong Thế chiến I.', 11, 9, 2011, 'Steven Spielberg', 'Jeremy Irvine, Peter Mullan, Emily Watson', NULL, 'youtube', 'https://www.youtube.com/embed/Sw0E6lHRy-I', '146 phút', 'Full HD', 'Vietsub', 'active', 1),
('Truy Tìm Hạnh Phúc', 'truy-tim-hanh-phuc', 'The Pursuit of Happyness', 'Một người bố đơn thân cam kết mang lại cuộc sống tốt hơn cho con trai mình.', 14, 2, 2006, 'Gabriele Muccino', 'Will Smith, Jaden Smith, Thandie Newton', NULL, 'youtube', 'https://www.youtube.com/embed/LSrC_nkWVpA', '117 phút', 'Full HD', 'Vietsub', 'active', 0),
('Mission: Impossible - Fallout', 'mission-impossible-fallout', 'Mission: Impossible - Fallout', 'Ethan Hunt và đội ngũ IMF phải ngăn chặn một thảm họa sắp xảy ra vì chứng bại của một nhiệm vụ trước đó.', 7, 2, 2018, 'Christopher McQuarrie', 'Tom Cruise, Henry Cavill, Ving Rhames', NULL, 'youtube', 'https://www.youtube.com/embed/3sLC5UQo8hc', '147 phút', 'Full HD', 'Vietsub', 'active', 0),
('Interstellar', 'interstellar', 'Interstellar', 'Một nhóm nhà du hành vũ trụ phải vượt qua lỗ giun gần Sao Thổ để bảo tồn nhân loại.', 5, 2, 2014, 'Christopher Nolan', 'Matthew McConaughey, Anne Hathaway, Jessica Chastain', NULL, 'youtube', 'https://www.youtube.com/embed/0vywZeB8uvU', '169 phút', '4K', 'Vietsub', 'active', 1),
('Wonder', 'wonder', 'Wonder', 'Một cậu bé có khuôn mặt khác thường bước vào trường trung học công lập lần đầu tiên.', 14, 2, 2017, 'Stephen Chbosky', 'Jacob Tremblay, Julia Roberts, Owen Wilson', NULL, 'youtube', 'https://www.youtube.com/embed/OEVpL5QxAUM', '113 phút', 'Full HD', 'Vietsub', 'active', 0),
('Forrest Gump', 'forrest-gump', 'Forrest Gump', 'Một người đàn ông với chỉ số IQ thấp nhưng trái tim vàng thực hiện những điều phi thường trong cuộc sống.', 14, 2, 1994, 'Robert Zemeckis', 'Tom Hanks, Sally Field, Gary Sinise', NULL, 'youtube', 'https://www.youtube.com/embed/bLvqoByUQkc', '142 phút', 'Full HD', 'Vietsub', 'active', 1),
('The Notebook', 'the-notebook', 'The Notebook', 'Một nghệ sĩ và một cô gái có hoàn cảnh khác nhau yêu nhau một cách điên cuồng.', 2, 2, 2004, 'Nick Cassavetes', 'Ryan Gosling, Rachel McAdams', NULL, 'youtube', 'https://www.youtube.com/embed/EWd7cECpyc8', '123 phút', 'Full HD', 'Vietsub', 'active', 0),
('Crazy Rich Asians', 'crazy-rich-asians', 'Crazy Rich Asians', 'Một cô gái người Mỹ gốc Á được mờ tối bởi sự giàu có và công khai tham dự của gia đình bạn trai.', 3, 2, 2018, 'Jon M. Chu', 'Constance Wu, Henry Golding, Michelle Yeoh', NULL, 'youtube', 'https://www.youtube.com/embed/ZQ-YX-5bAs0', '120 phút', 'Full HD', 'Vietsub', 'active', 0);

-- Tập phim mẫu (cho Kung Fu Panda - series)
INSERT INTO `movie_episodes` (`movie_id`, `episode_number`, `title`, `video_type`, `video_path`, `duration`, `status`) VALUES
(6, 1, 'Tập 1: Chiến sĩ Trong Mơ', 'youtube', 'https://www.youtube.com/embed/sTsHZI_SEzw', '92 phút', 'active');

-- Dữ liệu đánh giá mẫu
INSERT INTO `ratings` (`user_id`, `movie_id`, `rating`) VALUES
(2, 1, 9),
(2, 3, 8),
(2, 5, 9);
