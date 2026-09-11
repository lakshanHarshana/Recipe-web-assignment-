<?php
// contact.php - Contact Form Page & Processing
$page_title = "Contact Us";
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$errors = [];
$success_message = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? 'General Inquiry');
    $message = trim($_POST['message'] ?? '');

    // Server-Side Validation
    if (empty($name) || strlen($name) < 2) {
        $errors['name'] = 'Name is required and must be at least 2 characters.';
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'A valid email address is required.';
    }

    if (empty($subject)) {
        $errors['subject'] = 'Subject is required.';
    }

    if (empty($message) || strlen($message) < 10) {
        $errors['message'] = 'Message must be at least 10 characters long.';
    }

    // Insert into MySQL Database if validation passes
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO messages (name, email, subject, message) VALUES (:name, :email, :subject, :message)");
            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':subject' => $subject,
                ':message' => $message
            ]);
            set_flash('success', 'Thank you! Your message has been received. Our culinary support team will respond shortly.');
            header("Location: contact.php");
            exit;
        } catch (PDOException $e) {
            $errors['general'] = 'Failed to submit message. Please try again later. Error: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container mt-4 mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            
            <div class="text-center mb-5">
                <span class="badge bg-accent px-3 py-2 text-uppercase fs-6 mb-2">Get in Touch</span>
                <h1 class="font-heading display-5 fw-bold text-dark">We'd Love to Hear From You</h1>
                <p class="lead text-muted max-w-600 mx-auto">Have questions about a recipe, feedback for our culinary team, or business inquiries? Drop us a message below.</p>
            </div>

            <div class="row g-4">
                <!-- Contact Info Sidebar -->
                <div class="col-md-4">
                    <div class="bg-dark text-white p-4 rounded-4 h-100 shadow d-flex flex-column justify-content-between">
                        <div>
                            <h4 class="font-heading text-accent fw-bold mb-4"><i class="bi bi-info-circle me-2"></i>Contact Details</h4>
                            
                            <div class="d-flex align-items-start mb-4">
                                <div class="bg-accent rounded-circle p-2 me-3 fs-5 text-white">
                                    <i class="bi bi-geo-alt"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Location</h6>
                                    <p class="small text-secondary mb-0">Department of ICT, Faculty of Technology,<br>Rajarata University of Sri Lanka.</p>
                                </div>
                            </div>

                            <div class="d-flex align-items-start mb-4">
                                <div class="bg-accent rounded-circle p-2 me-3 fs-5 text-white">
                                    <i class="bi bi-envelope-open"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Email Support</h6>
                                    <p class="small text-secondary mb-0">support@flavorcraft.lk<br>info@rajarata.ac.lk</p>
                                </div>
                            </div>

                            <div class="d-flex align-items-start mb-4">
                                <div class="bg-accent rounded-circle p-2 me-3 fs-5 text-white">
                                    <i class="bi bi-telephone"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Phone Line</h6>
                                    <p class="small text-secondary mb-0">+94 25 226 6600</p>
                                </div>
                            </div>
                        </div>

                        <div class="border-top border-secondary pt-3">
                            <small class="text-secondary d-block mb-2">Connect on Socials</small>
                            <div class="d-flex gap-3 fs-5">
                                <a href="#" class="text-white text-hover-accent"><i class="bi bi-facebook"></i></a>
                                <a href="#" class="text-white text-hover-accent"><i class="bi bi-instagram"></i></a>
                                <a href="#" class="text-white text-hover-accent"><i class="bi bi-twitter-x"></i></a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Form Card -->
                <div class="col-md-8">
                    <div class="bg-white p-4 p-md-5 rounded-4 shadow-sm border">
                        
                        <?php if (!empty($errors['general'])): ?>
                            <div class="alert alert-danger mb-4"><?php echo sanitize($errors['general']); ?></div>
                        <?php endif; ?>

                        <form id="contactForm" action="contact.php" method="POST" novalidate>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label fw-medium">Your Name <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-person text-muted"></i></span>
                                        <input type="text" class="form-control <?php echo isset($errors['name']) ? 'is-invalid-custom' : ''; ?>" id="name" name="name" placeholder="John Doe" value="<?php echo sanitize($_POST['name'] ?? ''); ?>" required>
                                    </div>
                                    <?php if (isset($errors['name'])): ?>
                                        <div class="invalid-feedback-custom d-block"><?php echo sanitize($errors['name']); ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="col-md-6">
                                    <label for="email" class="form-label fw-medium">Your Email <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-envelope text-muted"></i></span>
                                        <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid-custom' : ''; ?>" id="email" name="email" placeholder="john@example.com" value="<?php echo sanitize($_POST['email'] ?? ''); ?>" required>
                                    </div>
                                    <?php if (isset($errors['email'])): ?>
                                        <div class="invalid-feedback-custom d-block"><?php echo sanitize($errors['email']); ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="col-12">
                                    <label for="subject" class="form-label fw-medium">Subject <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-tag text-muted"></i></span>
                                        <input type="text" class="form-control <?php echo isset($errors['subject']) ? 'is-invalid-custom' : ''; ?>" id="subject" name="subject" placeholder="e.g. Recipe Question, Feedback, Partnership" value="<?php echo sanitize($_POST['subject'] ?? ''); ?>" required>
                                    </div>
                                    <?php if (isset($errors['subject'])): ?>
                                        <div class="invalid-feedback-custom d-block"><?php echo sanitize($errors['subject']); ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="col-12">
                                    <label for="message" class="form-label fw-medium">Message <span class="text-danger">*</span></label>
                                    <textarea class="form-control <?php echo isset($errors['message']) ? 'is-invalid-custom' : ''; ?>" id="message" name="message" rows="5" placeholder="Write your message or inquiry here..." required><?php echo sanitize($_POST['message'] ?? ''); ?></textarea>
                                    <?php if (isset($errors['message'])): ?>
                                        <div class="invalid-feedback-custom d-block"><?php echo sanitize($errors['message']); ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-accent rounded-pill px-5 py-2 fw-medium w-100 w-md-auto">
                                        <i class="bi bi-send-fill me-2"></i>Send Message
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
