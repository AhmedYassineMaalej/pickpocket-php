<?php

use App\Entities\Product;
use App\Helpers\JWT;
use App\Repositories\BookmarkRepository;
use App\Repositories\ProductRepository;

function product_card($product)
{
    $minPrice = ProductRepository::getMinPriceForProduct($product->id);

    if (JWT::isLoggedIn()) {
        $userID = JWT::getUserId();
        $isBookmarked = BookmarkRepository::isProductBookmarked($userID, $product->id);
    } else {
        $isBookmarked = false;
    }

    $bookmark_css_class = $isBookmarked ? 'bookmark-full' : '';

    ?>
    <div class="product-card">
        <img src="<?php echo htmlspecialchars($product->image); ?>" class="product-img" alt="product image">
        <h5 class="product-title fw-bold"><?php echo htmlspecialchars($product->name); ?></h5>
        <p class="product-reference text-muted">Ref: <?php echo htmlspecialchars($product->reference); ?></p>
        <p class="product-price text-success fs-5 fw-bold">Starting at <?php echo number_format($minPrice, 2); ?> TND</p>

            <button class ="btn btn-primary flex-grow-1" onclick="event.stopPropagation();showProductModal(<?= $product->id?>)">view details</button>
        <button class="product-bookmark-btn <?= $bookmark_css_class; ?>"
                onclick="event.stopPropagation(); toggleBookmark('<?= htmlspecialchars($product->reference) ?>', this)">
            <img src="/<?= $isBookmarked ? 'bookmark-full.svg' : 'bookmark-empty.svg' ?>">
        </button>
    </div>
    <script src="/js/catalog.js"></script>
<?php } ?>

