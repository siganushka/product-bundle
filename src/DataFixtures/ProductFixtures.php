<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Siganushka\ProductBundle\Entity\Product;

class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $product1 = new Product();
        $product1->setName('iPhone 15');

        $manager->persist($product1);
        $manager->flush();

        $this->addReference('product-1', $product1);
    }
}
