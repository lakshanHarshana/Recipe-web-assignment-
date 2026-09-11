USE `recipe_book`;

-- Add role column if not exists
SET @dbname = DATABASE();
SET @tablename = 'users';
SET @columnname = 'role';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  "ALTER TABLE users ADD COLUMN role ENUM('Chef', 'Customer') NOT NULL DEFAULT 'Chef';"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Update existing sample users
UPDATE `users` SET `role` = 'Customer' WHERE `username` = 'john_doe';
UPDATE `users` SET `role` = 'Chef' WHERE `username` IN ('chef_maria', 'spice_master');
