<?php

namespace App\Repositories;

use App\Entities\Category;

class CategoryRepository extends Repository
{
    protected static string $tableName = "category";

    public static function getByID(int $categoryID): Category
    {
        $data = self::select(['id' => $categoryID])[0];
        return self::convertToCategory($data);
    }

    private static function convertToCategory(object $data): Category
    {
        return new Category(
            $data->id,
            $data->name,
        );
    }
}
