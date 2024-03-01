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
        $product0->setName('苹果 iPhone 15');
        $product0->setImg($this->getReference('media-5', Media::class));
        $product0->addOption($this->getReference('option-0', Option::class));
        $product0->addOption($this->getReference('option-1', Option::class));

        $product1 = new Product();
        $product1->setName('正宗陕西油泼面');
        $product1->setImg($this->getReference('media-6', Media::class));
        $product1->addOption($this->getReference('option-2', Option::class));

        $product2 = new Product();
        $product2->setName('迪卡侬保冷野餐包');
        $product2->setImg($this->getReference('media-7', Media::class));

        $product3 = new Product();
        $product3->setName('新品春季时尚卫衣');
        $product3->setImg($this->getReference('media-8', Media::class));
        $product3->addOption($this->getReference('option-0', Option::class));
        $product3->addOption($this->getReference('option-3', Option::class));

        $product4 = new Product();
        $product4->setName('耐克幼童易穿脱运动童鞋');
        $product4->setImg($this->getReference('media-9', Media::class));
        $product4->addOption($this->getReference('option-4', Option::class));

        $product5 = new Product();
        $product5->setName('New Balance 新品跑鞋女');
        $product5->setImg($this->getReference('media-10', Media::class));
        $product5->addOption($this->getReference('option-5', Option::class));

        $manager->persist($product0);
        $manager->persist($product1);
        $manager->persist($product2);
        $manager->persist($product3);
        $manager->persist($product4);
        $manager->persist($product5);
        $manager->flush();

        $this->addReference('product-0', $product0);
        $this->addReference('product-1', $product1);
        $this->addReference('product-2', $product2);
        $this->addReference('product-3', $product3);
        $this->addReference('product-4', $product4);
        $this->addReference('product-5', $product5);
    }

    public function getDependencies(): array
    {
        return [
            MediaFixtures::class,
            OptionFixtures::class,
        ];
    }
}
