<?php function catalog_button($categories) { ?>
    <li class="nav-item dropdown">

        <a class="nav-link dropdown-toggle" href="/catalog" role="button" data-bs-toggle="dropdown">
            Catalog
        </a>

        <ul class="dropdown-menu">

            <li>
                <a class="dropdown-item" href="/catalog">
                    All products
                </a>
            </li>

            <li><hr class="dropdown-divider"></li>

            <?php foreach ($categories as $category) { ?>
                <li>
                    <a class="dropdown-item"
                       href="/catalog?filters[category]=<?= urlencode($category->Name) ?>">
                        <?= htmlspecialchars($category->Name) ?>
                    </a>
                </li>
            <?php } ?>

        </ul>

    </li>
<?php } ?>