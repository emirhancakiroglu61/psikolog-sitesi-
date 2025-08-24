-- Psikolog Merkezi Veritabanı Kurulum Dosyası

-- Veritabanını oluştur
CREATE DATABASE IF NOT EXISTS psikolog_merkezi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE psikolog_merkezi;

-- Blog tablosu
CREATE TABLE IF NOT EXISTS blog (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    image_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Beslenme ve diyetetik tablosu
CREATE TABLE IF NOT EXISTS beslenme_diyetetik (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    image_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Randevular tablosu (yeni yapı)
CREATE TABLE IF NOT EXISTS appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    national_id VARCHAR(11) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    email VARCHAR(255),
    preferred_date DATE NOT NULL,
    appointment_time VARCHAR(10),
    service_type VARCHAR(100) NOT NULL,
    message TEXT,
    status ENUM('pending', 'approved', 'rejected', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Mesajlar tablosu
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('unread', 'read') DEFAULT 'unread',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Admin tablosu
CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    active TINYINT(1) DEFAULT 1,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Danışanlar tablosu
CREATE TABLE IF NOT EXISTS patients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    national_id VARCHAR(11) NOT NULL UNIQUE,
    details TEXT,
    active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Örnek blog yazıları
INSERT INTO blog (title, content, created_at) VALUES
('Stres Yönetimi Teknikleri', 'Günlük hayatta karşılaştığımız stres faktörlerini nasıl yönetebileceğimiz hakkında detaylı bir rehber...', NOW()),
('Anksiyete ile Başa Çıkma Yolları', 'Anksiyete belirtilerini tanıma ve bu durumla başa çıkma yöntemleri hakkında uzman görüşleri...', NOW()),
('Sağlıklı İlişki Kurma', 'Partnerinizle daha sağlıklı ve mutlu bir ilişki kurmanın yolları...', NOW());

-- Örnek beslenme içerikleri
INSERT INTO beslenme_diyetetik (title, content, created_at) VALUES
('Psikolojik Faktörlerin Beslenmeye Etkisi', 'Ruh halimizin yeme alışkanlıklarımızı nasıl etkilediği ve sağlıklı beslenme için öneriler...', NOW()),
('Kilo Verme Sürecinde Psikoloji', 'Kilo verme sürecinde karşılaşılan psikolojik zorluklar ve bunlarla başa çıkma yöntemleri...', NOW()),
('Yeme Bozuklukları ve Tedavi Yöntemleri', 'Yeme bozukluklarının nedenleri, belirtileri ve tedavi yöntemleri hakkında detaylı bilgi...', NOW());

-- Örnek admin kullanıcısı (şifre: admin123)
INSERT INTO admin (username, first_name, last_name, email, password) VALUES
('admin', 'Admin', 'User', 'admin@psikologmerkezi.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- İndeksler
CREATE INDEX idx_blog_created_at ON blog(created_at);
CREATE INDEX idx_beslenme_created_at ON beslenme_diyetetik(created_at);
CREATE INDEX idx_appointments_status ON appointments(status);
CREATE INDEX idx_appointments_created_at ON appointments(created_at);
CREATE INDEX idx_messages_status ON messages(status);
CREATE INDEX idx_messages_created_at ON messages(created_at); 

-- Admin şifre sıfırlama tokenleri
CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    email VARCHAR(255) NOT NULL,
    token_hash CHAR(64) NOT NULL,
    ip VARCHAR(45) DEFAULT NULL,
    user_agent VARCHAR(255) DEFAULT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_token_hash (token_hash),
    INDEX idx_expires_at (expires_at),
    FOREIGN KEY (admin_id) REFERENCES admin(id) ON DELETE CASCADE
);