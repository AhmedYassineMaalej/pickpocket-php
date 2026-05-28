<?php

namespace App\Repositories;

use App\Entities\Bookmark;
use App\Entities\Product;
use PDO;

class BookmarkRepository extends Repository
{
    protected static string $tableName = 'bookmark';

    /**
     * @return array<Bookmark>
     */
    public static function getUserBookmarks(int $userID): array
    {
        $sql = "
        SELECT 
        b.id AS bookmark_id,
        b.user_id,
        b.product_id,
        p.name AS product_name,
        p.reference AS product_reference, 
        p.image AS product_image,
        p.category_id,                     
        o.price AS price
        FROM bookmark b
        JOIN product p ON b.product_id = p.id
        LEFT JOIN offer o ON o.product_id = p.id
        WHERE b.user_id = :user_id
        ";

        $stmt = self::getConnection()->prepare($sql);
        $stmt->bindValue(':user_id', $userID);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_OBJ);

        return array_map(function ($row) {
            return new Product(
                $row->product_id,
                $row->product_name,
                $row->product_reference,
                $row->product_image,
                $row->category_id,
            );
        }, $rows);
    }
    public static function addUserBookmark(int $userID, int $productID): bool
    {
        return self::insert([
            'user_id' => $userID,
            'product_id' => $productID,
        ]);
    }

    public static function removeUserBookmark(int $userID, int $productID): void
    {
        self::delete([
            'user_id' => $userID,
            'product_id' => $productID,
        ]);
    }


    public static function isProductBookmarked(int $userID, int $productID): bool
    {
        $rows = self::select([
            'user_id' => $userID,
            'product_id' => $productID,
        ]);

        return count($rows) > 0;
    }
}
