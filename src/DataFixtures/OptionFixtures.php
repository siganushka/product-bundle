<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Siganushka\ProductBundle\Entity\Option;
use Siganushka\ProductBundle\Entity\OptionValue;

class OptionFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $option0 = new Option();
        $option0->setName('颜色');
        $option0->addValue(new OptionValue(null, '蓝色'));
        $option0->addValue(new OptionValue(null, '绿色'));
        $option0->addValue(new OptionValue(null, '粉色'));

        $option1 = new Option();
        $option1->setName('存储');
        $option1->addValue(new OptionValue(null, '128GB'));
        $option1->addValue(new OptionValue(null, '256GB'));
        $option1->addValue(new OptionValue(null, '512GB'));

        $option2 = new Option();
        $option2->setName('辣度');
        $option2->addValue(new OptionValue(null, '不辣', $this->getReference('media-0')));
        $option2->addValue(new OptionValue(null, '微辣', $this->getReference('media-1')));
        $option2->addValue(new OptionValue(null, '中辣', $this->getReference('media-2')));
        $option2->addValue(new OptionValue(null, '特辣', $this->getReference('media-3')));
        $option2->addValue(new OptionValue(null, '变态辣', $this->getReference('media-4')));

        $manager->persist($option0);
        $manager->persist($option1);
        $manager->persist($option2);
        $manager->flush();

        $this->addReference('option-0', $option0);
        $this->addReference('option-1', $option1);
        $this->addReference('option-2', $option2);
    }

    public function getDependencies(): array
    {
        return [
            MediaFixtures::class,
        ];
    }
}
