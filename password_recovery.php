<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $role = $_POST['role'] ?? '';

    try {
        $valid_roles = ['lecturer', 'admin'];
        if (!in_array($role, $valid_roles)) {
            throw new Exception('Invalid user role selected');
        }

        $table = ($role === 'lecturer') ? 'lecturers' : 'administrators';
        $stmt = $pdo->prepare("SELECT * FROM $table WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $success = "Password reset instructions have been sent to your email";
        } else {
            $error = "No account found with that email address";
        }

    } catch (Exception $e) {
        $error = "Error processing your request: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Password Recovery - University QA</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <!-- Favicon -->
    <link href="img/favicon.jpg" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="css/style.css" rel="stylesheet">
</head>

<body>
    <div class="container-xxl position-relative bg-white d-flex p-0">
        <!-- Spinner -->
        <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>

        <!-- Recovery Content -->
        <div class="container-fluid">
            <div class="row h-100 align-items-center justify-content-center" style="min-height: 100vh;">
                <div class="col-12 col-sm-8 col-md-6 col-lg-5 col-xl-4">
                    <div class="bg-light rounded p-4 p-sm-5 my-4 mx-3">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <a href="#" class="">
                                <h3 class="text-primary"><i class="fas fa-university me-2"></i>UNI QA</h3><h3>Password Recovery</h3>
                            </a>
                        </div>

                        <?php if ($error): ?>
                        <div class="alert alert-danger mb-3"><?= $error ?></div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                        <div class="alert alert-success mb-3"><?= $success ?></div>
                        <?php else: ?>
                        <form method="POST" id="recoveryForm">
                            <!-- Email Input -->
                            <div class="form-floating mb-3">
                                <input type="email" class="form-control" id="email" name="email" 
                                       placeholder="name@example.com" required>
                                <label for="email"><i class="fas fa-envelope me-2"></i>Email address</label>
                            </div>

                            <!-- Role Selection -->
                            <div class="form-floating mb-4">
                                <select class="form-select" id="role" name="role" required>
                                    <option value="">Select Role</option>
                                    <option value="lecturer">Lecturer</option>
                                    <option value="admin">Administrator</option>
                                </select>
                                <label for="role"><i class="fas fa-user-tag me-2"></i>Account Type</label>
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" class="btn btn-primary py-3 w-100 mb-4">
                                <i class="fas fa-paper-plane me-2"></i>Send Reset Instructions
                            </button>
                        </form>
                        <?php endif; ?>

                        <div class="text-center">
                            <a href="login.php" class="text-primary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Login
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/chart/chart.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="lib/tempusdominus/js/moment.min.js"></script>
    <script src="lib/tempusdominus/js/moment-timezone.min.js"></script>
    <script src="lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('recoveryForm');
        if (form) {
            form.addEventListener('submit', (e) => {
                const btn = form.querySelector('button[type="submit"]');
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Sending...';
                btn.disabled = true;
            });
        }
    });
    </script>
</body>
</html>