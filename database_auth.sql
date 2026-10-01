CREATE DATABASE IF NOT EXISTS ujian_asts CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ujian_asts;

-- Membuat tabel baru jika belum ada.
CREATE TABLE IF NOT EXISTS accounts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NULL,
    email VARCHAR(150) NOT NULL,
    whatsapp VARCHAR(20) NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user','admin') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Migrasi database lama: tambahkan kolom yang belum ada.
SET @db := DATABASE();

SET @sql := IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='accounts' AND COLUMN_NAME='name')=0,
    'ALTER TABLE accounts ADD COLUMN name VARCHAR(100) NULL AFTER id',
    'SELECT 1'
);
PREPARE s1 FROM @sql; EXECUTE s1; DEALLOCATE PREPARE s1;

SET @sql := IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='accounts' AND COLUMN_NAME='whatsapp')=0,
    'ALTER TABLE accounts ADD COLUMN whatsapp VARCHAR(20) NULL AFTER email',
    'SELECT 1'
);
PREPARE s2 FROM @sql; EXECUTE s2; DEALLOCATE PREPARE s2;

SET @sql := IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='accounts' AND COLUMN_NAME='role')=0,
    "ALTER TABLE accounts ADD COLUMN role ENUM('user','admin') NOT NULL DEFAULT 'user' AFTER password",
    'SELECT 1'
);
PREPARE s3 FROM @sql; EXECUTE s3; DEALLOCATE PREPARE s3;

SET @sql := IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='accounts' AND COLUMN_NAME='created_at')=0,
    'ALTER TABLE accounts ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
    'SELECT 1'
);
PREPARE s4 FROM @sql; EXECUTE s4; DEALLOCATE PREPARE s4;

-- Isi nama kosong dari email agar data lama tetap bisa ditampilkan.
UPDATE accounts
SET name = LEFT(SUBSTRING_INDEX(email, '@', 1), 100)
WHERE (name IS NULL OR name='') AND email IS NOT NULL AND email<>'';

-- Akun admin bawaan.
-- Email    : admin@ujianasts.local
-- Password : Admin123!
INSERT INTO accounts (name,email,whatsapp,password,role)
SELECT 'Administrator','admin@ujianasts.local','080000000000',
'$2y$12$jeVIOfKRqSSZ/ldg6/boTuJxTkoJ2RRbtURkGNg8hr0ZZtu4qSMSO','admin'
WHERE NOT EXISTS (SELECT 1 FROM accounts WHERE email='admin@ujianasts.local');

UPDATE accounts
SET role='admin', name='Administrator', whatsapp='080000000000',
    password='$2y$12$jeVIOfKRqSSZ/ldg6/boTuJxTkoJ2RRbtURkGNg8hr0ZZtu4qSMSO'
WHERE email='admin@ujianasts.local';

-- Semua akun selain admin bawaan dianggap user.
UPDATE accounts SET role='user' WHERE email<>'admin@ujianasts.local' AND (role IS NULL OR role='');
