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

        $productReference = $_POST['productReference'];
        $productID = ProductRepository::getProductByReference($productReference)->id;
        $result = BookmarkRepository::addUserBookmark($userId, $productID);


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
            exit;
        }

        $userId = JWT::getUserId();
        $productReference = $_POST["productReference"];
        $product = ProductRepository::getProductByReference($productReference);
        BookmarkRepository::removeUserBookmark($userId, $product->id);

        exit;
    }
}
