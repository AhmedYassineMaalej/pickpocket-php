<?php
use App\Helpers\JWT;
use App\Repositories\BookmarkRepository;

require __DIR__ . '/../fragments/head.php';
require __DIR__ . '/../fragments/navbar.php';
require __DIR__ . '/../fragments/product_section.php';
?>

<!doctype html>
<html lang="en">
    <?php head('Bookmarks'); ?>
    <script src="js/bookmark_button.js"></script>
<body>

<?php navbar(); ?>
<?php
$userID = JWT::getUserId();
$products = BookmarkRepository::getUserBookmarks($userID);

product_section('My Bookmarks', $products);

?>


</body>
</html>
