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
        $option0 = new ProductOption('颜色');
        $option0->addValue(new ProductOptionValue('blue', '蓝色'));
        $option0->addValue(new ProductOptionValue('pink', '粉色'));
        $option0->addValue(new ProductOptionValue('yellow', '黄色'));
        $option0->addValue(new ProductOptionValue('green', '绿色'));
        $option0->addValue(new ProductOptionValue('black', '黑色'));

        $option1 = new ProductOption('存储');
        $option1->addValue(new ProductOptionValue('128gb', '128GB'));
        $option1->addValue(new ProductOptionValue('256gb', '256GB'));
        $option1->addValue(new ProductOptionValue('512gb', '512GB'));

        $option2 = new ProductOption('颜色');
        $option2->addValue(new ProductOptionValue(null, '原色钛金属'));
        $option2->addValue(new ProductOptionValue(null, '蓝色钛金属'));
        $option2->addValue(new ProductOptionValue(null, '白色钛金属'));
        $option2->addValue(new ProductOptionValue(null, '黑色钛金属'));

        $option3 = new ProductOption('存储');
        $option3->addValue(new ProductOptionValue(null, '256GB'));
        $option3->addValue(new ProductOptionValue(null, '512GB'));
        $option3->addValue(new ProductOptionValue(null, '1TB'));

        $option4 = new ProductOption('存储');
        $option4->addValue(new ProductOptionValue(null, '8GB+256GB'));
        $option4->addValue(new ProductOptionValue(null, '8GB+512GB'));
        $option4->addValue(new ProductOptionValue(null, '12GB+512GB'));

        $option5 = new ProductOption('颜色');
        $option5->addValue(new ProductOptionValue(null, '秘矿紫'));
        $option5->addValue(new ProductOptionValue(null, '浅珀黄'));
        $option5->addValue(new ProductOptionValue(null, '水墨黑'));
        $option5->addValue(new ProductOptionValue(null, '雅岩灰'));

        $option6 = new ProductOption('存储');
        $option6->addValue(new ProductOptionValue(null, '12GB+256GB'));
        $option6->addValue(new ProductOptionValue(null, '12GB+512GB'));
        $option6->addValue(new ProductOptionValue(null, '12GB+1TB'));

        $option7 = new ProductOption('颜色');
        $option7->addValue(new ProductOptionValue(null, '钛灰'));
        $option7->addValue(new ProductOptionValue(null, '钛黑'));
        $option7->addValue(new ProductOptionValue(null, '钛暮紫'));
        $option7->addValue(new ProductOptionValue(null, '钛羽黄'));

        $option8 = new ProductOption('尺码');
        $option8->addValue(new ProductOptionValue(null, '25', '内长约 17cm'));
        $option8->addValue(new ProductOptionValue(null, '26', '内长约 17.5cm'));
        $option8->addValue(new ProductOptionValue(null, '27', '内长约 18cm'));
        $option8->addValue(new ProductOptionValue(null, '28', '内长约 18.5cm'));
        $option8->addValue(new ProductOptionValue(null, '29', '内长约 19cm'));
        $option8->addValue(new ProductOptionValue(null, '30', '内长约 19.5cm'));
        $option8->addValue(new ProductOptionValue(null, '31', '内长约 20cm'));
        $option8->addValue(new ProductOptionValue(null, '32', '内长约 20.05cm'));

        $option9 = new ProductOption('尺码');
        $option9->addValue(new ProductOptionValue(null, 'M', '建议90-120斤'));
        $option9->addValue(new ProductOptionValue(null, 'L', '建议120-140斤'));
        $option9->addValue(new ProductOptionValue(null, 'XL', '建议140-160斤'));
        $option9->addValue(new ProductOptionValue(null, '2XL', '建议160-180'));
        $option9->addValue(new ProductOptionValue(null, '3XL', '建议180-200斤'));

        $option10 = new ProductOption('辣度');
        $option10->addValue(new ProductOptionValue(null, '不辣', null, $this->getReference('media-8', Media::class)));
        $option10->addValue(new ProductOptionValue(null, '微辣', null, $this->getReference('media-9', Media::class)));
        $option10->addValue(new ProductOptionValue(null, '中辣', null, $this->getReference('media-10', Media::class)));
        $option10->addValue(new ProductOptionValue(null, '特辣', null, $this->getReference('media-11', Media::class)));
        $option10->addValue(new ProductOptionValue(null, '变态辣', null, $this->getReference('media-12', Media::class)));

        $product0 = new Product();
        $product0->setName('苹果 iPhone 15');
        $product0->setImg($this->getReference('media-0', Media::class));
        $product0->addOption($option0);
        $product0->addOption($option1);

        $product1 = new Product();
        $product1->setName('苹果 iPhone 15 Plus');
        $product1->setImg($this->getReference('media-0', Media::class));
        $product1->addOption(clone $option0);
        $product1->addOption(clone $option1);

        $product2 = new Product();
        $product2->setName('苹果 iPhone 15 Pro');
        $product2->setImg($this->getReference('media-1', Media::class));
        $product2->addOption($option2);
        $product2->addOption($option3);

        $product3 = new Product();
        $product3->setName('苹果 iPhone 15 Pro Max');
        $product3->setImg($this->getReference('media-1', Media::class));
        $product3->addOption(clone $option2);
        $product3->addOption(clone $option3);

        $product4 = new Product();
        $product4->setName('三星 S24');
        $product4->setImg($this->getReference('media-2', Media::class));
        $product4->addOption($option4);
        $product4->addOption($option5);

        $product5 = new Product();
        $product5->setName('三星 S24+');
        $product5->setImg($this->getReference('media-2', Media::class));
        $product5->addOption(clone $option4);
        $product5->addOption(clone $option5);

        $product6 = new Product();
        $product6->setName('三星 S24 Ultra');
        $product6->setImg($this->getReference('media-3', Media::class));
        $product6->addOption($option6);
        $product6->addOption($option7);

        $product7 = new Product();
        $product7->setName('耐克幼童易穿脱运动童鞋');
        $product7->setImg($this->getReference('media-4', Media::class));
        $product7->addOption($option8);

        $product8 = new Product();
        $product8->setName('新品春季时尚卫衣');
        $product8->setImg($this->getReference('media-5', Media::class));
        $product8->addOption($option9);

        $product9 = new Product();
        $product9->setName('正宗陕西油泼面');
        $product9->setImg($this->getReference('media-6', Media::class));
        $product9->addOption($option10);

        $product10 = new Product();
        $product10->setName('迪卡侬保冷野餐包');
        $product10->setImg($this->getReference('media-7', Media::class));

        $manager->persist($product0);
        $manager->persist($product1);
        $manager->persist($product2);
        $manager->persist($product3);
        $manager->persist($product4);
        $manager->persist($product5);
        $manager->persist($product6);
        $manager->persist($product7);
        $manager->persist($product8);
        $manager->persist($product9);
        $manager->persist($product10);
        $manager->flush();

        $this->addReference('product-0', $product0);
        $this->addReference('product-1', $product1);
        $this->addReference('product-2', $product2);
        $this->addReference('product-3', $product3);
        $this->addReference('product-4', $product4);
        $this->addReference('product-5', $product5);
        $this->addReference('product-6', $product6);
        $this->addReference('product-7', $product7);
        $this->addReference('product-8', $product8);
        $this->addReference('product-9', $product9);
        $this->addReference('product-10', $product10);
    }

    public function getDependencies(): array
    {
        return [
            MediaFixtures::class,
        ];
    }
}
