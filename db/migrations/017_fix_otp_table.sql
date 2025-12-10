ALTER TABLE `otp_codes`
CHANGE COLUMN `email` `identifier` VARCHAR(255) NOT NULL,
CHANGE COLUMN `code_hash` `code` VARCHAR(255) NOT NULL;
