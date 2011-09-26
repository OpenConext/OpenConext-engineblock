-- Add id field to login table (ported from manage patch-001.sql)
ALTER TABLE `log_logins` ADD COLUMN `id` INT AUTO_INCREMENT PRIMARY KEY;