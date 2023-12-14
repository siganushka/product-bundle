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
        /** @var Product */
        $product1 = $this->getReference('product-1', Product::class);

        foreach ($product1->getOptionValueChoices() as $index => $optionValues) {
            $variant = new ProductVariant();
            $variant->setProduct($product1);
            $variant->setPrice(999);
            $variant->setInventory(100);
            $variant->setOptionValues($optionValues);

            $manager->persist($variant);

            $this->addReference('product-variant-'.$index, $variant);
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
