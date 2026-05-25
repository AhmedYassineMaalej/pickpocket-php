<?php require __DIR__ . "/../fragments/head.php"; ?>
<?php require __DIR__ . "/../fragments/navbar.php"; ?>
<?php require __DIR__ . "/../fragments/stickers.php"; ?>

<!DOCTYPE html>
<html lang="en">
    <?php head("Login", 'login.css'); ?>
<body>

<?php navbar(); ?>

<?php stickers()?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert" style="position: relative; z-index: 2; max-width: 600px; margin: 20px auto;">
        <strong>⚠️ Error!</strong> <?php echo htmlspecialchars($_SESSION['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div class="container d-flex align-items-center justify-content-center" style="min-height: 90vh;">
    <div class="col-md-6 col-lg-5">
        <div class="login-card p-4">
            <div class="text-center mb-4">
                <h2 class="fw-bold text-primary title">Welcome Back! 👋</h2>
                <p class="text-muted">Login to continue hunting the best deals 💰</p>
            </div>

            <!-- Error Messages -->
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?php 
                        $error = $_GET['error'];
                        $message = '';
                        switch($error) {
                            case 'invalid_credentials': $message = 'Wrong username or password. Please try again.'; break;
                            case 'missing_fields': $message = 'Please fill in all fields.'; break;
                            case 'invalid_csrf': $message = 'Invalid security token. Please try again.'; break;
                            default: $message = 'Something went wrong. Please try again.';
                        }
                        echo htmlspecialchars($message);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form action="/login" method="POST">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Username</label>
                    <input type="text" 
                           class="form-control" 
                           id="username" 
                           name="username" 
                           placeholder="Enter your username"
                           value="<?= htmlspecialchars($_GET['username'] ?? '') ?>"
                           required>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Password</label>
                    <input type="password" 
                           class="form-control" 
                           id="password" 
                           name="password" 
                           placeholder="Enter your password"
                           required>
                    <input type="hidden" name="csrf" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                    Login 🚀
                </button>
            </form>

            <div class="text-center mt-4">
                <p class="mb-0 text-muted">
                    Don't have an account? 
                    <a href="/signup" class="fw-semibold text-decoration-none">Sign Up</a>
                </p>
            </div>
        </div>
    </div>
</div>

</body>
</html>
