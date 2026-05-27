<?php require __DIR__ . "/../fragments/head.php"; ?>
<?php require __DIR__ . "/../fragments/navbar.php"; ?>
<?php require __DIR__ . "/../fragments/stickers.php"; ?>
<?php require __DIR__ . "/../fragments/product_section.php"; ?>



<!doctype html>
<html lang="en">
    <?php head("Pickpocket | Home", 'home.css') ?>
    <script src="js/bookmark_button.js"></script>
    <script src="js/catalog.js"></script>
<body class="bg-dark text-white">

<?php stickers() ?>
<?php navbar() ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>⚠️ Error!</strong> <?= htmlspecialchars($_SESSION['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>✅ Success!</strong> <?= htmlspecialchars($_SESSION['success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<div class="container-fluid">
    <div class="row">
        
        <nav class="col-md-3 col-lg-2 d-md-block sidebar shadow-sm p-4">
            <div class="position-sticky text-center pt-3">
                <div class="profile-avatar-wrapper mb-3 mx-auto d-flex align-items-center justify-content-center bg-secondary rounded-circle text-white">
                    👤
                </div>
                <h5 class="fw-bold text-white mb-4"><?= htmlspecialchars($username ?? 'User'); ?></h5>
                <hr class="text-white-50 mb-4">
                
                <div class="nav flex-column nav-pills text-start">
                    <a class="nav-link btn w-100 text-start py-3 mb-2 text-white <?= ($activeTab === 'dashboard') ? 'btn-coral active' : 'bg-transparent' ?>" href="/myspace">
                        <span class="me-2">🎯</span> Recommendations
                    </a>
                    
                    <a class="nav-link btn w-100 text-start py-3 mb-2 text-white <?= ($activeTab === 'bookmarks') ? 'btn-coral active' : 'bg-transparent' ?>" href="/myspace?tab=bookmarks">
                        <span class="me-2">❤️</span> Bookmarks
                    </a>
                    
                    <a class="nav-link btn w-100 text-start py-3 mb-2 text-white <?= ($activeTab === 'settings') ? 'btn-coral active' : 'bg-transparent' ?>" href="/myspace?tab=settings">
                        <span class="me-2">⚙️</span> Settings
                    </a>
                    
                    <a class="nav-link btn w-100 text-start py-3 mt-4 text-danger bg-transparent" href="/logout">
                        <span class="me-2">↪️</span> Logout
                    </a>
                </div>
            </div>
        </nav>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-4">
            <div class="content-limit-wrapper">
                
                <?php if ($activeTab === 'settings'): ?>
                    <section class="p-3">
                        <h3 class="fw-bold text-white mb-4">⚙️ Account Settings</h3><br>
                        <div class="card shadow border-0 p-4">
                            <form action="/myspace/update" method="POST" id="settingsForm">
                                <div class="mb-3">
                                    <label for="username" class="form-label fw-bold text-white">Username</label>
                                    <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($username ?? '') ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="old_password" class="form-label fw-bold text-white">Current Password</label>
                                    <input type="password" class="form-control" id="old_password" name="old_password" placeholder="Verify current password to commit changes" required>
                                </div>
                                <div class="mb-4">
                                    <label for="new_password" class="form-label fw-bold text-white">New Password</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Enter your new password choice" required>
                                </div>
                                <button type="submit" id="saveBtn" class="btn btn-coral w-100 py-2 fw-bold text-white" disabled>Save Changes 💾</button>
                            </form>
                        </div>
                    </section>

                    <script>
                        document.addEventListener("DOMContentLoaded", function () {
                            const oldPass = document.getElementById('old_password');
                            const newPass = document.getElementById('new_password');
                            const saveBtn = document.getElementById('saveBtn');

                            function toggleButtonState() {
                                if (oldPass.value.trim() === "" || newPass.value.trim() === "") {
                                    saveBtn.disabled = true;
                                } else {
                                    saveBtn.disabled = false;
                                }
                            }

                            oldPass.addEventListener('input', toggleButtonState);
                            newPass.addEventListener('input', toggleButtonState);
                        });
                    </script>
                    
                <?php elseif ($activeTab === 'bookmarks'): ?>
                    <section class="p-3">
                        <h3 class="fw-bold text-white mb-4">❤️ Saved Bookmarks</h3><br>
                        
                        <?php if (!empty($bookmarks)): ?>
                            <?php error_log("Rendering " . count($bookmarks) . " bookmarks"); ?>
                            <div class="products-container">
                                <?php foreach ($bookmarks as $product): ?>
                                    <?php product_card($product); ?>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state text-center p-4 rounded">
                                <p class="text-muted mb-0">Your bookmark catalog list is currently empty.</p>
                            </div>
                        <?php endif; ?>
                    </section>
                    
                <?php else: ?>
                    <section class="p-3">
                        <h3 class="section-title fw-bold text-white mb-4">🎯 Recommendations</h3><br>
                        
                        <div class="alert alert-info">
                            <strong>DEBUG:</strong> Dashboard tab active | RecommendedProducts count: <?= isset($recommendedProducts) ? count($recommendedProducts) : 0 ?>
                            <?php if (!empty($recommendedProducts)): ?>
                                <br>First product: <?= htmlspecialchars($recommendedProducts[0]->name) ?>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!empty($recommendedProducts)): ?>
                            <?php error_log("Rendering " . count($recommendedProducts) . " recommended products"); ?>
                            <div class="products-container">
                                <?php foreach ($recommendedProducts as $product): ?>
                                    <?php product_card($product); ?>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state text-center p-4 rounded">
                                <p class="text-muted mb-0">No recommendations found. Start browsing to get personalized recommendations!</p>
                            </div>
                        <?php endif; ?>
                    </section>
                <?php endif; ?>
                
            </div>
        </main>

    </div>
</div>

<!-- Product Modal -->
<div class="modal fade" id="productModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header border-secondary">
                <h5 class="modal-title" id="modalProductTitle">Product Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalBody">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-secondary">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

</body>
</html>