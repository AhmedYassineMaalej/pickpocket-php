<?php require __DIR__ . "/../fragments/head.php"; ?>
<?php require __DIR__ . "/../fragments/navbar.php"; ?>

<!doctype html>
<html lang="en">
    <?php head("My Space", "myspace.css", "/css/cart.css") ?>
<body class="bg-dark text-white">

<!-- Floating Stickers/Coins Animation -->
<div class="stickers-container">
    <div class="sticker">🪙</div>
    <div class="sticker">💰</div>
    <div class="sticker">💵</div>
    <div class="sticker">💸</div>
    <div class="sticker">💰</div>
    <div class="sticker">💵</div>
    <div class="sticker">🪙</div>
    <div class="sticker">💸</div>
    <div class="sticker">🪙</div>
    <div class="sticker">💰</div>
    <div class="sticker">💵</div>
    <div class="sticker">🪙</div>
    <div class="sticker">💸</div>
    <div class="sticker">🪙</div>
    <div class="sticker">💰</div>
</div>

<?php navbar() ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show fixed-toast-alert" role="alert">
        <strong>⚠️ Error!</strong> <?= htmlspecialchars($_SESSION['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show fixed-toast-alert" role="alert">
        <strong>✅ Success!</strong> <?= htmlspecialchars($_SESSION['success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php $activeTab = $currentTab ?? 'dashboard'; ?>

<div class="container-fluid">
    <div class="row">    
        <nav class="col-md-3 col-lg-2 d-md-block sidebar shadow-sm p-4 sidebar-glass">
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
                        <div class="card shadow border-0 p-4 settings-card-wrapper">
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
                    
                <?php elseif ($activeTab === 'bookmarks'): ?>
                    <section class="p-3">
                        <h3 class="fw-bold text-white mb-4">❤️ Saved Bookmarks</h3><br>
                        <?php if (!empty($bookmarks)): ?>
                            <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
                                <?php foreach ($bookmarks as $item): ?>
                                    <div class="col" id="bookmark-card-<?= htmlspecialchars($item['id']) ?>">
                                        <div class="card h-100 shadow border-0 text-white">
                                            <div class="card-body text-center p-4 d-flex flex-column justify-content-between">
                                                
                                                <div>
                                                    <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="product-display-img">
                                                    <h5 class="card-title fw-bold text-white"><?= htmlspecialchars($item['name']) ?></h5>
                                                    <p class="text-coral fw-bold mb-3"><?= htmlspecialchars($item['price']) ?> TND</p>
                                                </div>

                                                <div class="d-flex align-items-center gap-2 mt-3">
                                                    <button class="btn btn-primary flex-grow-1 text-white fw-bold py-2" onclick="window.location.href='/catalog?id=<?= urlencode($item['id']) ?>'">
                                                        View Product 🚀
                                                    </button>
                                                    
                                                    <button class="btn btn-link p-0 d-flex align-items-center justify-content-center bookmark-toggle-btn" 
                                                            onclick="deleteBookmarkFromDb(this, <?= intval($item['id']) ?>)">
                                                        <img src="/bookmark-full.svg" alt="Bookmarked" class="bookmark-icon" style="width: 24px; height: 24px;">
                                                    </button>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="p-4 rounded text-center empty-catalog-fallback">
                                <p class="text-muted mb-0">Your bookmark catalog list is currently empty.</p>
                            </div>
                        <?php endif; ?>
                    </section>
                    
                <?php else: ?>
                    <section class="p-3">
                        <h3 class="section-title fw-bold text-white mb-4">🎯 Recommendations</h3><br>
                        <?php if (!empty($recommendedProducts)): ?>
                            <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
                                <?php foreach ($recommendedProducts as $product): ?>
                                    <div class="col">
                                        <div class="card h-100 shadow border-0 product-card text-white">
                                            <div class="card-body text-center p-4">
                                                <img src="<?= htmlspecialchars($product->image) ?>" alt="<?= htmlspecialchars($product->name) ?>" class="product-display-img">
                                                <h5 class="card-title fw-bold text-white"><?= htmlspecialchars($product->name) ?></h5>
                                                <p class="text-light small opacity-75">Ref: <?= htmlspecialchars($product->reference) ?></p>
                                                <button class="btn btn-coral w-100 text-white fw-bold" onclick="window.location.href='/catalog?ref=<?= urlencode($product->reference) ?>'">View Deals 🚀</button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="p-4 rounded text-center empty-catalog-fallback">
                                <p class="text-muted mb-0">No custom recommendations are available for your profile right now.</p>
                            </div>
                        <?php endif; ?>
                    </section>
                <?php endif; ?>
                
            </div>
        </main>

    </div>
</div>

<script src="/js/myspace.js"></script>
</body>
</html>