<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Siganushka\ProductBundle\Entity\Product;
use Siganushka\ProductBundle\Entity\ProductVariant;

class ProductVariantFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var array<int, Product> */
        $products = [
            $this->getReference('product-0', Product::class),
            $this->getReference('product-1', Product::class),
            $this->getReference('product-2', Product::class),
            $this->getReference('product-3', Product::class),
            $this->getReference('product-4', Product::class),
            $this->getReference('product-5', Product::class),
        ];

        $prices = [100, 200, 300, 400, 500];
        foreach ($products as $index => $product) {
            foreach ($product->getCombinedOptionValues() as $index2 => $optionValues) {
                $variant = new ProductVariant();
                $variant->setProduct($product);
                $variant->setPrice($prices[array_rand($prices)]);
                $variant->setInventory(100);
                $variant->setOptionValues($optionValues);
                $manager->persist($variant);

                $this->addReference(sprintf('product-%d-variant-%d', $index, $index2), $variant);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ProductFixtures::class,
        ];
    }
}
