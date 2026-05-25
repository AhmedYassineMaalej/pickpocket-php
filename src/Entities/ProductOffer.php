<?php

namespace App\Entities;
use App\Repositories\ProductRepository;
use App\Repositories\ProductOfferRepository;
use App\Repositories\ProviderRepository;
use PDO;
class ProductOffer {
    public int $id;
    public Product $product;
    public string $link;
    public float $price;
    public Provider $provider;

    public function __construct(int $id, Product|int $product, string $link, float $price, Provider|int $provider) {
        $this->id = $id;
        $this->link = $link;
        $this->price = $price;
        if ($product instanceof Product){
            $this->product = $product;
        }
        else{
            $this->product = ProductRepository :: getProductById($product);
        }
        if ($provider instanceof Provider){
            $this->provider = $provider;
        }
        else{
            $this->provider = ProviderRepository :: getByID ($provider);
        }
    }
    public function convertToProductOffer($data){
        $productOffer = new ProductOffer($data->id,  $data->product, $data->link, $data->price, $data->provider);

        return $productOffer;

    }

    public function getProductOffersByProductId(int $productId): array {
            $conn = self::getConnection();
            $stmt = $conn->prepare("SELECT * FROM ProductOffer WHERE ProductID = ?");
            $stmt->execute([$productId]);
            $results = $stmt->fetchAll(PDO::FETCH_OBJ);
            return array_map(self::convertToProductOffer(...), $results);
        }


}
