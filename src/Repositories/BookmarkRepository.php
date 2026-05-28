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
            b.product_id,
            p.name AS product_name,
            p.reference AS product_reference, 
            p.image AS product_image,
            p.category_id
        FROM bookmark b, product p
        WHERE b.product_id = p.id
        AND b.user_id = :userID
        ";

        $stmt = self::getConnection()->prepare($sql);
        $stmt->bindValue(':userID', $userID);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_OBJ);

        return array_map(function ($row) {
            return new \App\Entities\Product(
                $row->product_id,
                $row->product_name,
                $row->product_reference,
                $row->product_image,
                (int) $row->category_id,
            );
        }, $rows);
    }

    public static function addUserBookmark(int $userID, int $productID): bool
    {
        try {
            $result = self::insert([
                'user_id' => $userID,
                'product_id' => $productID,
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
                'user_id' => $userID,
                'product_id' => $productID,
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
            'user_id' => $userID,
            'product_id' => $productID,
        ]);

        return count($rows) > 0;
    }
}
