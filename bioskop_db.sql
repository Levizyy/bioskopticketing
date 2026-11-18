-- =============================================
-- Database: bioskop_db
-- Website Pemesanan Tiket Bioskop
-- =============================================

CREATE DATABASE IF NOT EXISTS bioskop_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bioskop_db;

-- Tabel users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel film
CREATE TABLE film (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(200) NOT NULL,
    genre VARCHAR(100) NOT NULL,
    durasi INT NOT NULL COMMENT 'dalam menit',
    rating VARCHAR(10) NOT NULL,
    sinopsis TEXT,
    poster VARCHAR(255),
    status ENUM('tayang','tidak') DEFAULT 'tayang',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel jadwal
CREATE TABLE jadwal (
    id INT AUTO_INCREMENT PRIMARY KEY,
    film_id INT NOT NULL,
    studio VARCHAR(50) NOT NULL,
    tanggal DATE NOT NULL,
    jam TIME NOT NULL,
    harga DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (film_id) REFERENCES film(id) ON DELETE CASCADE
);

-- Tabel kursi
CREATE TABLE kursi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    jadwal_id INT NOT NULL,
    nomor_kursi VARCHAR(10) NOT NULL,
    status ENUM('tersedia','terisi') DEFAULT 'tersedia',
    FOREIGN KEY (jadwal_id) REFERENCES jadwal(id) ON DELETE CASCADE
);

-- Tabel transaksi
CREATE TABLE transaksi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    jadwal_id INT NOT NULL,
    kursi TEXT NOT NULL,
    total_harga DECIMAL(10,2) NOT NULL,
    tanggal_pesan TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending','confirmed','cancelled') DEFAULT 'confirmed',
    kode_booking VARCHAR(20) UNIQUE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (jadwal_id) REFERENCES jadwal(id) ON DELETE CASCADE
);

-- =============================================
-- DATA AWAL
-- =============================================

-- Admin & User default
-- Password: admin123 | user123
INSERT INTO users (nama, email, password, role) VALUES
('Administrator', 'admin@bioskop.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('John Doe', 'user@bioskop.com', '$2y$10$TKh8H1.PbyxgL0EsB1iXXuIsD9f1HkJHoupfolio8STEma0cymewi', 'user');

-- Film
INSERT INTO film (judul, genre, durasi, rating, sinopsis, poster, status) VALUES
('Avengers: Secret Wars', 'Action, Sci-Fi', 180, 'PG-13', 'Para Avengers kembali bersatu untuk menghadapi ancaman terbesar dari multiverse yang mengancam seluruh eksistensi realita. Sebuah pertempuran epik yang menentukan nasib semua semesta.', 'poster-avengers-secret-wars.svg', 'tayang'),
('The Dark Forest', 'Horror, Thriller', 115, 'R', 'Sekelompok peneliti menemukan hutan terlarang yang menyimpan rahasia gelap. Satu per satu mereka menghilang dalam kegelapan yang tak pernah mereka bayangkan sebelumnya.', 'poster-the-dark-forest.svg', 'tayang'),
('Love in Tokyo', 'Romance, Drama', 120, 'PG', 'Kisah cinta dua jiwa yang bertemu di Tokyo. Di antara gemerlapnya kota dan sakura yang bermekaran, mereka belajar arti cinta sejati yang melampaui batas budaya.', 'poster-love-in-tokyo.svg', 'tayang'),
('Galactic Warriors', 'Action, Adventure', 150, 'PG-13', 'Di luar angkasa jauh, sebuah kelompok prajurit antargalaksi berjuang melindungi alam semesta dari kejahatan kosmik yang mengancam seluruh planet dan peradaban.', 'poster-galactic-warriors.svg', 'tayang'),
('The Last Detective', 'Mystery, Crime', 130, 'R', 'Seorang detektif veteran menerima kasus terakhirnya: sebuah pembunuhan berantai yang ternyata berkaitan dengan masa lalunya sendiri yang kelam dan penuh misteri.', 'poster-the-last-detective.svg', 'tayang'),
('Funny Business', 'Comedy', 95, 'PG', 'Komedi segar tentang seorang karyawan biasa yang tiba-tiba harus menggantikan CEO perusahaan besar. Kekacauan lucu pun tak terhindarkan dalam petualangan korporat ini.', 'poster-funny-business.svg', 'tayang');

-- Jadwal
INSERT INTO jadwal (film_id, studio, tanggal, jam, harga) VALUES
(1, 'Studio 1', CURDATE(), '10:00:00', 75000),
(1, 'Studio 1', CURDATE(), '13:00:00', 75000),
(1, 'Studio 2', CURDATE(), '16:00:00', 85000),
(1, 'Studio 2', DATE_ADD(CURDATE(), INTERVAL 1 DAY), '19:00:00', 85000),
(2, 'Studio 3', CURDATE(), '11:00:00', 70000),
(2, 'Studio 3', CURDATE(), '14:00:00', 70000),
(3, 'Studio 1', DATE_ADD(CURDATE(), INTERVAL 1 DAY), '12:00:00', 65000),
(3, 'Studio 2', DATE_ADD(CURDATE(), INTERVAL 1 DAY), '15:00:00', 65000),
(4, 'Studio 4', CURDATE(), '13:00:00', 80000),
(4, 'Studio 4', CURDATE(), '17:00:00', 80000),
(5, 'Studio 3', DATE_ADD(CURDATE(), INTERVAL 2 DAY), '10:00:00', 70000),
(6, 'Studio 1', DATE_ADD(CURDATE(), INTERVAL 2 DAY), '13:00:00', 60000);

-- Generate kursi untuk setiap jadwal (5 baris x 8 kursi = 40 kursi)
DELIMITER $$
CREATE PROCEDURE generate_kursi()
BEGIN
    DECLARE j INT DEFAULT 1;
    DECLARE baris CHAR(1);
    DECLARE nomor INT;
    DECLARE total_jadwal INT;
    
    SELECT COUNT(*) INTO total_jadwal FROM jadwal;
    
    WHILE j <= total_jadwal DO
        SET baris = 'A';
        WHILE baris <= 'E' DO
            SET nomor = 1;
            WHILE nomor <= 8 DO
                INSERT INTO kursi (jadwal_id, nomor_kursi, status) 
                VALUES (j, CONCAT(baris, nomor), 'tersedia');
                SET nomor = nomor + 1;
            END WHILE;
            SET baris = CHAR(ASCII(baris) + 1);
        END WHILE;
        SET j = j + 1;
    END WHILE;
END$$
DELIMITER ;

CALL generate_kursi();
DROP PROCEDURE generate_kursi;

-- Sample transaksi
INSERT INTO transaksi (user_id, jadwal_id, kursi, total_harga, status, kode_booking) VALUES
(2, 1, 'B3,B4', 150000, 'confirmed', 'BKP-001-2024'),
(2, 5, 'C5', 70000, 'confirmed', 'BKP-002-2024');

-- Update kursi yang sudah terisi
UPDATE kursi SET status = 'terisi' WHERE jadwal_id = 1 AND nomor_kursi IN ('B3','B4');
UPDATE kursi SET status = 'terisi' WHERE jadwal_id = 5 AND nomor_kursi = 'C5';
