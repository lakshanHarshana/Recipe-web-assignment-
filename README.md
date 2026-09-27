# FlavorCraft - Digital Recipe Book Web Application

**Course:** ICT 2209 – Web Technologies Mini Project  
**Institution:** Rajarata University of Sri Lanka | Faculty of Technology | Department of ICT  
**Theme:** Digital Recipe Book - Recipe search, dynamic content loading, form validation for user-submitted recipes.

---

## 📌 Project Overview

**FlavorCraft** is an interactive, responsive web application designed for food enthusiasts to explore, search, filter, and share delicious recipes. Built with modern HTML5, CSS3, Bootstrap 5.3, vanilla JavaScript (ES6+), PHP 8+, and MySQL, this project fulfills all basic, interactivity, authentication, and database requirements specified in the ICT 2209 project guidelines.

---

## ✨ Key Features & Requirements Checklist

### 1. Basic Structure & Responsiveness (20%)
- **Multi-page Architecture**: Includes `index.php` (Home/Catalog), `dashboard.php` (User Dashboard & Recipe Publisher), `contact.php` (Contact Form), `auth/register.php` (User Sign Up), `auth/login.php` (Authentication), and `auth/logout.php`.
- **Responsive Layout**: Designed with Bootstrap 5.3 fluid grid, responsive breakpoints, and mobile-friendly navigation.

### 2. JavaScript Features & Interactivity (15%)
- **Dynamic Content Updates & Live Search**: Real-time keyword search (searches title, ingredients, instructions) and category pill filter without full page refreshes via AJAX/JS API (`api/get_recipes.php`).
- **Interactive Image Slider**: Hero banner carousel with manual Next/Prev controls, auto-play timer (5s), pause-on-hover, and indicator dots.
- **Client-Side Form Validation**: Real-time validation on blur and submit for Register, Login, Add Recipe, and Contact forms with visual invalid error hints and green success indicators (`js/validation.js`).
- **Event Handling & Modals**: Click event listeners on recipe cards open a detail modal displaying full ingredient lists, step-by-step instructions, prep times, and author details.
- **Smooth Scrolling**: Back to top button and smooth scroll navigation for page sections (`js/main.js`).

### 3. Backend & Database Integration (20%)
- **MySQL Database (`recipe_book`)**: Includes relational tables (`users`, `recipes`, `messages`) with foreign keys and cascade deletion.
- **PDO Security**: All database queries use Prepared Statements to prevent SQL Injection vulnerabilities.

### 4. User Authentication (20%)
- **Registration (`auth/register.php`)**: Validates unique email and username, and securely hashes user passwords using `password_hash()` (BCRYPT).
- **Login (`auth/login.php`)**: Verifies credentials using `password_verify()`, regenerates session IDs to prevent session fixation, and manages session state.
- **Logout (`auth/logout.php`)**: Safely terminates active user sessions.

### 5. Contact Form & Data Handling (10%)
- **Contact Page (`contact.php`)**: Processes user queries, validates entries both client-side and server-side, and stores messages in the `messages` table.

---

## 📁 Project Directory Structure

```text
digital-recipe-book/
├── css/
│   └── style.css            # Custom theme styles, warm palette, animations, cards
├── js/
│   ├── main.js             # UI interactivity, smooth scrolling, modal data binder
│   ├── slider.js           # Interactive hero slider (auto & manual controls)
│   ├── search.js           # Live search & category filter engine (AJAX)
│   └── validation.js       # Real-time form validation engine
├── images/                  # Project assets & placeholders
├── includes/
│   ├── db.php              # PDO MySQL database connection
│   ├── functions.php       # Security helpers, session management, flash alerts
│   ├── header.php          # HTML head, Bootstrap CDN, navigation header
│   └── footer.php          # Footer, modal container, script imports
├── auth/
│   ├── register.php        # User registration form & logic
│   ├── login.php           # User login form & session creation
│   └── logout.php          # Session termination
├── api/
│   └── get_recipes.php     # JSON API endpoint for dynamic search
├── contact.php             # Contact form & message storage
├── index.php               # Main landing page, hero slider, recipe catalog
├── dashboard.php           # Authenticated user dashboard & recipe submitter
├── database.sql            # Ready-to-import MySQL database dump with seed data
└── README.md               # Project documentation & setup instructions
```

---

## 🛠️ Local Installation & Setup Guide (XAMPP / WAMP)

### Prerequisites
- Install **XAMPP** or **WAMP** (with PHP 8.0+ and MySQL/MariaDB).

### Step 1: Clone / Copy Project Files
Place the project directory inside your local web server root directory:
- **XAMPP**: `C:\xampp\htdocs\digital-recipe-book`
- **WAMP**: `C:\wamp64\www\digital-recipe-book`

Alternatively, git clone directly:
```bash
git clone https://github.com/dinethbamunuarachchige777-collab/Mini-Project---web-technologies-.git
```

### Step 2: Import Database in phpMyAdmin
1. Start **Apache** and **MySQL** in your XAMPP/WAMP control panel.
2. Open your web browser and navigate to `http://localhost/phpmyadmin/`.
3. Click on the **Import** tab in phpMyAdmin.
4. Click **Choose File** and select `database.sql` from the project folder.
5. Click **Import** (or **Go**). This creates the `recipe_book` database along with `users`, `recipes`, and `messages` tables populated with sample data.

### Step 3: Run Application
Open your browser and navigate to:
```text
http://localhost/digital-recipe-book/index.php
```
*(Or use PHP built-in server: `php -S localhost:8000` inside the project folder).*

---

## 🔑 Pre-Configured Test Accounts

| Role | Username / Email | Password |
| :--- | :--- | :--- |
| **Chef Maria** | `maria@example.com` or `chef_maria` | `Password123!` |
| **User John** | `john@example.com` or `john_doe` | `Password123!` |
| **Spice Master** | `alex@example.com` or `spice_master` | `Password123!` |

*(You can also register a new account on `auth/register.php`)*

---

## 🧪 Verification & Testing Instructions

1. **Test Live Search & Category Filter**:
   - Go to `index.php`.
   - Type `"Avocado"`, `"Carbonara"`, or `"Chicken"` in the search bar. Observe instant grid updates.
   - Click category filter pills (e.g. *Italian*, *Breakfast*, *Healthy*) to filter dishes dynamically.

2. **Test Interactive Hero Slider**:
   - Observe automatic slide transitions every 5 seconds.
   - Hover over the slider to pause transition.
   - Click Next (`>`), Prev (`<`), or Pause/Play buttons to manually control slides.

3. **Test Recipe Detail Modal**:
   - Click on any recipe card to open the modal pop-up with full ingredients, cooking steps, and print button.

4. **Test Form Validation & Authentication**:
   - Go to `auth/register.php` or `auth/login.php` or `contact.php`.
   - Submit empty fields or invalid emails to see real-time red error feedback.
   - Log in using test credentials (`maria@example.com` / `Password123!`).

5. **Test Recipe Submission & Deletion**:
   - Log in and go to `dashboard.php`.
   - Fill out the **Submit New Recipe** form and click **Publish Recipe**.
   - Verify the new recipe appears immediately on `dashboard.php` and on the home page catalog `index.php`.
   - Click the delete trash icon next to a recipe under "My Recipes" to remove it.

---

## 📜 License & Credits

Developed for the **ICT 2209 Web Technologies** course, Department of ICT, Faculty of Technology, Rajarata University of Sri Lanka.
