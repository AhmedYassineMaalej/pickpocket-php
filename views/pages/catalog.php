<?php 
require __DIR__ . "/../fragments/head.php"; 
require __DIR__ . "/../fragments/navbar.php";

require __DIR__ . "/../fragments/stickers.php";
require __DIR__ . "/../fragments/sidebar.php";
?>


<!doctype html>
<html lang="en">
    <?php head("Pickpocket | Home", 'home.css') ?>


    <body>

    <?php stickers(); ?>

        <?php navbar() ?>



        <header class="catalog-header text-white text-center py-5">
            <div class="container">
                <h1 class="display-4 fw-bold">📦 Product Catalog</h1>
                <p class="lead mb-0">Compare prices and choose where to buy the best deals!</p>
            </div>
        </header>

    <div class="catalog-layout">

        <!-- SIDEBAR -->
        <?php sidebar(); ?>

        <!-- PRODUCTS -->
        <main class="catalog-products">
            <?php product_section("", $products); ?>
        </main>

    </div>
        <?php require __DIR__ . "/../fragments/productModal.php"; ?>

    <script src="/js/sidebar.js"></script>

    </body>
</html>
