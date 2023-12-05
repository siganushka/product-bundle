<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Siganushka\ProductBundle\Entity\ProductOption;
use Siganushka\ProductBundle\Entity\ProductVariant;

use function BenTools\CartesianProduct\cartesian_product;

class ProductVariantFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $product = $this->getReference('product');

        $options = $product->getOptions();
        $values = $options->map(fn (ProductOption $option) => $option->getValues());

        foreach (cartesian_product($values->toArray()) as $optionValues) {
            $variant = new ProductVariant();
            $variant->setProduct($product);
            $variant->setOptionValues($optionValues);

            $manager->persist($variant);

            $this->addReference('product-variant', $variant);
        }

        $manager->flush();
    }
}
