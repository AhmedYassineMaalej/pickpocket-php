<?php

namespace App\Repositories;
use App\Entities\ProductOffer;

use Exception;

class ProductOfferRepository extends Repository {
    protected static string $tableName = "ProductOffer";

    public static function getProductOffers(int $productID): array {
        $data = self::select(['ProductID' => $productID]);
        return array_map(self::convertToProductOffer(...), $data);
    }

    private static function convertToProductOffer(object $data): ProductOffer {
        if (!$data) {
            throw new Exception("unable to convert data into ProductOffer");
        };

        return new ProductOffer(
            $data->ID,
            $data->ProductID,
            $data->Link,
            $data->Price,
            $data->ProviderID
        );
    }
    public static function getProductOfferById(int $id): ?ProductOffer {
        $data = self::findById($id);
        if (!$data) return null;
        return self::convertToProductOffer($data);
    }
    public  static function filterOffers(array $filters = []) //this function gives the product with the minimal price with all its possibles proprities
    {
        $joinCategory = false;
        $joinProvider = false;

        $conditions = [];
        $params = [];


        if (!empty($filters['category'])) {
            $conditions[] = "c.Name = :category";
            $params[':category'] = $filters['category'];
            $joinCategory = true;
        }

        if (!empty($filters['provider'])) {
            $conditions[] = "pr.Name = :provider";
            $params[':provider'] = $filters['provider'];
            $joinProvider = true;
        }


        if (!empty($filters['min_price'])) {
            $conditions[] = "po.Price >= :min_price";
            $params[':min_price'] = $filters['min_price'];
        }

        if (!empty($filters['max_price'])) {
            $conditions[] = "po.Price <= :max_price";
            $params[':max_price'] = $filters['max_price'];
        }


        if (!empty($filters['search'])) {
            $conditions[] = "
        (
            p.Name LIKE :search
            OR p.Reference LIKE :search

        )
        ";
            $params[':search'] = "%" . $filters['search'] . "%";
        }


        $sql = "
        SELECT 
            po.ID AS offer_id,
            po.Price,
            po.Link,

            p.ID AS product_id,
            p.Name AS product_name,
            p.Reference AS product_reference,
            p.Image,
            p.CategoryID,

            c.Name AS category_name,
            pr.Name AS provider_name,

            pi.ID AS info_id,
            pi.`Key` AS info_key,
            pi.Value AS info_value

       FROM Product p
       JOIN ProductOffer po
       ON po.ProductID = p.ID
       JOIN (
       SELECT ProductID, MIN(Price) AS min_price
       FROM ProductOffer
       GROUP BY ProductID
       ) best
       ON best.ProductID = po.ProductID
       AND best.min_price = po.Price
    ";


        if ($joinCategory) {
            $sql .= " JOIN Category c ON p.CategoryID = c.ID";
        } else {
            $sql .= " LEFT JOIN Category c ON p.CategoryID = c.ID";
        }


        if ($joinProvider) {
            $sql .= " JOIN Provider pr ON po.ProviderID = pr.ID";
        } else {
            $sql .= " LEFT JOIN Provider pr ON po.ProviderID = pr.ID";
        }


        $sql .= " LEFT JOIN ProductInfo pi ON pi.ProductID = p.ID";


        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }


        if (!empty($filters['sort_by'])) {
            $allowedSort = ['price', 'name'];

            if (in_array($filters['sort_by'], $allowedSort)) {
                $direction = (!empty($filters['order']) && strtolower($filters['order']) === 'desc')
                    ? 'DESC'
                    : 'ASC';

                if ($filters['sort_by'] === 'price') {
                    $sql .= " ORDER BY po.Price $direction";
                } else {
                    $sql .= " ORDER BY p.Name $direction";
                }
            }
        }


        if (!empty($filters['limit'])) {
            $sql .= " LIMIT :limit";
            $params[':limit'] = (int)$filters['limit'];
        }

        if (!empty($filters['offset'])) {
            $sql .= " OFFSET :offset";
            $params[':offset'] = (int)$filters['offset'];
        }

        $stmt = self::getConnection()->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue(
                $key,
                $value,

            );
        }

        $stmt->execute();
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $offers = [];

        foreach ($rows as $row) {

            $id = $row['offer_id'];

            if (!isset($offers[$id])) {
                $offers[$id] = [
                    'offer_id' => $row['offer_id'],
                    'Price' => $row['Price'],
                    'Link' => $row['Link'],

                    'product' => [
                        'id' => $row['product_id'],
                        'name' => $row['product_name'],
                        'reference' => $row['product_reference'],
                        'image' => $row['Image'],
                        'category_id' => $row['CategoryID'],
                        'category_name' => $row['category_name'],
                        'provider_name' => $row['provider_name'],
                    ],

                    'info' => []
                ];
            }

            if (!empty($row['info_key'])) {
                $offers[$id]['info'][$row['info_key']] = $row['info_value'];
            }
        }

        return array_values($offers);

    }


    function filterOffers(PDO $pdo, array $filters = []) //this function offers all info Possible on productoffers matching the filters
                                                         //if class object are needed as a result ,we should manually map it to classes because the result groups attributs from different classes
    {
        $joinCategory = false;
        $joinProvider = false;

        $conditions = [];
        $params = [];


        if (!empty($filters['category'])) {
            $conditions[] = "c.Name = :category";
            $params[':category'] = $filters['category'];
            $joinCategory = true;
        }

        if (!empty($filters['provider'])) {
            $conditions[] = "pr.Name = :provider";
            $params[':provider'] = $filters['provider'];
            $joinProvider = true;
        }


        if (!empty($filters['min_price'])) {
            $conditions[] = "po.Price >= :min_price";
            $params[':min_price'] = $filters['min_price'];
        }

        if (!empty($filters['max_price'])) {
            $conditions[] = "po.Price <= :max_price";
            $params[':max_price'] = $filters['max_price'];
        }


        if (!empty($filters['search'])) {
            $conditions[] = "
        (
            p.Name LIKE :search
            OR p.Reference LIKE :search
            OR p.Description LIKE :search
        )
        ";
            $params[':search'] = "%" . $filters['search'] . "%";
        }


        $sql = "
        SELECT 
            po.ID AS offer_id,
            po.Reference,
            po.Price,
            po.Link,

            p.ID AS product_id,
            p.Name AS product_name,
            p.Reference AS product_reference,
            p.Description,
            p.Image,
            p.CategoryID,

            c.Name AS category_name,
            pr.Name AS provider_name,

            pi.ID AS info_id,
            pi.`Key` AS info_key,
            pi.Value AS info_value

        FROM ProductOffer po
        JOIN Product p ON po.Reference = p.Reference
    ";


        if ($joinCategory) {
            $sql .= " JOIN Category c ON p.CategoryID = c.ID";
        } else {
            $sql .= " LEFT JOIN Category c ON p.CategoryID = c.ID";
        }


        if ($joinProvider) {
            $sql .= " JOIN Provider pr ON po.ProviderID = pr.ID";
        } else {
            $sql .= " LEFT JOIN Provider pr ON po.ProviderID = pr.ID";
        }


        $sql .= " LEFT JOIN ProductInfo pi ON pi.ProductID = p.ID";


        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }


        if (!empty($filters['sort_by'])) {
            $allowedSort = ['price', 'name'];

            if (in_array($filters['sort_by'], $allowedSort)) {
                $direction = (!empty($filters['order']) && strtolower($filters['order']) === 'desc')
                    ? 'DESC'
                    : 'ASC';

                if ($filters['sort_by'] === 'price') {
                    $sql .= " ORDER BY po.Price $direction";
                } else {
                    $sql .= " ORDER BY p.Name $direction";
                }
            }
        }


        if (!empty($filters['limit'])) {
            $sql .= " LIMIT :limit";
            $params[':limit'] = (int)$filters['limit'];
        }

        if (!empty($filters['offset'])) {
            $sql .= " OFFSET :offset";
            $params[':offset'] = (int)$filters['offset'];
        }

        $stmt = $pdo->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue(
                $key,
                $value,

            );
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}

