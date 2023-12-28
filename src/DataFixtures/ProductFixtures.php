<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Siganushka\MediaBundle\Entity\Media;
use Siganushka\ProductBundle\Entity\Option;
use Siganushka\ProductBundle\Entity\Product;

class ProductFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $product0 = new Product();
        $product0->setName('iPhone 15');
        $product0->setImg($this->getReference('media-5', Media::class));
        $product0->addOption($this->getReference('option-0', Option::class));
        $product0->addOption($this->getReference('option-1', Option::class));

        $product1 = new Product();
        $product1->setName('正宗陕西油泼面');
        $product1->setImg($this->getReference('media-6', Media::class));
        $product1->addOption($this->getReference('option-2', Option::class));

        $manager->persist($product0);
        $manager->persist($product1);
        $manager->flush();

        $this->addReference('product-0', $product0);
        $this->addReference('product-1', $product1);
    }

    public function getDependencies(): array
    {
        return [
            MediaFixtures::class,
            OptionFixtures::class,
        ];
    }
}
