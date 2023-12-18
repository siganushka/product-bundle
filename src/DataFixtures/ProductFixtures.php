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
        /** @var Option */
        $option1 = $this->getReference('option-1', Option::class);
        /** @var Option */
        $option2 = $this->getReference('option-2', Option::class);
        /** @var Option */
        $option3 = $this->getReference('option-3', Option::class);

        $product1 = new Product();
        $product1->setName('iPhone 15');
        $product1->addOption($option1);
        $product1->addOption($option2);

        $product2 = new Product();
        $product2->setName('正宗陕西油泼面（速食）');
        $product2->addOption($option3);

        $manager->persist($product1);
        $manager->persist($product2);
        $manager->flush();

        $this->addReference('product-1', $product1);
        $this->addReference('product-2', $product2);
    }

    public function getDependencies(): array
    {
        return [
            OptionFixtures::class,
        ];
    }
}
