<?php
// auth/login.php - User Login Logic & Form
$page_title = "User Login";
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Redirect if already logged in
if (is_logged_in()) {
    header("Location: ../dashboard.php");
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email_username = trim($_POST['email_username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email_username)) {
        $errors['email_username'] = 'Email or username is required.';
    }

    if (empty($password)) {
        $errors['password'] = 'Password is required.';
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email OR username = :username LIMIT 1");
            $stmt->execute([':email' => $email_username, ':username' => $email_username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Success - start session & regenerate ID
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'] ?? 'Customer';

                set_flash('success', "Welcome back, " . sanitize($user['username']) . " (" . sanitize($user['role'] ?? 'User') . ")!");
                
                if (($user['role'] ?? '') === 'Chef') {
                    header("Location: ../dashboard.php");
                } else {
                    header("Location: ../customer_dashboard.php");
                }
                exit;
            } else {
                $errors['general'] = 'Invalid email/username or password.';
            }
        } catch (PDOException $e) {
            $errors['general'] = 'Database error: ' . $e->getMessage();
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
                    <div class="brand-icon fs-1 mb-2"><i class="bi bi-box-arrow-in-right"></i></div>
                    <h3 class="font-heading fw-bold mb-1">Welcome Back</h3>
                    <p class="text-light-50 small mb-0">Log in to manage your recipes</p>
                </div>
                <div class="card-body p-4 p-md-5">

                    <?php if (!empty($errors['general'])): ?>
                        <div class="alert alert-danger mb-4"><?php echo sanitize($errors['general']); ?></div>
                    <?php endif; ?>

                    <form id="loginForm" action="login.php" method="POST" novalidate>
                        <div class="mb-3">
                            <label for="email_username" class="form-label fw-medium">Email or Username <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-person-fill text-muted"></i></span>
                                <input type="text" class="form-control <?php echo isset($errors['email_username']) ? 'is-invalid-custom' : ''; ?>" id="email_username" name="email_username" placeholder="Username or email address" value="<?php echo sanitize($_POST['email_username'] ?? ''); ?>" required>
                            </div>
                            <?php if (isset($errors['email_username'])): ?>
                                <div class="invalid-feedback-custom d-block"><?php echo sanitize($errors['email_username']); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label fw-medium">Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-lock-fill text-muted"></i></span>
                                <input type="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid-custom' : ''; ?>" id="password" name="password" placeholder="Enter your password" required>
                            </div>
                            <?php if (isset($errors['password'])): ?>
                                <div class="invalid-feedback-custom d-block"><?php echo sanitize($errors['password']); ?></div>
                            <?php endif; ?>
                        </div>

                        <button type="submit" class="btn btn-accent rounded-pill w-100 py-2 fw-medium mb-3">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Log In
                        </button>

                        <div class="text-center">
                            <p class="small text-muted mb-0">Don't have an account? <a href="register.php" class="text-accent fw-bold text-decoration-none">Register Now</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
