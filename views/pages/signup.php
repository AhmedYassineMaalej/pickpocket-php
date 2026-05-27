<?php require __DIR__ . "/../fragments/head.php"; ?>
<?php require __DIR__ . "/../fragments/navbar.php"; ?>
<?php require __DIR__ . "/../fragments/stickers.php"; ?>

<!doctype html>
<html lang="en">
    <?php head("Sign Up", "signup.css") ?>
    <body>

        <?php stickers(); ?>
        <?php navbar() ?>

        <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert" style="position: relative; z-index: 2; max-width: 600px; margin: 20px auto;">
            <strong>⚠️ Error!</strong> <?php echo htmlspecialchars($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        <div class="container d-flex align-items-center justify-content-center" style="min-height: 90vh;">
            <div class="col-md-6 col-lg-5">

                <div class="signup-card p-4">

                    <div class="text-center mb-4">
                        <h2 class="fw-bold text-primary title">Create Account</h2>
                        <p class="text-muted">Join PickPocket and hunt the best deals 💰</p>
                    </div>

                    <!-- error -->
                    <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?php 
                        $error = $_GET['error'];
                        $message = '';
                        switch($error) {
                        case 'empty_fields': $message = 'Please fill in all fields.'; break;
                        case 'password_mismatch': $message = 'Passwords do not match.'; break;
                        case 'user_exists': $message = 'Username already exists.'; break;
                        case 'db_error': $message = 'Database error.'; break;
                        case 'invalid_csrf': $message = 'Invalid security token.'; break;
                        default: $message = 'Something went wrong.';
                        }
                        echo htmlspecialchars($message);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>

                    <!-- form -->
                    <form method="POST" action="/signup">

                        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf_token) ?>">

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Username</label>
                            <input type="text" class="form-control"
                                name="username"
                                value="<?= htmlspecialchars($_GET['username'] ?? '') ?>"
                                placeholder="type your username"
                                required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Password</label>
                            <input type="password" class="form-control"
                                name="password"
                                placeholder="password"
                                required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Confirm Password</label>
                            <input type="password" class="form-control"
                                name="confirm_password"
                                placeholder="confirm your password"
                                required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                            Create Account
                        </button>

                    </form>

                    <div class="text-center mt-4">
                        <p class="mb-0 text-muted">
                            Already have an account?
                            <a href="/login" class="fw-semibold text-decoration-none">Login</a>
                        </p>
                    </div>

                </div>

            </div>
        </div>
    </body>
</html>
