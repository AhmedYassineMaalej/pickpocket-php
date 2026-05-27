<?php

namespace App\Repositories;

use App\Entities\Bookmark;
use PDO;

class BookmarkRepository extends Repository
{
    protected static string $tableName = 'Bookmark';

    /**
     * @return array<Bookmark>
     */
    public static function getUserBookmarks(int $userID): array
{
    $sql = "
    SELECT 
        b.ID AS bookmark_id,
        b.UserID,
        b.ProductID,
        p.Name AS product_name,
        p.Reference AS product_reference, 
        p.Image AS product_image,
        p.CategoryID,                     
        po.Price AS price
    FROM Bookmark b
    JOIN Product p ON b.ProductID = p.ID
    LEFT JOIN ProductOffer po ON po.ProductID = p.ID
    WHERE b.UserID = :userID
    ";

    $stmt = self::getConnection()->prepare($sql);
    $stmt->bindValue(':userID', $userID);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_OBJ);

    return array_map(function ($row) {
        return new \App\Entities\Product(
            $row->ProductID,
            $row->product_name,
            $row->product_reference,
            $row->product_image,
            (int)$row->CategoryID
        );
    }, $rows);
}
    public static function addUserBookmark(int $userID, int $productID): bool
    {
        return self::insert([
            'UserID' => $userID,
            'ProductID' => $productID,
        ]);
    }

    public static function removeUserBookmark(int $userID, int $productID): void
    {
        self::delete([
            'UserID' => $userID,
            'ProductID' => $productID,
        ]);
    }


    public static function isProductBookmarked(int $userID, int $productID): bool
    {
        $rows = self::select([
            'UserID' => $userID,
            'ProductID' => $productID,
        ]);

        return count($rows) > 0;
    }
}
