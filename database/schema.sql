-- =========================================================
-- Database: photogram_db
-- =========================================================
CREATE DATABASE IF NOT EXISTS photogram_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE photogram_db;

-- Tabel users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) DEFAULT NULL,
    avatar VARCHAR(255) DEFAULT 'default-avatar.png',
    bio VARCHAR(255) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabel photos
CREATE TABLE IF NOT EXISTS photos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    caption TEXT DEFAULT NULL,
    filter_applied VARCHAR(30) DEFAULT 'normal',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Tabel likes
CREATE TABLE IF NOT EXISTS likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    photo_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_like (photo_id, user_id),
    FOREIGN KEY (photo_id) REFERENCES photos(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Tabel comments
CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    photo_id INT NOT NULL,
    user_id INT NOT NULL,
    comment TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (photo_id) REFERENCES photos(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Tabel views (untuk insight / analytics)
CREATE TABLE IF NOT EXISTS photo_views (
    id INT AUTO_INCREMENT PRIMARY KEY,
    photo_id INT NOT NULL,
    user_id INT DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    viewed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (photo_id) REFERENCES photos(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Index tambahan untuk mempercepat query dashboard
CREATE INDEX idx_photos_user ON photos(user_id);
CREATE INDEX idx_likes_photo ON likes(photo_id);
CREATE INDEX idx_comments_photo ON comments(photo_id);
CREATE INDEX idx_views_photo ON photo_views(photo_id);
CREATE INDEX idx_views_date ON photo_views(viewed_at);

-- User contoh (password: password123)
INSERT INTO users (username, email, password, full_name, bio) VALUES
('demo', 'demo@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.YeIeVCmv/eeZ.g.4/8ArE1qP0kJ.PSy1e', 'Demo User', 'Akun demo untuk mencoba aplikasi');
