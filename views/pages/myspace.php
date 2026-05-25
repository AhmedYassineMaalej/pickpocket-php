<?php require __DIR__ . "/../fragments/head.php"; ?>
<?php require __DIR__ . "/../fragments/navbar.php"; ?>
<?php require __DIR__ . "/../fragments/stickers.php"; ?>
<!doctype html>
<html lang="en">
    <?php head("My Space", "myspace.css", "/css/cart.css") ?>
<body class="bg-dark text-white">

<?php stickers() ?>

<?php navbar() ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert" style="position: fixed; top: 70px; right: 20px; z-index: 1050; max-width: 400px;">
        <strong>⚠️ Error!</strong> <?= htmlspecialchars($_SESSION['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert" style="position: fixed; top: 70px; right: 20px; z-index: 1050; max-width: 400px;">
        <strong>✅ Success!</strong> <?= htmlspecialchars($_SESSION['success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php $activeTab = $currentTab ?? 'dashboard'; ?>

<div class="container-fluid">
    <div class="row">
        
        <nav class="col-md-3 col-lg-2 d-md-block sidebar shadow-sm p-4" style="background: rgba(255,255,255,0.05); backdrop-filter: blur(10px); border-right: 1px solid rgba(255,255,255,0.1);">
            <div class="position-sticky text-center pt-3">
                
                <div class="profile-avatar-wrapper mb-3 mx-auto d-flex align-items-center justify-content-center bg-secondary rounded-circle text-white" style="width: 100px; height: 100px; font-size: 2.5rem;">
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
                        <div class="card shadow border-0 p-4" style="max-width: 600px;">
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
                            <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
                                <?php foreach ($bookmarks as $item): ?>
                                    <div class="col" id="bookmark-card-<?= htmlspecialchars($item['id']) ?>">
                                        <div class="card h-100 shadow border-0 text-white" style="background: rgba(255,255,255,0.05); backdrop-filter: blur(10px);">
                                            <div class="card-body text-center p-4 d-flex flex-column justify-content-between">
                                                
                                                <div>
                                                    <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" style="height: 150px; object-fit: contain; margin-bottom: 15px;">
                                                    <h5 class="card-title fw-bold text-white"><?= htmlspecialchars($item['name']) ?></h5>
                                                    <p class="text-coral fw-bold mb-3"><?= htmlspecialchars($item['price']) ?> TND</p>
                                                </div>

                                                <div class="d-flex align-items-center gap-2 mt-3">
                                                    <button class="btn btn-primary flex-grow-1 text-white fw-bold py-2" onclick="window.location.href='/catalog?id=<?= urlencode($item['id']) ?>'">
                                                        View Product 🚀
                                                    </button>
                                                    
                                                    <button class="btn btn-link p-0 d-flex align-items-center justify-content-center bookmark-toggle-btn" 
                                                            onclick="deleteBookmarkFromDb(this, <?= intval($item['id']) ?>)"
                                                            style="width: 42px; height: 42px; background: rgba(255,255,255,0.08); border-radius: 8px; border: none; transition: background 0.2s;">
                                                        <img src="/bookmark-full.svg" alt="Bookmarked" class="bookmark-icon" style="width: 24px; height: 24px;">
                                                    </button>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="p-4 rounded text-center" style="background: rgba(140, 140, 140, 0.59); border: 1px dashed rgba(255,255,255,0.2);">
                                <p class="text-muted mb-0">Your bookmark catalog list is currently empty.</p>
                            </div>
                        <?php endif; ?>
                    </section>

                    <script>
                    function deleteBookmarkFromDb(buttonElement, bookmarkId) {
                        const iconImg = buttonElement.querySelector('.bookmark-icon');
                        
                        // Instantly toggle the local SVG image to the empty state for snappy feedback
                        if (iconImg) {
                            iconImg.src = "/bookmark-empty.svg";
                        }

                        fetch('/bookmarks/remove', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({ bookmarks_item_id: bookmarkId })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                const targetCard = document.getElementById(`bookmark-card-${bookmarkId}`);
                                if (targetCard) {
                                    targetCard.style.transition = 'all 0.35s cubic-bezier(0.4, 0, 0.2, 1)';
                                    targetCard.style.opacity = '0';
                                    targetCard.style.transform = 'scale(0.85) translateY(5px)';
                                    
                                    setTimeout(() => {
                                        targetCard.remove();
                                        if (document.querySelectorAll('[id^="bookmark-card-"]').length === 0) {
                                            window.location.reload();
                                        }
                                    }, 350);
                                }
                            } else {
                                // Fallback image state back to full if the backend controller operation drops out
                                if (iconImg) iconImg.src = "/bookmark-full.svg";
                                alert('Error: ' + (data.error || 'Failed to clean entry record.'));
                            }
                        })
                        .catch(err => {
                            console.error('Network sync failure:', err);
                            if (iconImg) iconImg.src = "/bookmark-full.svg";
                        });
                    }
                    </script>
                    
                <?php else: ?>
                    <section class="p-3">
                        <h3 class="section-title fw-bold text-white mb-4">🎯 Recommendations</h3><br>
                        <?php if (!empty($recommendedProducts)): ?>
                            <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
                                <?php foreach ($recommendedProducts as $product): ?>
                                    <div class="col">
                                        <div class="card h-100 shadow border-0 product-card text-white">
                                            <div class="card-body text-center p-4">
                                                <img src="<?= htmlspecialchars($product->image) ?>" alt="<?= htmlspecialchars($product->name) ?>" style="height: 150px; object-fit: contain; margin-bottom: 15px;">
                                                <h5 class="card-title fw-bold text-white"><?= htmlspecialchars($product->name) ?></h5>
                                                <p class="text-light small opacity-75">Ref: <?= htmlspecialchars($product->reference) ?></p>
                                                <button class="btn btn-coral w-100 text-white fw-bold" onclick="window.location.href='/catalog?ref=<?= urlencode($product->reference) ?>'">View Deals 🚀</button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </section>
                <?php endif; ?>
                
            </div>
        </main>

    </div>
</div>

</body>
</html>