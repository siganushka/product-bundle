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
        $choices = $product1->getVariantsChoices();

        foreach ($choices as $key => $optionValues) {
            $variant = new ProductVariant();
            $variant->setProduct($product1);
            $variant->setPrice(random_int(100, 999));
            $variant->setInventory(100);

            array_walk($optionValues, [$variant, 'addOptionValue']);

            $manager->persist($variant);

            $this->addReference('product-variant-'.$key, $variant);
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
