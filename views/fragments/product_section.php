<?php
require __DIR__.'/product_card.php';

function product_section(string $title, array $products, string $bg = '')
{
    ?>
<section class="<?php echo $bg; ?>">
    <div class="container mb-5">
        <h1 class="mb-4"><?php echo $title; ?></h1>
        <div class="products-container">
            <?php foreach ($products as $product) { ?>
            <?php product_card($product); ?>
            <?php } ?>
        </div>
    </div>
</section>
<?php } ?>

