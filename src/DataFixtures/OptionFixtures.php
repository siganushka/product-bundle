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
        $option1->addValue(new OptionValue('blue', '蓝色'));
        $option1->addValue(new OptionValue('green', '绿色'));
        $option1->addValue(new OptionValue('pick', '粉色'));

        $option2 = new Option();
        $option2->setName('存储');
        $option2->addValue(new OptionValue('128gb', '128GB'));
        $option2->addValue(new OptionValue('256gb', '256GB'));
        $option2->addValue(new OptionValue('512gb', '512GB'));

        $manager->persist($option1);
        $manager->persist($option2);
        $manager->flush();

        $this->addReference('option-1', $option1);
        $this->addReference('option-2', $option2);
    }
}
