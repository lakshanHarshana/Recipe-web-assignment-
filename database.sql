-- Digital Recipe Book Database Schema
-- Project: ICT 2209 - Web Technologies Mini Project
-- Theme: Digital Recipe Book

CREATE DATABASE IF NOT EXISTS `recipe_book` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `recipe_book`;

-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `reviews`;
DROP TABLE IF EXISTS `favorites`;
DROP TABLE IF EXISTS `recipes`;
DROP TABLE IF EXISTS `messages`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `recipes`
-- --------------------------------------------------------
CREATE TABLE `recipes` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `category` VARCHAR(50) NOT NULL,
  `prep_time` INT(11) NOT NULL COMMENT 'In minutes',
  `cook_time` INT(11) NOT NULL COMMENT 'In minutes',
  `servings` INT(11) NOT NULL,
  `difficulty` ENUM('Easy', 'Medium', 'Hard') NOT NULL DEFAULT 'Medium',
  `ingredients` TEXT NOT NULL,
  `instructions` TEXT NOT NULL,
  `image_url` VARCHAR(500) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_recipes_users` (`user_id`),
  CONSTRAINT `fk_recipes_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `messages`
-- --------------------------------------------------------
CREATE TABLE `messages` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `subject` VARCHAR(150) DEFAULT 'General Inquiry',
  `message` TEXT NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `reviews`
-- --------------------------------------------------------
CREATE TABLE `reviews` (
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

-- --------------------------------------------------------
-- Table structure for table `favorites`
-- --------------------------------------------------------
CREATE TABLE `favorites` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `recipe_id` INT(11) NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_recipe_unique` (`user_id`, `recipe_id`),
  CONSTRAINT `fk_fav_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_fav_recipes` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Sample Data Insertion
-- Password for all sample users is: Password123!
-- Hashed using password_hash('Password123!', PASSWORD_DEFAULT)
-- --------------------------------------------------------

INSERT INTO `users` (`id`, `username`, `email`, `password`, `created_at`) VALUES
(1, 'chef_maria', 'maria@example.com', '$2y$10$wO7v8GgS4qP2hHqD/mJgje1M0Z/TzM0D7Gg1V1n5wW7Y0wZ9eE2yS', '2026-01-10 10:00:00'),
(2, 'john_doe', 'john@example.com', '$2y$10$wO7v8GgS4qP2hHqD/mJgje1M0Z/TzM0D7Gg1V1n5wW7Y0wZ9eE2yS', '2026-01-15 11:30:00'),
(3, 'spice_master', 'alex@example.com', '$2y$10$wO7v8GgS4qP2hHqD/mJgje1M0Z/TzM0D7Gg1V1n5wW7Y0wZ9eE2yS', '2026-02-01 14:15:00');

INSERT INTO `recipes` (`id`, `user_id`, `title`, `category`, `prep_time`, `cook_time`, `servings`, `difficulty`, `ingredients`, `instructions`, `image_url`, `created_at`) VALUES
(1, 1, 'Classic Creamy Carbonara', 'Italian', 15, 20, 4, 'Medium', 
'400g Spaghetti\n200g Guanciale or Pancetta, diced\n4 Large Egg yolks + 1 Whole egg\n100g Pecorino Romano cheese, freshly grated\nFreshly cracked black pepper\nSalt for pasta water', 
'1. Bring a large pot of salted water to a boil and cook spaghetti until al dente.\n2. In a skillet over medium heat, crisp the guanciale until golden brown. Remove from heat.\n3. Whisk egg yolks, whole egg, grated Pecorino, and black pepper in a bowl to create a thick paste.\n4. Reserve 1 cup of pasta water, then drain spaghetti.\n5. Toss pasta directly into the skillet with guanciale off the heat.\n6. Pour in the egg & cheese mixture, whisking rapidly while adding reserved pasta water to form a silky sauce.\n7. Serve immediately topped with extra Pecorino and cracked pepper.', 
'https://images.unsplash.com/photo-1612874742237-6526221588e3?auto=format&fit=crop&w=800&q=80', 
'2026-02-10 12:00:00'),

(2, 1, 'Avocado & Poached Egg Toast', 'Breakfast', 10, 5, 2, 'Easy', 
'2 Slices Sourdough bread, toasted\n1 Ripe Avocado\n2 Fresh Eggs\n1 tbsp Lemon juice\n1 tbsp Extra virgin olive oil\nRed pepper flakes, salt, and black pepper', 
'1. Mash avocado with lemon juice, olive oil, salt, and pepper in a bowl.\n2. Bring a small pot of water with a dash of vinegar to a gentle simmer.\n3. Swirl water to create a whirlpool and drop egg into the center. Poach for 3 minutes.\n4. Spread mashed avocado generously over toasted sourdough.\n5. Top each toast with a poached egg, sprinkle red pepper flakes and black pepper.', 
'https://images.unsplash.com/photo-1525351484163-7529414344d8?auto=format&fit=crop&w=800&q=80', 
'2026-02-12 08:30:00'),

(3, 2, 'Authentic Chicken Tikka Masala', 'Asian', 25, 35, 4, 'Hard', 
'800g Chicken thighs, cut into bite-sized pieces\n1 cup Plain yogurt\n2 tbsp Garam masala\n1 tbsp Turmeric & Cumin\n1 Can (400g) Tomato purée\n1 cup Heavy cream\n1 Large Onion, finely chopped\n4 Cloves Garlic & 1 inch Ginger, minced', 
'1. Marinate chicken in yogurt, lemon juice, garlic, ginger, and spices for at least 30 mins.\n2. Sear chicken pieces in a hot skillet until charred on edges. Set aside.\n3. In a large saucepan, saute onions until golden. Add garlic, ginger, tomato purée, and spices.\n4. Simmer sauce for 15 minutes, then stir in heavy cream.\n5. Add cooked chicken into the sauce and simmer for another 10 minutes until chicken is tender.\n6. Serve hot garnished with fresh cilantro alongside Basmati rice and warm Garlic Naan.', 
'https://images.unsplash.com/photo-1565557623262-b51c2513a641?auto=format&fit=crop&w=800&q=80', 
'2026-02-14 19:00:00'),

(4, 3, 'Decadent Chocolate Lava Cake', 'Dessert', 20, 12, 2, 'Medium', 
'100g Dark chocolate (70% cocoa)\n100g Unsalted butter\n2 Eggs + 2 Egg yolks\n50g Caster sugar\n30g All-purpose flour\nButter and cocoa powder for ramekins', 
'1. Preheat oven to 200°C (400°F). Grease two ramekins with butter and dust with cocoa powder.\n2. Melt dark chocolate and butter together in a heatproof bowl over simmering water.\n3. Whisk eggs, yolks, and caster sugar in a separate bowl until thick and pale.\n4. Fold melted chocolate into egg mixture, then gently sift in flour.\n5. Divide batter between ramekins and bake for 12 minutes until edges are set but center soft.\n6. Invert onto plates and serve immediately with vanilla bean ice cream.', 
'https://images.unsplash.com/photo-1606313564200-e75d5e30476c?auto=format&fit=crop&w=800&q=80', 
'2026-02-18 21:15:00'),

(5, 2, 'Fresh Mediterranean Greek Salad', 'Healthy', 15, 0, 4, 'Easy', 
'4 Ripe Tomatoes, cut into wedges\n1 English Cucumber, sliced\n1 Red Onion, thinly sliced\n150g Kalamata olives\n200g Feta cheese block\n4 tbsp Extra virgin olive oil\n1 tbsp Dried oregano & Red wine vinegar', 
'1. In a large salad bowl, combine tomatoes, cucumber, red onion, and Kalamata olives.\n2. Drizzle with extra virgin olive oil and red wine vinegar.\n3. Gently toss ingredients together.\n4. Place a solid block of feta cheese on top of the salad.\n5. Sprinkle generously with dried oregano and freshly ground black pepper.', 
'https://images.unsplash.com/photo-1540420773420-3366772f4999?auto=format&fit=crop&w=800&q=80', 
'2026-02-20 13:45:00'),

(6, 3, 'Teriyaki Salmon Power Bowl', 'Healthy', 15, 15, 2, 'Easy', 
'2 Salmon fillets\n3 tbsp Teriyaki sauce\n1 cup Cooked Jasmine or Brown rice\n1 Avocado, sliced\n1 cup Steamed Edamame beans\n1 Shredded carrot\nSesame seeds & Green onions for garnish', 
'1. Pan-sear salmon fillets skin-side down in a hot skillet for 4 minutes.\n2. Flip salmon, pour teriyaki sauce into the skillet, and glaze fish for 3 minutes.\n3. Divide cooked rice into two serving bowls.\n4. Arrange glazed salmon, sliced avocado, edamame, and carrots neatly over the rice base.\n5. Drizzle remaining pan glaze and sprinkle toasted sesame seeds and chopped green onions.', 
'https://images.unsplash.com/photo-1467003909585-2f8a72700288?auto=format&fit=crop&w=800&q=80', 
'2026-02-22 18:20:00');

INSERT INTO `messages` (`id`, `name`, `email`, `subject`, `message`, `created_at`) VALUES
(1, 'Emily Watson', 'emily@example.com', 'Recipe Inquiry', 'Hi! I loved the Carbonara recipe. Do you have a vegetarian version of it?', '2026-02-25 09:30:00');

INSERT INTO `reviews` (`recipe_id`, `user_id`, `rating`, `comment`) VALUES
(1, 2, 5, 'Absolutely authentic Carbonara! The egg and cheese emulsion turned out perfectly silky.'),
(1, 3, 5, 'Quick, delicious, and easy to follow. Will definitely make again!'),
(2, 1, 4, 'Great breakfast recipe. Poaching the egg was surprisingly easy.'),
(3, 1, 5, 'Resturant quality Chicken Tikka Masala! The heavy cream balance is perfect.'),
(4, 2, 5, 'Rich and chocolatey with a perfectly molten lava center. Highly recommended!');
