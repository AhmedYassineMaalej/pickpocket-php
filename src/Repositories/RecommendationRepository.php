<?php

namespace App\Repositories;

use App\Entities\Product;
use Exception;
use PDO;
/* 
4 methods exist here:
- one that updates weights on a bookmark
- one that given a product fetches the recommended products
- one that given a user fetches the recommended products
- one that given a category fetches the top relationships
*/
class RecommendationRepository extends Repository {
    protected static string $tableName = "recommendation";

    public static function updateWeightsOnBookmark(int $userId, int $newProductId, bool $increment = true): void {
        try {
            // Get all existing bookmarked products (excluding the new one)
            $stmt = self::getConnection()->prepare("
                SELECT ProductID as product_id
                FROM Bookmark
                WHERE UserID = ? AND ProductID != ?
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
                $existingProductId = $existing->product_id;
                
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

    public static function getRecommendationsForProduct(int $productId, int $limit = 6): array {
        try {
            // Get product's category
            $stmt = self::getConnection()->prepare("
                SELECT CategoryID as category_id FROM Product WHERE ID = ?
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

    public static function getRecommendationsForUser(int $userId, int $limit = 6): array {
        try {
            // Get products from user's bookmarks
            $stmt = self::getConnection()->prepare("
                SELECT DISTINCT ProductID as product_id
                FROM Bookmark
                WHERE UserID = ?
            ");
            $stmt->execute([$userId]);
            $bookmarkedProducts = $stmt->fetchAll(\PDO::FETCH_OBJ);

            if (empty($bookmarkedProducts)) {
                return [];
            }

            $bookmarkedProductIds = array_column($bookmarkedProducts, 'product_id');
            $placeholders = implode(',', array_fill(0, count($bookmarkedProductIds), '?'));

            $limit = (int)$limit;
            
            $sql = "
                SELECT 
                    CASE 
                        WHEN product_id1 IN ($placeholders) THEN product_id2
                        ELSE product_id1
                    END as recommended_product_id,
                    SUM(weight) as total_weight
                FROM recommendation
                WHERE (product_id1 IN ($placeholders) OR product_id2 IN ($placeholders))
                GROUP BY recommended_product_id
                HAVING recommended_product_id IS NOT NULL
                ORDER BY total_weight DESC
                LIMIT $limit
            ";

            $params = array_merge($bookmarkedProductIds, $bookmarkedProductIds, $bookmarkedProductIds);

            $stmt = self::getConnection()->prepare($sql);
            $stmt->execute($params);
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
            error_log("Failed to get user recommendations: " . $e->getMessage());
            return [];
        }
    }

    public static function getTopRelationshipsInCategory(int $categoryId, int $limit = 6): array {
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