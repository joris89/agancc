<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $products = [
            ['name' => 'Product 1', 'price' => 9.99],
            ['name' => 'Product 2', 'price' => 19.99],
            ['name' => 'Product 3', 'price' => 29.99],
            ['name' => 'Product 4', 'price' => 39.99],
            ['name' => 'Product 5', 'price' => 49.99],
        ];

        foreach ($products as $data) {
            $product = new Product();
            $product->setName($data['name']);
            $product->setPrice($data['price']);

            $manager->persist($product);
        }

        $manager->flush();
    }
}
