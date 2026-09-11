<?php
// auth/register.php - User Registration Logic & Form
$page_title = "Register Account";
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Redirect if already logged in
if (is_logged_in()) {
    header("Location: ../dashboard.php");
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token()) {
        $errors['general'] = 'Invalid or expired security token. Please try submitting the form again.';
    }

    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Backend Validation
    if (empty($username) || strlen($username) < 3) {
        $errors['username'] = 'Username must be at least 3 characters.';
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'A valid email address is required.';
    }

    if (empty($password) || strlen($password) < 6) {
        $errors['password'] = 'Password must be at least 6 characters long.';
    }

    if ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }

    // Check if username or email already exists
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username OR email = :email");
            $stmt->execute([':username' => $username, ':email' => $email]);
            if ($stmt->fetch()) {
                $errors['general'] = 'Username or email address is already registered.';
            }
        } catch (PDOException $e) {
            $errors['general'] = handle_db_error($e, 'Failed to verify existing user account details.');
        }
    }

    $role = trim($_POST['role'] ?? 'Customer');
    if (!in_array($role, ['Chef', 'Customer'])) {
        $role = 'Customer';
    }

    // Insert user into database
    if (empty($errors)) {
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (:username, :email, :password, :role)");
            $stmt->execute([
                ':username' => $username,
                ':email' => $email,
                ':password' => $hashed_password,
                ':role' => $role
            ]);

            set_flash('success', 'Account registered successfully as a ' . sanitize($role) . '! Please log in.');
            header("Location: login.php");
            exit;
        } catch (PDOException $e) {
            $errors['general'] = handle_db_error($e, 'Failed to register account.');
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-header bg-dark text-white text-center p-4">
                    <div class="brand-icon fs-1 mb-2"><i class="bi bi-person-plus-fill"></i></div>
                    <h3 class="font-heading fw-bold mb-1">Create Account</h3>
                    <p class="text-light-50 small mb-0">Join FlavorCraft to share & manage recipes</p>
                </div>
                <div class="card-body p-4 p-md-5">

                    <?php if (!empty($errors['general'])): ?>
                        <div class="alert alert-danger mb-4"><?php echo sanitize($errors['general']); ?></div>
                    <?php endif; ?>

                    <form id="registerForm" action="register.php" method="POST" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        <div class="mb-3">
                            <label for="role" class="form-label fw-medium">Register As: <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-person-badge text-muted"></i></span>
                                <select class="form-select" id="role" name="role" required>
                                    <option value="Customer" <?php echo (($_POST['role'] ?? '') === 'Customer') ? 'selected' : ''; ?>>🍽️ Customer / Food Lover (Browse, Rate & Save)</option>
                                    <option value="Chef" <?php echo (($_POST['role'] ?? '') === 'Chef') ? 'selected' : ''; ?>>👨‍🍳 Chef (Publish & Manage Recipes)</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="username" class="form-label fw-medium">Username <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-person text-muted"></i></span>
                                <input type="text" class="form-control <?php echo isset($errors['username']) ? 'is-invalid-custom' : ''; ?>" id="username" name="username" placeholder="e.g. chef_john" value="<?php echo sanitize($_POST['username'] ?? ''); ?>" required>
                            </div>
                            <?php if (isset($errors['username'])): ?>
                                <div class="invalid-feedback-custom d-block"><?php echo sanitize($errors['username']); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label fw-medium">Email Address <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-envelope text-muted"></i></span>
                                <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid-custom' : ''; ?>" id="email" name="email" placeholder="john@example.com" value="<?php echo sanitize($_POST['email'] ?? ''); ?>" required>
                            </div>
                            <?php if (isset($errors['email'])): ?>
                                <div class="invalid-feedback-custom d-block"><?php echo sanitize($errors['email']); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label fw-medium">Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-lock text-muted"></i></span>
                                <input type="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid-custom' : ''; ?>" id="password" name="password" placeholder="At least 6 characters" required>
                            </div>
                            <?php if (isset($errors['password'])): ?>
                                <div class="invalid-feedback-custom d-block"><?php echo sanitize($errors['password']); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-4">
                            <label for="confirm_password" class="form-label fw-medium">Confirm Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-lock-fill text-muted"></i></span>
                                <input type="password" class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid-custom' : ''; ?>" id="confirm_password" name="confirm_password" placeholder="Repeat password" required>
                            </div>
                            <?php if (isset($errors['confirm_password'])): ?>
                                <div class="invalid-feedback-custom d-block"><?php echo sanitize($errors['confirm_password']); ?></div>
                            <?php endif; ?>
                        </div>

                        <button type="submit" class="btn btn-accent rounded-pill w-100 py-2 fw-medium mb-3">
                            <i class="bi bi-check-circle-fill me-2"></i>Sign Up
                        </button>

                        <div class="text-center">
                            <p class="small text-muted mb-0">Already have an account? <a href="login.php" class="text-accent fw-bold text-decoration-none">Log In Here</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
