<?php require __DIR__ . "/../fragments/head.php"; ?>
<?php require __DIR__ . "/../fragments/navbar.php"; ?>
<?php require __DIR__ . "/../fragments/stickers.php"; ?>
<?php require __DIR__ . "/../fragments/product_section.php"; ?>



<!doctype html>
<html lang="en">
    <?php head("Pickpocket | Home", ['home.css', 'myspace.css']) ?>
    <body class="bg-dark text-white">

        <?php navbar(); ?>

        <div id="container">
            <div id="sidebar">
                <nav>
                    <p class="welcome-msg">Welcome <?=$username?>!</p>
                    <a class="sidebar-item" href="/myspace"> Recommendations </a>
                    <a class="sidebar-item" href="/myspace?tab=bookmarks"> Bookmarks </a>
                    <a class="sidebar-item" href="/logout"> Logout </a>
                </nav>
            </div>
            <main class="catalog-products">
    <?php if ($activeTab === 'bookmarks'): ?>
        <h3 class="tab-title">❤️ Saved Bookmarks</h3>
        <?php if (!empty($bookmarks)): ?>
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
    <?php else: ?>
        <h3 class="tab-title">🎯 Recommendations</h3>
        <?php if (!empty($recommendedProducts)): ?>
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
    <?php endif; ?>
</main>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="/js/bookmark_button.js"></script>
</body>
</html>
