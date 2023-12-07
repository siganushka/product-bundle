<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Siganushka\ProductBundle\Entity\ProductOption;
use Siganushka\ProductBundle\Entity\ProductVariant;

use function BenTools\CartesianProduct\cartesian_product;

class ProductVariantFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $product1 = $this->getReference('product-1');

        $options = $product1->getOptions();
        $values = $options->map(fn (ProductOption $option) => $option->getValues());

        foreach (cartesian_product($values->toArray()) as $index => $optionValues) {
            $variant = new ProductVariant();
            $variant->setProduct($product1);
            $variant->setOptionValues($optionValues);

            $manager->persist($variant);

            $this->addReference('product-variant-'.($index + 1), $variant);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ProductOptionFixtures::class,
        ];
    }
}
