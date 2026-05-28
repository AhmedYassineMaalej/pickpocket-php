<?php

namespace App\Repositories;

use App\Entities\Bookmark;
use App\Repositories\RecommendationRepository;
use Exception;
use PDO;

class BookmarkRepository extends Repository
{
    protected static string $tableName = 'bookmark';

    /**
     * @return array<Product>
     */
    public static function getUserBookmarks(int $userID): array
    {
        $sql = "
        SELECT 
            b.ID AS bookmark_id,
            b.UserID,
            b.productID,
            p.Name AS product_name,
            p.Reference AS product_reference, 
            p.Image AS product_image,
            p.category_id,                     
            po.Price AS price
        FROM Bookmark b
        JOIN Product p ON b.productID = p.ID
        LEFT JOIN offer po ON po.product_id = p.ID
        WHERE b.UserID = :userID
        ";

        $stmt = self::getConnection()->prepare($sql);
        $stmt->bindValue(':userID', $userID);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_OBJ);

        return array_map(function ($row) {
            return new \App\Entities\Product(
                $row->productID,
                $row->product_name,
                $row->product_reference,
                $row->product_image,
                (int)$row->category_id
            );
        }, $rows);
    }

    public static function addUserBookmark(int $userID, int $productID): bool
    {
        try {
            $result = self::insert([
                'UserID' => $userID,
                'productID' => $productID,
            ]);
            
            if ($result) {
                RecommendationRepository::updateWeightsOnBookmark($userID, $productID, true);
                return true;
            }
            return false;
        } catch (Exception $e) {
            error_log("Failed to add bookmark: " . $e->getMessage());
            return false;
        }
    }

    public static function removeUserBookmark(int $userID, int $productID): bool
    {
        try {
            self::delete([
                'UserID' => $userID,
                'productID' => $productID,
            ]);
            
            RecommendationRepository::updateWeightsOnBookmark($userID, $productID, false);
            return true;
        } catch (Exception $e) {
            error_log("Failed to remove bookmark: " . $e->getMessage());
            return false;
        }
    }

    public static function isProductBookmarked(int $userID, int $productID): bool
    {
        $rows = self::select([
            'UserID' => $userID,
            'productID' => $productID,
        ]);

        return count($rows) > 0;
    }
}