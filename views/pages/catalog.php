<?php
require __DIR__ . "/../fragments/head.php";
require __DIR__ . "/../fragments/navbar.php";

require __DIR__ . "/../fragments/stickers.php";
require __DIR__ . "/../fragments/product_section.php";
?>


<!doctype html>
<html lang="en">
    <?php head("Home", ['home.css']) ?>


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


        <!-- PRODUCTS -->
        <main class="catalog-products">
            <?php product_section("", $products); ?>
        </main>

    </div>
    <!-- Product Modal -->
        <div class="modal fade" id="productModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content bg-dark text-white">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalProductTitle">Product Details</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="modalBody">
                        <div class="text-center">
                            <div class="spinner-border text-light" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <script src="/js/sidebar.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/js/catalog.js"></script>
    <script src="/js/bookmark_button.js"></script>
    </body>
</html>
