<?php

namespace App\Repositories;

use App\Entities\ProductInfo;

class ProductInfoRepository extends Repository
{
    protected static string $tableName = "product_info";

    public static function getByProductID(int $productID): array
    {
        $result = self::select(["product_id" => $productID]);
        return array_map(function ($row) {
            return new ProductInfo(
                $row->id,
                $row->product_id,
                $row->key,
                $row->value,
            );
        }, $result);
    }
}
