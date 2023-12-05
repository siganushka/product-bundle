<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Siganushka\ProductBundle\Entity\ProductOption;
use Siganushka\ProductBundle\Entity\ProductOptionValue;

class ProductOptionFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $product = $this->getReference('product');

        $option1 = new ProductOption();
        $option1->setProduct($product);
        $option1->setName('颜色');
        $option1->addValue(new ProductOptionValue('蓝色'));
        $option1->addValue(new ProductOptionValue('绿色'));
        $option1->addValue(new ProductOptionValue('粉色'));

        $option2 = new ProductOption();
        $option2->setProduct($product);
        $option2->setName('存储');
        $option2->addValue(new ProductOptionValue('128GB'));
        $option2->addValue(new ProductOptionValue('256GB'));
        $option2->addValue(new ProductOptionValue('512GB'));

        $manager->persist($option1);
        $manager->persist($option2);
        $manager->flush();

        $this->addReference('product-option-1', $option1);
        $this->addReference('product-option-2', $option2);
    }
}
