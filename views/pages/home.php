<?php
use App\Repositories\ProductRepository;

require __DIR__ . "/../fragments/head.php"; ?>
<?php require __DIR__ . "/../fragments/navbar.php"; ?>
<?php require __DIR__ . "/../fragments/deal_of_the_day.php"; ?>
<?php require_once __DIR__ . "/../fragments/product_section.php"; ?>
<?php require __DIR__ . "/../fragments/stickers.php"; ?>

<!doctype html>
<html lang="en">
    <?php head("Home", css: ['home.css']) ?>
    <body>

        <?php stickers(); ?>
        <?php navbar(); ?>

        <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert" style="position: relative; z-index: 2; max-width: 600px; margin: 20px auto;">
            <strong>⚠️ Error!</strong> <?php echo htmlspecialchars($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        <?php deal_of_the_day() ?>
        <?php
        product_section('🌟 Best Deals', ProductRepository::getProductsWithMostOffers(8));
product_section('⏰ Expiring Deals', ProductRepository::getTopOffers(8), "py-5");
product_section('🆕 Newest Deals', ProductRepository::getNewestProducts(8), "py-5");
?>
    <script src="/js/catalog.js" defer></script>
    <script src="/js/bookmark_button.js" defer></script>
    </body>
</html>
