<?php
// auth/profile.php - User Profile & Password Change Manager
$page_title = "My Profile";
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

require_login();
$user_data = current_user();

// Fetch full user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute([':id' => $user_data['id']]);
$user = $stmt->fetch();

$errors = [];

// Handle Profile Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (empty($username) || strlen($username) < 3) {
            $errors['username'] = 'Username must be at least 3 characters.';
        }
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Valid email address is required.';
        }

        if (empty($errors)) {
            try {
                // Check uniqueness excluding current user
                $chk = $pdo->prepare("SELECT id FROM users WHERE (username = :u OR email = :e) AND id != :id");
                $chk->execute([':u' => $username, ':e' => $email, ':id' => $user['id']]);
                if ($chk->fetch()) {
                    $errors['profile_general'] = 'Username or Email is already taken by another account.';
                } else {
                    $upd = $pdo->prepare("UPDATE users SET username = :u, email = :e WHERE id = :id");
                    $upd->execute([':u' => $username, ':e' => $email, ':id' => $user['id']]);
                    
                    $_SESSION['username'] = $username;
                    $_SESSION['user_email'] = $email;
                    set_flash('success', 'Profile details updated successfully!');
                    header("Location: profile.php");
                    exit;
                }
            } catch (PDOException $e) {
                $errors['profile_general'] = 'Failed to update profile: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'change_password') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (!password_verify($current_password, $user['password'])) {
            $errors['current_password'] = 'Incorrect current password.';
        }

        if (empty($new_password) || strlen($new_password) < 6) {
            $errors['new_password'] = 'New password must be at least 6 characters long.';
        }

        if ($new_password !== $confirm_password) {
            $errors['confirm_password'] = 'New passwords do not match.';
        }

        if (empty($errors)) {
            try {
                $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                $upd = $pdo->prepare("UPDATE users SET password = :p WHERE id = :id");
                $upd->execute([':p' => $hashed, ':id' => $user['id']]);
                set_flash('success', 'Password updated successfully!');
                header("Location: profile.php");
                exit;
            } catch (PDOException $e) {
                $errors['pass_general'] = 'Failed to change password: ' . $e->getMessage();
            }
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container mt-4 mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            
            <div class="bg-dark text-white p-4 p-md-5 rounded-4 shadow-sm mb-4 d-flex align-items-center gap-4">
                <div class="bg-accent rounded-circle p-3 fs-1 text-white d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                    <i class="bi bi-person-fill"></i>
                </div>
                <div>
                    <h2 class="font-heading fw-bold mb-1"><?php echo sanitize($user['username']); ?></h2>
                    <p class="text-light-50 mb-0"><i class="bi bi-envelope me-1"></i><?php echo sanitize($user['email']); ?></p>
                    <small class="text-secondary"><i class="bi bi-calendar-check me-1"></i>Member since <?php echo date('F Y', strtotime($user['created_at'])); ?></small>
                </div>
            </div>

            <div class="row g-4">
                <!-- Update Account Details -->
                <div class="col-md-6">
                    <div class="bg-white p-4 rounded-4 shadow-sm border h-100">
                        <h4 class="font-heading fw-bold text-dark mb-3"><i class="bi bi-person-lines-fill text-accent me-2"></i>Account Details</h4>
                        
                        <?php if (!empty($errors['profile_general'])): ?>
                            <div class="alert alert-danger"><?php echo sanitize($errors['profile_general']); ?></div>
                        <?php endif; ?>

                        <form action="profile.php" method="POST">
                            <input type="hidden" name="action" value="update_profile">
                            
                            <div class="mb-3">
                                <label for="username" class="form-label fw-medium">Username</label>
                                <input type="text" class="form-control <?php echo isset($errors['username']) ? 'is-invalid-custom' : ''; ?>" id="username" name="username" value="<?php echo sanitize($_POST['username'] ?? $user['username']); ?>" required>
                                <?php if (isset($errors['username'])): ?>
                                    <div class="invalid-feedback-custom d-block"><?php echo sanitize($errors['username']); ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-4">
                                <label for="email" class="form-label fw-medium">Email Address</label>
                                <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid-custom' : ''; ?>" id="email" name="email" value="<?php echo sanitize($_POST['email'] ?? $user['email']); ?>" required>
                                <?php if (isset($errors['email'])): ?>
                                    <div class="invalid-feedback-custom d-block"><?php echo sanitize($errors['email']); ?></div>
                                <?php endif; ?>
                            </div>

                            <button type="submit" class="btn btn-accent rounded-pill w-100 fw-medium">Save Changes</button>
                        </form>
                    </div>
                </div>

                <!-- Change Password -->
                <div class="col-md-6">
                    <div class="bg-white p-4 rounded-4 shadow-sm border h-100">
                        <h4 class="font-heading fw-bold text-dark mb-3"><i class="bi bi-shield-lock-fill text-accent me-2"></i>Change Password</h4>

                        <?php if (!empty($errors['pass_general'])): ?>
                            <div class="alert alert-danger"><?php echo sanitize($errors['pass_general']); ?></div>
                        <?php endif; ?>

                        <form action="profile.php" method="POST">
                            <input type="hidden" name="action" value="change_password">

                            <div class="mb-3">
                                <label for="current_password" class="form-label fw-medium">Current Password</label>
                                <input type="password" class="form-control <?php echo isset($errors['current_password']) ? 'is-invalid-custom' : ''; ?>" id="current_password" name="current_password" required>
                                <?php if (isset($errors['current_password'])): ?>
                                    <div class="invalid-feedback-custom d-block"><?php echo sanitize($errors['current_password']); ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label for="new_password" class="form-label fw-medium">New Password</label>
                                <input type="password" class="form-control <?php echo isset($errors['new_password']) ? 'is-invalid-custom' : ''; ?>" id="new_password" name="new_password" required>
                                <?php if (isset($errors['new_password'])): ?>
                                    <div class="invalid-feedback-custom d-block"><?php echo sanitize($errors['new_password']); ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-4">
                                <label for="confirm_password" class="form-label fw-medium">Confirm New Password</label>
                                <input type="password" class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid-custom' : ''; ?>" id="confirm_password" name="confirm_password" required>
                                <?php if (isset($errors['confirm_password'])): ?>
                                    <div class="invalid-feedback-custom d-block"><?php echo sanitize($errors['confirm_password']); ?></div>
                                <?php endif; ?>
                            </div>

                            <button type="submit" class="btn btn-outline-dark rounded-pill w-100 fw-medium">Update Password</button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
