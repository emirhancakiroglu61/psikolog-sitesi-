-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1
-- Üretim Zamanı: 24 Ağu 2025, 16:11:17
-- Sunucu sürümü: 10.4.32-MariaDB
-- PHP Sürümü: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `psikolog_merkezi`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `active` tinyint(1) DEFAULT 1,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `admin`
--

INSERT INTO `admin` (`id`, `username`, `first_name`, `last_name`, `email`, `password`, `active`, `last_login`, `created_at`) VALUES
(1, 'admin', 'Dr. Emirhan', 'Çakıroğlu', 'emirhan678c@gmail.com', '$2y$10$ycv2W/Gx05NZv2SFhLj3rugv9tbUE5A6z.F6B5YXSHkOWtE3LVvBW', 1, '2025-08-24 13:27:33', '2025-07-20 19:27:15');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `national_id` varchar(11) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `preferred_date` date NOT NULL,
  `appointment_time` varchar(20) NOT NULL,
  `service_type` varchar(100) NOT NULL,
  `message` text DEFAULT NULL,
  `status` enum('pending','approved','rejected','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `beslenme_diyetetik`
--

CREATE TABLE `beslenme_diyetetik` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `beslenme_diyetetik`
--

INSERT INTO `beslenme_diyetetik` (`id`, `title`, `content`, `image_path`, `created_at`, `updated_at`) VALUES
(1, 'Psikolojik Faktörlerin Beslenmeye Etkisi', 'Ruh halimizin yeme alışkanlıklarımızı nasıl etkilediği ve sağlıklı beslenme için öneriler...', NULL, '2025-07-20 19:27:15', '2025-07-20 19:27:15'),
(2, 'Kilo Verme Sürecinde Psikoloji', 'Kilo verme sürecinde karşılaşılan psikolojik zorluklar ve bunlarla başa çıkma yöntemleri...', NULL, '2025-07-20 19:27:15', '2025-07-20 19:27:15'),
(3, 'Yeme Bozuklukları ve Tedavi Yöntemleri', 'Yeme bozukluklarının nedenleri, belirtileri ve tedavi yöntemleri hakkında detaylı bilgi...', NULL, '2025-07-20 19:27:15', '2025-07-20 19:27:15'),
(4, 'adadadadadadad', 'sadasdasdasdsadsa', NULL, '2025-07-21 12:29:48', '2025-07-21 12:37:13'),
(6, 'Yemeyinnnn', 'şişmanlamak istemiyorsan yeme', 'beslenme_1755612198_68a48426978be.jpg', '2025-08-19 14:03:18', '2025-08-19 14:03:18'),
(7, 'dsadadasd', 'adadada', NULL, '2025-08-19 14:04:13', '2025-08-19 14:04:13'),
(8, 'dasdadasdadad', 'asdasdadad', NULL, '2025-08-19 14:04:18', '2025-08-19 14:04:18');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `blog`
--

CREATE TABLE `blog` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `blog`
--

INSERT INTO `blog` (`id`, `title`, `content`, `image_path`, `created_at`, `updated_at`) VALUES
(4, 'Cici Kuş', 'babacık babacık', 'blog_1753091280_687e0cd01ad65.jpg', '2025-07-21 09:48:00', '2025-07-21 09:48:00'),
(5, 'İKİZ KULELER', 'Bir  kule olarak uçaktan kaçmanızın yollları...', 'blog_1753102439_687e38675b769.jpg', '2025-07-21 12:37:56', '2025-07-21 12:53:59'),
(6, 'Küfürbaz haydo', 'haydonun sürekli sinirli davranması insanlara saldırması bir çocukluk tramvası', 'blog_1753102552_687e38d8ba5d0.jpg', '2025-07-21 12:55:52', '2025-07-21 12:55:52'),
(7, 'İlişki tavsiyeleri', 'evlendiğiniz kişi eğer gorilse ve  ayrılma isteğinizi kendinizde saklı tutun (parçalara ayrılmak istemiyorsanız )', 'blog_1753102787_687e39c379840.png', '2025-07-21 12:59:47', '2025-07-21 12:59:47'),
(8, 'Depresyonla mücadele', 'Depresyon bünyede yorgunluk verir (bol bol Anzer balı yiyin)', 'blog_1753102944_687e3a60db6b7.jpg', '2025-07-21 13:02:24', '2025-07-29 12:17:36'),
(9, 'çocuk ve ergen psikolojisi', 'çocuğunuz sizi korkutuyorsa içine cin kaçmış olabilir(hemen   şeytan terliklenmeli )', 'blog_1753103283_687e3bb336c0c.png', '2025-07-21 13:08:03', '2025-07-21 13:08:03'),
(10, 'yarasa', 'sdaadadasdas', 'blog_1755612014_68a4836ecbe6c.jpg', '2025-08-19 14:00:14', '2025-08-19 14:00:14');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `consultants`
--

CREATE TABLE `consultants` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `expertise` varchar(255) DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` enum('unread','read') DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token_hash` char(64) NOT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `password_resets`
--

INSERT INTO `password_resets` (`id`, `admin_id`, `email`, `token_hash`, `ip`, `user_agent`, `expires_at`, `used_at`, `created_at`) VALUES
(1, 1, 'emirhan678c@gmail.com', 'aafeb4e338e750d38a705ffbf6f4800bc70c093d6485e34ef758ada2b73b5deb', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-19 13:32:10', '2025-08-19 13:32:47', '2025-08-19 10:32:10');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `patients`
--

CREATE TABLE `patients` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `national_id` varchar(11) NOT NULL,
  `details` text DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `php_sessions`
--

CREATE TABLE `php_sessions` (
  `id` varchar(128) NOT NULL,
  `data` blob NOT NULL,
  `timestamp` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `php_sessions`
--

INSERT INTO `php_sessions` (`id`, `data`, `timestamp`) VALUES
('5egjkamaig6ro4t0gmgu8hb3e1', '', 1753364606),
('8giqshmhv53rmadru6c52vbv2t', '', 1753364616),
('u589tbd8dhc9cbps5rcns08ocu', 0x6c6f67696e5f617474656d7074737c693a303b6c6173745f617474656d70745f74696d657c693a303b61646d696e5f6c6f676765645f696e7c623a313b61646d696e5f69647c693a313b61646d696e5f757365726e616d657c733a353a2261646d696e223b61646d696e5f656d61696c7c733a32353a2261646d696e407073696b6f6c6f676d65726b657a692e636f6d223b61646d696e5f66697273745f6e616d657c733a31313a2244722e20456d697268616e223b61646d696e5f6c6173745f6e616d657c733a31323a22c387616bc4b1726fc49f6c75223b6c6f67696e5f74696d657c693a313735333336343631343b637372665f746f6b656e7c733a36343a2262643463613766326436333634633635373665303438376532323431393263353461373433656437643830613731643066396634336334616265346538323232223b, 1753364614);

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Tablo için indeksler `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_appointments_status` (`status`),
  ADD KEY `idx_appointments_created_at` (`created_at`);

--
-- Tablo için indeksler `beslenme_diyetetik`
--
ALTER TABLE `beslenme_diyetetik`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_beslenme_created_at` (`created_at`);

--
-- Tablo için indeksler `blog`
--
ALTER TABLE `blog`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_blog_created_at` (`created_at`);

--
-- Tablo için indeksler `consultants`
--
ALTER TABLE `consultants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Tablo için indeksler `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_messages_status` (`status`),
  ADD KEY `idx_messages_created_at` (`created_at`);

--
-- Tablo için indeksler `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_token_hash` (`token_hash`),
  ADD KEY `idx_expires_at` (`expires_at`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Tablo için indeksler `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `national_id` (`national_id`);

--
-- Tablo için indeksler `php_sessions`
--
ALTER TABLE `php_sessions`
  ADD PRIMARY KEY (`id`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- Tablo için AUTO_INCREMENT değeri `beslenme_diyetetik`
--
ALTER TABLE `beslenme_diyetetik`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Tablo için AUTO_INCREMENT değeri `blog`
--
ALTER TABLE `blog`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Tablo için AUTO_INCREMENT değeri `consultants`
--
ALTER TABLE `consultants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Tablo için AUTO_INCREMENT değeri `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `patients`
--
ALTER TABLE `patients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
