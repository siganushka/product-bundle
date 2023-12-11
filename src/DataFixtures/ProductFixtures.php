<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Siganushka\ProductBundle\Entity\Option;
use Siganushka\ProductBundle\Entity\Product;

class ProductFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $option1 = $this->getReference('option-1', Option::class);
        $option2 = $this->getReference('option-2', Option::class);

        $product1 = new Product();
        $product1->setName('iPhone 15');
        $product1->addOption($option1);
        $product1->addOption($option2);

        $manager->persist($product1);
        $manager->flush();

        $this->addReference('product-1', $product1);
    }

    public function getDependencies(): array
    {
        return [
            OptionFixtures::class,
        ];
    }
}
