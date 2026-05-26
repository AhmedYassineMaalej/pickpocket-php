<?php

namespace App\Controllers;

use App\Helpers\JWT;
use App\Repositories\BookmarkRepository;
use App\Repositories\ProductRepository;

class BookmarksController
{
    public static function index(): void
    {
        require __DIR__ . '/../../views/pages/bookmarks.php';
    }

    public static function getBookmarksJson()
    {
        header('Content-Type: application/json');

        if (!JWT::isLoggedIn()) {
            echo json_encode(['items' => [], 'total' => 0]);
            exit;
        }

        $userId = JWT::getUserId();
        $bookmarks = BookmarkRepository::getUserBookmarks($userId);

        $response = [
            'items' => [],
        ];

        foreach ($bookmarks as $bookmark) {
            $response['items'][] = [
                'id'       => $bookmark['id'],
                'name'     => $bookmark['name'],
                'image'    => $bookmark['image'],
                'quantity' => $bookmark['quantity'],
                'price'    => $bookmark['price'],
                'total'    => $bookmark['total'],
            ];
        }

        echo json_encode($response);
        exit;
    }

    public static function addBookmark()
    {
        header('Content-Type: application/json');

        if (!JWT::isLoggedIn()) {
            echo json_encode(['success' => false, 'error' => 'Not logged in']);
            exit;
        }

        $userId = JWT::getUserId();

        $productReference = $_POST['productReference'] ?? null;
        if (!$productReference) {
            echo json_encode(['success' => false, 'error' => 'Missing product reference']);
            exit;
        }

        $productID = ProductRepository::getProductByReference($productReference)->id;
        $result = BookmarkRepository::addUserBookmark($userId, $productID);

        error_log(print_r($result, true));

        if ($result) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to add to bookmarks']);
        }

        exit;
    }

    public static function removeBookmark(): void
    {
        header('Content-Type: application/json');

        if (!JWT::isLoggedIn()) {
            echo json_encode(['success' => false, 'error' => 'Not logged in']);
            exit;
        }

        $userId = JWT::getUserId();
        $productReference = null;

        if (isset($_POST['productReference']) || isset($_POST['bookmarks_item_id'])) {
            $productReference = $_POST['productReference'] ?? $_POST['bookmarks_item_id'];
        }

        if (!$productReference) {
            $rawInput = file_get_contents('php://input');
            $jsonData = json_decode($rawInput, true);
            if ($jsonData) {
                $productReference = $jsonData['productReference'] ?? $jsonData['bookmarks_item_id'] ?? null;
            }
        }

        if (!$productReference || $productReference === 'undefined') {
            echo json_encode(['success' => false, 'error' => 'Missing product identifier reference.']);
            exit;
        }

        $productId = null;

        if (is_numeric($productReference)) {
            $bookmarkId = (int)$productReference;
            
            $db = BookmarkRepository::getConnection();
            $stmt = $db->prepare("SELECT ProductID FROM Bookmark WHERE ID = :bookmarkId AND UserID = :userId");
            $stmt->execute([':bookmarkId' => $bookmarkId, ':userId' => $userId]);
            $row = $stmt->fetch(\PDO::FETCH_OBJ);
            
            if ($row) {
                $productId = (int)$row->ProductID;
            }
        } else {
            $product = ProductRepository::getProductByReference($productReference);
            if ($product) {
                $productId = (int)$product->id;
            }
        }

        if (!$productId) {
            echo json_encode(['success' => false, 'error' => 'Target product record could not be matched.']);
            exit;
        }

        BookmarkRepository::removeUserBookmark((int)$userId, $productId);

        echo json_encode(['success' => true]);
        exit;
    }
}