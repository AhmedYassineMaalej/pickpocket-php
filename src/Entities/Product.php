<?php

namespace App\Entities;
use App\Repositories\CategoryRepository;


class Product {
    public int $id;
    public string $name;
    public string $reference;
    public string $image;
    public Category $category;

    public function __construct(int $id, string $name, string $reference, string $image, Category|int $category) {
        $this->id = $id;
        $this->name = $name;
        $this->reference = $reference;
        $this->image = $image;

        if ($category instanceof Category){
            $this->category = $category;
        }
        else{
            $this->category = CategoryRepository::getByID($category);
        }

        
    }
    public static function convertToProduct($data) {
        $product= new Product(
            $data["id"],
            $data["name"],
            $data["reference"],
            $data["image"],
            $data["category_id"] );
    return $product;
    }
}
