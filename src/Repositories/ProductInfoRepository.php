<?php

namespace App\Repositories;
use App\Entities\ProductInfo;


class ProductInfoRepository extends Repository {
    protected static string $tableName = "ProductInfo";

    public static function getByProductID(int $productID): array {
        $result = self::select(["ProductID" => $productID]);
        return array_map(function ($row) {
            return new ProductInfo(
                $row->ID,
                $row->ProductID,
                $row->Key,
                $row->Value,
            );
        }, $result);
    }
}
