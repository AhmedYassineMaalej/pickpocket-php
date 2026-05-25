<?php

namespace App\Controllers;
use App\Repositories\ProductRepository;
use App\Repositories\ProductOfferRepository; 
use App\Entities\ProductInfo; 
use App\Entities\ProductOffer;
use App\Entities\Product;
use App\Helpers\JWT;
require_once __DIR__ . '/../../views/fragments/product_section.php';

class CatalogController {
    public static function index() {
        if (! JWT::isLoggedIn()){
            $_SESSION['error'] = "You're not logged in yet !";
            header('Location: /');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'GET'){
            $filters=$_GET["filters"] ?? [];
            $product_array = ProductOfferRepository::filterOffers($filters);


            $products = array_map(
                function ($offer) {
                    return Product::convertToProduct( $offer['product']);
                },
                $product_array
            );


            require __DIR__ . '/../../views/pages/catalog.php';
        }
        else if ($_SERVER['REQUEST_METHOD'] === 'POST'){
            add_to_cart();
        }
        else{
            header('HTTP/1.1 405 Method Not Allowed');
            echo "Method Not Allowed";
            exit;
        }
    }

    public static function getProductAjax(): void {
        header('Content-Type: application/json');

        if (!JWT::isLoggedIn()) {
            echo json_encode(['error' => 'Not logged in']);
            exit;
        }

        // Get product ID
        $productId = isset($_GET['id']) ? (int)$_GET['id'] : null;

        if (!$productId) {
            echo json_encode(['error' => 'Product ID required']);
            exit;
        }

        $completeProduct = ProductRepository::getCompleteProduct($productId);

        // Check if product exists
        if (!$completeProduct || !$completeProduct->product) {
            echo json_encode(['error' => 'Product not found']);
            exit;
        }

        $response = [
            'product' => [
                'id' => $completeProduct->product->id,
                'reference' => $completeProduct->product->reference,
                'image' => $completeProduct->product->image,
                'categoryName' => $completeProduct->product->category->Name,
                'name' => $completeProduct->product->name,
            ],
            'info' => [],
            'offers' => []
        ];
        
        // Add product info if exists
        if (isset($completeProduct->info) && is_array($completeProduct->info)) {
            foreach ($completeProduct->info as $info) {
                if ($info) {
                    $response['info'][] = [
                        'key' => $info->key,
                        'value' => $info->value
                    ];
                }
            }
        }
        
        // Add offers if exists - using product_id now
        if (isset($completeProduct->offers) && is_array($completeProduct->offers)) {
            foreach ($completeProduct->offers as $offer) {
                if ($offer) {
                    $response['offers'][] = [
                        'id' => $offer->id,
                        'product_id' => $offer->product->id,  // Changed from reference to product_id
                        'link' => $offer->link,
                        'price' => $offer->price,
                        'providerName' => $offer->provider->Name
                    ];
                }
            }
        }
        
        // Output JSON and exit
        echo json_encode($response);
        exit;
    }


    public static function getFilteredProductsAJAX(): void
    {
        $filters = [
            'min_price' => isset($_GET['minPrice']) ? (float)$_GET['minPrice'] : null,
            'max_price' => isset($_GET['maxPrice']) ? (float)$_GET['maxPrice'] : null,
            'category' => isset($_GET['category']) ? trim($_GET['category']) : '',
            'provider' => isset($_GET['providers'][0]) ? trim($_GET['providers'][0]) : '', // filterOffers expects a single string
        ];

        // Remove null/empty so filterOffers conditions are not triggered
        $filters = array_filter($filters, fn($v) => $v !== null && $v !== '');

        $product_array = ProductOfferRepository::filterOffers($filters);


        $products = array_map(
            function ($offer) {
                return Product::convertToProduct( $offer['product']);
            },
            $product_array
        );

        product_section("",$products);
    }


}





