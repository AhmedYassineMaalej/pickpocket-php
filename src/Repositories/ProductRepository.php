<?php

namespace App\Repositories;

use App\Entities\Product;
use App\Entities\ProductInfo;
use App\Entities\Offer;
use App\Repositories\ProductOfferRepository;
use Exception;
use PDO;

class ProductRepository extends Repository
{
    protected static string $tableName = "product";


    public static function convertToProduct(object $data): Product
    {
        return new Product(
            $data->id,
            $data->name,
            $data->reference,
            $data->image,
            $data->category_id,
        );
    }

    private static function convertToProductInfo(object $data): ProductInfo
    {
        return new ProductInfo(
            $data->id,
            $data->product_id,
            $data->key,
            $data->value,
        );
    }

    public static function getProductById(int $id): ?Product
    {
        $result = self::findById($id);
        if (!$result) {
            return null;
        }

        return self::convertToProduct($result);
    }

    public static function getProductByReference(string $reference): Product
    {
        $result = self::select(["reference" => $reference]);

        return self::convertToProduct($result[0]);
    }


    /**
     * @return Product[]
     */
    public static function getAllProducts()
    {
        $results = self::findAll();
        return array_map(self::convertToProduct(...), $results);
    }
    /**
     * @return ProductInfo[]
     */
    public static function getProductInfo(int $productId)
    {
        $conn = self::getConnection();
        $stmt = $conn->prepare("SELECT * FROM product_info WHERE product_id = ?");
        $stmt->execute([$productId]);
        $results = $stmt->fetchAll(PDO::FETCH_OBJ);
        return array_map(self::convertToProductInfo(...), $results);
    }

    /**
     * @return array
     */
    public static function getProductsWithMostOffers(int $limit = 8)
    {
        $conn = self::getConnection();
        $stmt = $conn->prepare("
            SELECT p.*, COUNT(po.ID) as offer_count, MIN(po.price) as min_price
            FROM product p
            INNER JOIN offer po ON p.ID = po.product_id
            GROUP BY p.id
            ORDER BY offer_count DESC
            LIMIT $limit
        ");
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_OBJ);

        return array_map(function ($row) {
            return self::convertToProduct($row);
        }, $results);
    }
    /**
     * @return array
     */
    public static function getTopOffers(int $limit = 6)
    {
        $conn = self::getConnection();
        $stmt = $conn->prepare("
            SELECT p.*, MIN(po.price) as min_price, COUNT(po.id) as offer_count
            FROM product p
            INNER JOIN offer po ON p.id = po.product_id
            GROUP BY p.id
            ORDER BY min_price ASC
            LIMIT $limit
        ");

        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_OBJ);

        return array_map(function ($row) {
            return self::convertToProduct($row);
        }, $results);
    }

    /**
     * @return array
     */
    public static function getMostInfoProducts(int $limit = 8)
    {
        $conn = self::getConnection();
        $stmt = $conn->prepare("
            SELECT p.*, COUNT(pi.id) as info_count
            FROM product p
            INNER JOIN product_info pi ON p.id = pi.product_id
            GROUP BY p.id
            ORDER BY info_count DESC
            LIMIT $limit
        ");
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_OBJ);

        return array_map(function ($row) {
            return self::convertToProduct($row);
        }, $results);
    }
    /**
     * @return array
     */
    public static function getNewestProducts($limit = 8)
    {
        try {
            $limit = (int) $limit;
            $conn = self::getConnection();
            $stmt = $conn->prepare("
                SELECT * FROM product 
                ORDER BY id DESC 
                LIMIT $limit
            ");
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_OBJ);
            return array_map(function ($result) {
                return self::convertToProduct($result);
            }, $results);
        } catch (Exception $e) {
            return [];
        }
    }
    /**
     * @return null|array
     */
    public static function getDealOfTheDay()
    {
        $conn = self::getConnection();
        $stmt = $conn->prepare("
            SELECT p.*, MIN(po.price) as min_price
            FROM product p
            INNER JOIN offer po ON p.id = po.product_id
            GROUP BY p.id
            ORDER BY RAND()
            LIMIT 1
        ");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_OBJ);

        if (!$row) {
            return null;
        }

        return [
            $row->reference ?? '',
            $row->description ?? $row->Name ?? 'Product',
            $row->image ?? '/images/placeholder.png',
            $row->min_price ?? 0,
        ];
    }

    public static function getMinPriceForProduct(int $productId): ?float
    {
        $conn = self::getConnection();
        $stmt = $conn->prepare("
            SELECT MIN(price) as min_price
            FROM offer
            WHERE product_id = ?
        ");
        $stmt->execute([$productId]);
        $result = $stmt->fetch(PDO::FETCH_OBJ);

        return $result->min_price ? (float) $result->min_price : null;
    }

    public static function getCompleteProduct(int $id): ?object
    {
        $product = self::getProductById($id);
        if (!$product) {
            return null;
        }

        return (object) [
            'product' => $product,
            'info' => self::getProductInfo($id),
            'offers' => OfferRepository::getProductOffers($id),
        ];
    }
}
