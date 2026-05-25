<?php

namespace App\Entities;

use App\Repositories\ProductRepository;
use App\Repositories\UserRepository;

class Bookmark
{
    public User $user;
    public Product $product;

    public function __construct(int|User $user, int|Product $product)
    {
        if (is_int($user)) {
            $user = UserRepository::getUserById($user);
        }

        if (is_int($product)) {
            $product = ProductRepository::getProductById($product);
        }
        $this->user = $user;
        $this->product = $product;
    }
}
