USE `recipe_book`;

-- Update users role default to Customer
ALTER TABLE `users` MODIFY COLUMN `role` ENUM('Chef', 'Customer') NOT NULL DEFAULT 'Customer';

-- Add recipient_id to messages table if not exists
SET @dbname = DATABASE();
SET @tablename = 'messages';
SET @columnname = 'recipient_id';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  "ALTER TABLE messages ADD COLUMN recipient_id INT(11) DEFAULT NULL, ADD CONSTRAINT fk_messages_users FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE SET NULL;"
));
PREPARE alterMsgIfNotExists FROM @preparedStatement;
EXECUTE alterMsgIfNotExists;
DEALLOCATE PREPARE alterMsgIfNotExists;

-- Update existing sample users
UPDATE `users` SET `role` = 'Customer' WHERE `username` = 'john_doe';
UPDATE `users` SET `role` = 'Chef' WHERE `username` IN ('chef_maria', 'spice_master');
