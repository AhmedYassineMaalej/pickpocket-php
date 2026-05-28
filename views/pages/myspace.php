<?php require __DIR__ . "/../fragments/head.php"; ?>
<?php require __DIR__ . "/../fragments/navbar.php"; ?>
<?php require __DIR__ . "/../fragments/stickers.php"; ?>
<?php require __DIR__ . "/../fragments/product_section.php"; ?>



<!doctype html>
<html lang="en">
    <?php head("Pickpocket | Home", 'myspace.css') ?>
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
            <main>
                <?php if ($activeTab === 'bookmarks'): ?>
                <h3 class="tab-title">❤️ Saved Bookmarks</h3><br>
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

            </main>
        </div>
    </body>
</html>
