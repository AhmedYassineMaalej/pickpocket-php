<?php

namespace App\Repositories;

use Exception;
use PDO;

class RecommendationRepository extends Repository
{
    protected static string $tableName = "recommendation";

    public static function updateWeightsOnBookmark(int $userId, int $newProductId, bool $increment = true): void
    {
        try {
            // Get all existing bookmarked products (excluding the new one)
            $stmt = self::getConnection()->prepare("
                SELECT productID FROM Bookmark WHERE UserID = ? AND productID != ?
            ");
            $stmt->execute([$userId, $newProductId]);
            $existingProducts = $stmt->fetchAll(\PDO::FETCH_OBJ);

            // Get category of the new product
            $stmt = self::getConnection()->prepare("
                SELECT category_id FROM Product WHERE ID = ?
            ");
            $stmt->execute([$newProductId]);
            $newProduct = $stmt->fetch(\PDO::FETCH_OBJ);
            
            if (!$newProduct) return;
            
            $categoryId = $newProduct->category_id;
            $weightChange = $increment ? 1 : -1;

            // Update weights for each existing bookmarked product in same category
            foreach ($existingProducts as $existing) {
                $existingProductId = $existing->productID;

                // Check if same category
                $stmt = self::getConnection()->prepare("
                    SELECT category_id FROM Product WHERE ID = ?
                ");
                $stmt->execute([$existingProductId]);
                $existingProduct = $stmt->fetch(\PDO::FETCH_OBJ);
                
                if ($existingProduct->category_id != $categoryId) { 
                    continue;
                }

                if ($increment) {
                    $stmt = self::getConnection()->prepare("
                        INSERT INTO recommendation (category_id, product_id1, product_id2, weight)
                        VALUES (?, LEAST(?, ?), GREATEST(?, ?), ?)
                        ON DUPLICATE KEY UPDATE weight = weight + ?
                    ");
                    $stmt->execute([$categoryId, $newProductId, $existingProductId, $newProductId, $existingProductId, $weightChange, $weightChange]);
                } else {
                    $stmt = self::getConnection()->prepare("
                        SELECT weight FROM recommendation 
                        WHERE category_id = ? 
                        AND product_id1 = LEAST(?, ?) 
                        AND product_id2 = GREATEST(?, ?)
                    ");
                    $stmt->execute([$categoryId, $newProductId, $existingProductId, $newProductId, $existingProductId]);
                    $existingRec = $stmt->fetch(\PDO::FETCH_OBJ);

                    if ($existingRec) {
                        $newWeight = $existingRec->weight + $weightChange;

                        if ($newWeight <= 0) {
                            $stmt = self::getConnection()->prepare("
                                DELETE FROM recommendation 
                                WHERE category_id = ? 
                                AND product_id1 = LEAST(?, ?) 
                                AND product_id2 = GREATEST(?, ?)
                            ");
                            $stmt->execute([$categoryId, $newProductId, $existingProductId, $newProductId, $existingProductId]);
                        } else {
                            $stmt = self::getConnection()->prepare("
                                UPDATE recommendation SET weight = ? 
                                WHERE category_id = ? 
                                AND product_id1 = LEAST(?, ?) 
                                AND product_id2 = GREATEST(?, ?)
                            ");
                            $stmt->execute([$newWeight, $categoryId, $newProductId, $existingProductId, $newProductId, $existingProductId]);
                        }
                    }
                }
            }
        } catch (Exception $e) {
            error_log("Failed to update recommendation weights: " . $e->getMessage());
        }
    }

    public static function getRecommendationsForProduct(int $productId, int $limit = 6): array
    {
        try {
            // Get product's category
            $stmt = self::getConnection()->prepare("
                SELECT category_id FROM Product WHERE ID = ?
            ");
            $stmt->execute([$productId]);
            $product = $stmt->fetch(\PDO::FETCH_OBJ);

            if (!$product) {
                return [];
            }

            $categoryId = $product->category_id;

            // Get recommended products based on weight
            $stmt = self::getConnection()->prepare("
                SELECT 
                    CASE 
                        WHEN product_id1 = ? THEN product_id2
                        ELSE product_id1
                    END as recommended_product_id,
                    weight
                FROM recommendation
                WHERE category_id = ? 
                AND (product_id1 = ? OR product_id2 = ?)
                ORDER BY weight DESC
                LIMIT ?
            ");
            $stmt->execute([$productId, $categoryId, $productId, $productId, $limit]);
            $recommendations = $stmt->fetchAll(\PDO::FETCH_OBJ);

            // Fetch full product objects
            $products = [];
            foreach ($recommendations as $rec) {
                $productObj = ProductRepository::getProductById($rec->recommended_product_id);
                if ($productObj) {
                    $products[] = $productObj;
                }
            }
            return $products;
        } catch (Exception $e) {
            error_log("Failed to get recommendations: " . $e->getMessage());
            return [];
        }
    }

    public static function getRecommendationsForUser(int $userId, int $limit = 6): array
{
    try {
        
        // Get products from user's bookmarks
        $stmt = self::getConnection()->prepare("
            SELECT DISTINCT productID
            FROM Bookmark
            WHERE UserID = ?
        ");
        $stmt->execute([$userId]);
        $bookmarkedProducts = $stmt->fetchAll(\PDO::FETCH_OBJ);
        

        if (empty($bookmarkedProducts)) {
            error_log("No bookmarks found, returning empty array");
            return [];
        }

        $bookmarkedProductIds = array_column($bookmarkedProducts, 'productID');
        
        $inClause = implode(',', array_fill(0, count($bookmarkedProductIds), '?'));
        
        // Get recommended products based on weight
        $sql = "
            SELECT 
                CASE 
                    WHEN product_id1 IN ($inClause) THEN product_id2
                    WHEN product_id2 IN ($inClause) THEN product_id1
                END as recommended_product_id,
                SUM(weight) as total_weight
            FROM recommendation
            WHERE (product_id1 IN ($inClause) OR product_id2 IN ($inClause))
            GROUP BY recommended_product_id
            HAVING recommended_product_id IS NOT NULL
            ORDER BY total_weight DESC
            LIMIT $limit
        ";
        
        
        $params = array_merge($bookmarkedProductIds, $bookmarkedProductIds, $bookmarkedProductIds, $bookmarkedProductIds);
        
        
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        $recommendations = $stmt->fetchAll(\PDO::FETCH_OBJ);

        // Fetch full product objects
        $products = [];
        foreach ($recommendations as $rec) {
            
            // Skip if already bookmarked
            if (in_array($rec->recommended_product_id, $bookmarkedProductIds)) {
                error_log("Skipping product " . $rec->recommended_product_id . " - already bookmarked");
                continue;
            }
            
            $productObj = ProductRepository::getProductById($rec->recommended_product_id);
            if ($productObj) {
                error_log("Added product: " . $productObj->name);
                $products[] = $productObj;
            } else {
                error_log("Product not found for ID: " . $rec->recommended_product_id);
            }
        }
        
        return $products;

    } catch (Exception $e) {
        error_log("=== getRecommendationsForUser ERROR ===");
        error_log("Error message: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        return [];
    }
}



    public static function getTopRelationshipsInCategory(int $categoryId, int $limit = 6): array
    {
        try {
            $stmt = self::getConnection()->prepare("
                SELECT product_id1, product_id2, weight
                FROM recommendation
                WHERE category_id = ?
                ORDER BY weight DESC
                LIMIT ?
            ");
            $stmt->execute([$categoryId, $limit]);
            return $stmt->fetchAll(\PDO::FETCH_OBJ);
        } catch (Exception $e) {
            error_log("Failed to get top relationships: " . $e->getMessage());
            return [];
        }
    }
}