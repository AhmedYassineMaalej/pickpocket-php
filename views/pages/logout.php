<?php require __DIR__ . "/../fragments/head.php"; ?>
<?php require __DIR__ . "/../fragments/navbar.php"; ?>
<?php require __DIR__ . "/../fragments/stickers.php"; ?>
<!DOCTYPE html>
<html lang="en">
    <?php head("Logout", "logout.css") ?>
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
        <div class="logout-card p-5">
            <div class="icon-large">🚪</div>
            
            <h2 class="fw-bold text-danger mb-3 title">Ready to Leave?</h2>
            <p class="text-muted mb-4">Are you sure you want to logout from PickPocket?</p>

            <!-- Logout Form -->
            <form action="/logout" method="POST">
                <input type="hidden" name="csrf" value="<?php echo $csrf_token ?? ''; ?>">
                
                <button type="submit" class="btn btn-danger w-100 py-2 fw-semibold mb-3">
                    Yes, Logout 🚪
                </button>
                
                <a href="/" class="btn btn-secondary w-100 py-2 fw-semibold text-decoration-none">
                    Cancel, Stay Here 🏠
                </a>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
