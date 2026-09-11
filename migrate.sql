USE `recipe_book`;

CREATE TABLE IF NOT EXISTS `reviews` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `recipe_id` INT(11) NOT NULL,
  `user_id` INT(11) NOT NULL,
  `rating` TINYINT(1) NOT NULL CHECK (`rating` BETWEEN 1 AND 5),
  `comment` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_reviews_recipes` (`recipe_id`),
  KEY `fk_reviews_users` (`user_id`),
  CONSTRAINT `fk_reviews_recipes` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_reviews_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `favorites` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `recipe_id` INT(11) NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_recipe_unique` (`user_id`, `recipe_id`),
  CONSTRAINT `fk_fav_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_fav_recipes` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Sample Reviews
INSERT INTO `reviews` (`recipe_id`, `user_id`, `rating`, `comment`) VALUES
(1, 2, 5, 'Absolutely authentic Carbonara! The egg and cheese emulsion turned out perfectly silky.'),
(1, 3, 5, 'Quick, delicious, and easy to follow. Will definitely make again!'),
(2, 1, 4, 'Great breakfast recipe. Poaching the egg was surprisingly easy.'),
(3, 1, 5, 'Resturant quality Chicken Tikka Masala! The heavy cream balance is perfect.'),
(4, 2, 5, 'Rich and chocolatey with a perfectly molten lava center. Highly recommended!');
