<?php

namespace App\Repositories;

use App\Entities\Product;
use Exception;
use PDO;

class RecommendationRepository extends Repository
{
    protected static string $tableName = "recommendation";

    public static function updateWeightsOnBookmark(int $userId, int $newProductOfferId, bool $increment = true): void
    {
        try {
            // product_id from product_offer
            $stmt = self::getConnection()->prepare("
                SELECT ProductID FROM ProductOffer WHERE id = ?
            ");
            $stmt->execute([$newProductOfferId]);
            $offer = $stmt->fetch(\PDO::FETCH_OBJ);

            if (!$offer) {
                return;
            }

            $newProductId = $offer->ProductID;

            // Get all existing products in user's cart (excluding the new one)
            $stmt = self::getConnection()->prepare("
                SELECT DISTINCT p.ID as product_id
                FROM Cart c
                JOIN CartItem ci ON c.id = ci.cart_id
                JOIN ProductOffer po ON ci.product_offer_id = po.id
                JOIN Product p ON po.ProductID = p.ID
                WHERE c.user_id = ? AND p.ID != ?
            ");

            $stmt->execute([$userId, $newProductId]);
            $existingProducts = $stmt->fetchAll(\PDO::FETCH_OBJ);

            // Get category of the new product
            $stmt = self::getConnection()->prepare("
                SELECT CategoryID FROM Product WHERE ID = ?
            ");
            $stmt->execute([$newProductId]);
            $newProduct = $stmt->fetch(\PDO::FETCH_OBJ);

            if (!$newProduct) {
                return;
            }

            $categoryId = $newProduct->CategoryID;

            // Determine weight change
            $weightChange = $increment ? 1 : -1;

            // Update weights for each existing product in same category
            foreach ($existingProducts as $existing) {
                $existingProductId = $existing->product_id;

                // Check if same category
                $stmt = self::getConnection()->prepare("
                    SELECT CategoryID FROM Product WHERE ID = ?
                ");
                $stmt->execute([$existingProductId]);
                $existingProduct = $stmt->fetch(\PDO::FETCH_OBJ);

                if ($existingProduct->CategoryID != $categoryId) {
                    continue;
                }

                if ($increment) {
                    // Adding bookmark - insert or update
                    $stmt = self::getConnection()->prepare("
                        INSERT INTO recommendation (category_id, product_id1, product_id2, weight)
                        VALUES (?, LEAST(?, ?), GREATEST(?, ?), ?)
                        ON DUPLICATE KEY UPDATE weight = weight + ?
                    ");
                    $stmt->execute([$categoryId, $newProductId, $existingProductId, $newProductId, $existingProductId, $weightChange, $weightChange]);
                } else {
                    // Removing bookmark - check current weight first
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
                            // Delete the row if weight becomes 0 or negative
                            $stmt = self::getConnection()->prepare("
                                DELETE FROM recommendation 
                                WHERE category_id = ? 
                                AND product_id1 = LEAST(?, ?) 
                                AND product_id2 = GREATEST(?, ?)
                            ");
                            $stmt->execute([$categoryId, $newProductId, $existingProductId, $newProductId, $existingProductId]);
                        } else {
                            // Update with new weight
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

    /**
    given a product id, give an array of "limit" products with the top strongest edges
     */
    public static function getRecommendationsForProduct(int $productId, int $limit = 6): array
    {
        try {
            // Get product's category
            $stmt = self::getConnection()->prepare("
                SELECT category_id FROM Product WHERE id = ?
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


    // given a user id, returns an array of products of lencth exactly = limit

    public static function getRecommendationsForUser(int $userId, int $limit = 6): array
    {
        try {
            // Get products from user's cart
            $stmt = self::getConnection()->prepare("
                SELECT DISTINCT p.ID as product_id
                FROM Cart c
                JOIN CartItem ci ON c.id = ci.cart_id
                JOIN ProductOffer po ON ci.product_offer_id = po.id
                JOIN Product p ON po.ProductID = p.ID
                WHERE c.user_id = ?
            ");
            $stmt->execute([$userId]);
            $cartProducts = $stmt->fetchAll(\PDO::FETCH_OBJ);

            error_log("=== DEBUG getRecommendationsForUser ===");
            error_log("User ID: " . $userId);
            error_log("Cart products found: " . json_encode($cartProducts));

            if (empty($cartProducts)) {
                error_log("No cart products found for user: " . $userId);
                return [];
            }

            $cartProductIds = array_column($cartProducts, 'product_id');
            $placeholders = implode(',', array_fill(0, count($cartProductIds), '?'));

            error_log("Cart product IDs: " . json_encode($cartProductIds));
            error_log("Placeholders: " . $placeholders);

            // Embed limit directly in SQL (cast to int for safety)
            $limit = (int) $limit;

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

            error_log("SQL Query: " . $sql);

            // Only pass the placeholders for IN clauses, not for LIMIT
            $params = array_merge($cartProductIds, $cartProductIds, $cartProductIds);

            $stmt = self::getConnection()->prepare($sql);
            $stmt->execute($params);
            $recommendations = $stmt->fetchAll(\PDO::FETCH_OBJ);

            error_log("Recommendations found: " . count($recommendations));
            error_log("Recommendations data: " . json_encode($recommendations));

            // Fetch full product objects
            $products = [];
            foreach ($recommendations as $rec) {
                error_log("Fetching product for ID: " . $rec->recommended_product_id);
                $productObj = ProductRepository::getProductById($rec->recommended_product_id);
                if ($productObj) {
                    $products[] = $productObj;
                    error_log("Product found: " . $productObj->name);
                } else {
                    error_log("Product NOT found for ID: " . $rec->recommended_product_id);
                }
            }

            error_log("Returning " . count($products) . " products");
            error_log("=== END DEBUG ===");

            return $products;
        } catch (Exception $e) {
            error_log("Failed to get user recommendations: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return [];
        }
    }



    /**
     given a category id, it fetches an array of "limit" products that are correlated the most
     */
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

