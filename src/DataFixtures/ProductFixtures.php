<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Siganushka\MediaBundle\Entity\Media;
use Siganushka\ProductBundle\Entity\Product;
use Siganushka\ProductBundle\Entity\ProductOption;
use Siganushka\ProductBundle\Entity\ProductOptionValue;

class ProductFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $option0 = new ProductOption();
        $option0->setName('颜色');
        $option0->addValue(new ProductOptionValue('蓝色'));
        $option0->addValue(new ProductOptionValue('绿色'));
        $option0->addValue(new ProductOptionValue('粉色'));

        $option1 = new ProductOption();
        $option1->setName('存储');
        $option1->addValue(new ProductOptionValue('128GB'));
        $option1->addValue(new ProductOptionValue('256GB'));
        $option1->addValue(new ProductOptionValue('512GB'));

        $option2 = new ProductOption();
        $option2->setName('辣度');
        $option2->addValue(new ProductOptionValue('不辣', null, $this->getReference('media-0', Media::class)));
        $option2->addValue(new ProductOptionValue('微辣', null, $this->getReference('media-1', Media::class)));
        $option2->addValue(new ProductOptionValue('中辣', null, $this->getReference('media-2', Media::class)));
        $option2->addValue(new ProductOptionValue('特辣', null, $this->getReference('media-3', Media::class)));
        $option2->addValue(new ProductOptionValue('变态辣', null, $this->getReference('media-4', Media::class)));

        $option3 = new ProductOption();
        $option3->setName('尺码');
        $option3->addValue(new ProductOptionValue('M', '建议90-120斤'));
        $option3->addValue(new ProductOptionValue('L', '建议120-140斤'));
        $option3->addValue(new ProductOptionValue('XL', '建议140-160斤'));
        $option3->addValue(new ProductOptionValue('2XL', '建议160-180'));
        $option3->addValue(new ProductOptionValue('3XL', '建议180-200斤'));

        $option4 = new ProductOption();
        $option4->setName('尺码');
        $option4->addValue(new ProductOptionValue('25', '内长约 17cm'));
        $option4->addValue(new ProductOptionValue('26', '内长约 17.5cm'));
        $option4->addValue(new ProductOptionValue('27', '内长约 18cm'));
        $option4->addValue(new ProductOptionValue('28', '内长约 18.5cm'));
        $option4->addValue(new ProductOptionValue('29', '内长约 19cm'));
        $option4->addValue(new ProductOptionValue('30', '内长约 19.5cm'));
        $option4->addValue(new ProductOptionValue('31', '内长约 20cm'));
        $option4->addValue(new ProductOptionValue('32', '内长约 20.05cm'));

        $option5 = new ProductOption();
        $option5->setName('尺码');
        $option5->addValue(new ProductOptionValue('38'));
        $option5->addValue(new ProductOptionValue('39'));
        $option5->addValue(new ProductOptionValue('40'));
        $option5->addValue(new ProductOptionValue('41'));
        $option5->addValue(new ProductOptionValue('42'));
        $option5->addValue(new ProductOptionValue('43'));
        $option5->addValue(new ProductOptionValue('44'));
        $option5->addValue(new ProductOptionValue('45'));

        $product0 = new Product();
        $product0->setName('苹果 iPhone 15');
        $product0->setImg($this->getReference('media-0', Media::class));
        $product0->addOption($option0);
        $product0->addOption($option1);

        $product1 = new Product();
        $product1->setName('正宗陕西油泼面');
        $product1->setImg($this->getReference('media-1', Media::class));
        $product1->addOption($option2);

        $product2 = new Product();
        $product2->setName('迪卡侬保冷野餐包');
        $product2->setImg($this->getReference('media-2', Media::class));

        $product3 = new Product();
        $product3->setName('新品春季时尚卫衣');
        $product3->setImg($this->getReference('media-3', Media::class));
        $product3->addOption($option3);

        $product4 = new Product();
        $product4->setName('耐克幼童易穿脱运动童鞋');
        $product4->setImg($this->getReference('media-4', Media::class));
        $product4->addOption($option4);

        $product5 = new Product();
        $product5->setName('新百伦 (New Balance) 跑鞋女');
        $product5->setImg($this->getReference('media-5', Media::class));
        $product5->addOption($option5);

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
        ];
    }
}
