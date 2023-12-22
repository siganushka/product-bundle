<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Siganushka\ProductBundle\Entity\Option;
use Siganushka\ProductBundle\Entity\OptionValue;

class OptionFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $option1 = new Option();
        $option1->setName('颜色');
        $option1->addValue(new OptionValue(null, '蓝色'));
        $option1->addValue(new OptionValue(null, '绿色'));
        $option1->addValue(new OptionValue(null, '粉色'));

        $option2 = new Option();
        $option2->setName('存储');
        $option2->addValue(new OptionValue(null, '128GB'));
        $option2->addValue(new OptionValue(null, '256GB'));
        $option2->addValue(new OptionValue(null, '512GB'));

        $option3 = new Option();
        $option3->setName('辣度');
        $option3->addValue(new OptionValue(null, '不辣', 'https://placehold.jp/100x100.png'));
        $option3->addValue(new OptionValue(null, '微辣', 'https://placehold.jp/100x100.png'));
        $option3->addValue(new OptionValue(null, '中辣', 'https://placehold.jp/100x100.png'));
        $option3->addValue(new OptionValue(null, '特辣', 'https://placehold.jp/100x100.png'));
        $option3->addValue(new OptionValue(null, '变态辣', 'https://placehold.jp/100x100.png'));

        $manager->persist($option1);
        $manager->persist($option2);
        $manager->persist($option3);
        $manager->flush();

        $this->addReference('option-1', $option1);
        $this->addReference('option-2', $option2);
        $this->addReference('option-3', $option3);
    }
}
