<?php
use App\Entities\Bookmark;
use App\Helpers\JWT;
use App\Repositories\BookmarkRepository;

require __DIR__ . '/../fragments/head.php';
require __DIR__ . '/../fragments/navbar.php';
require __DIR__ . '/../fragments/product_section.php';
?>

<!doctype html>
<html lang="en">
    <?php head('Pickpocket | Bookmarks', 'common.css'); ?>
    <script src="js/bookmark_button.js"></script>
<body>

<?php navbar(); ?>
<?php
$userID = JWT::getUserId();
$bookmarks = BookmarkRepository::getUserBookmarks($userID);
$products = array_map(function (Bookmark $bookmark) {
    return $bookmark->product;
}, $bookmarks);

product_section('My Bookmarks', $products);

?>


</body>
</html>
