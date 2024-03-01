<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Siganushka\MediaBundle\Entity\Media;
use Siganushka\ProductBundle\Entity\Option;
use Siganushka\ProductBundle\Entity\OptionValue;

class OptionFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $option0 = new Option();
        $option0->setName('颜色');
        $option0->addValue(new OptionValue('蓝色'));
        $option0->addValue(new OptionValue('绿色'));
        $option0->addValue(new OptionValue('粉色'));

        $option1 = new Option();
        $option1->setName('存储');
        $option1->addValue(new OptionValue('128GB'));
        $option1->addValue(new OptionValue('256GB'));
        $option1->addValue(new OptionValue('512GB'));

        $option2 = new Option();
        $option2->setName('辣度');
        $option2->addValue(new OptionValue('不辣', null, $this->getReference('media-0', Media::class)));
        $option2->addValue(new OptionValue('微辣', null, $this->getReference('media-1', Media::class)));
        $option2->addValue(new OptionValue('中辣', null, $this->getReference('media-2', Media::class)));
        $option2->addValue(new OptionValue('特辣', null, $this->getReference('media-3', Media::class)));
        $option2->addValue(new OptionValue('变态辣', null, $this->getReference('media-4', Media::class)));

        $option3 = new Option();
        $option3->setName('尺码');
        $option3->addValue(new OptionValue('M', '建议90-120斤'));
        $option3->addValue(new OptionValue('L', '建议120-140斤'));
        $option3->addValue(new OptionValue('XL', '建议140-160斤'));
        $option3->addValue(new OptionValue('2XL', '建议160-180'));
        $option3->addValue(new OptionValue('3XL', '建议180-200斤'));

        $option4 = new Option();
        $option4->setName('尺码');
        $option4->addValue(new OptionValue('25', '内长约 17cm'));
        $option4->addValue(new OptionValue('26', '内长约 17.5cm'));
        $option4->addValue(new OptionValue('27', '内长约 18cm'));
        $option4->addValue(new OptionValue('28', '内长约 18.5cm'));
        $option4->addValue(new OptionValue('29', '内长约 19cm'));
        $option4->addValue(new OptionValue('30', '内长约 19.5cm'));
        $option4->addValue(new OptionValue('31', '内长约 20cm'));
        $option4->addValue(new OptionValue('32', '内长约 20.05cm'));

        $option5 = new Option();
        $option5->setName('尺码');
        $option5->addValue(new OptionValue('38'));
        $option5->addValue(new OptionValue('39'));
        $option5->addValue(new OptionValue('40'));
        $option5->addValue(new OptionValue('41'));
        $option5->addValue(new OptionValue('42'));
        $option5->addValue(new OptionValue('43'));
        $option5->addValue(new OptionValue('44'));
        $option5->addValue(new OptionValue('45'));

        $manager->persist($option0);
        $manager->persist($option1);
        $manager->persist($option2);
        $manager->persist($option3);
        $manager->persist($option4);
        $manager->persist($option5);
        $manager->flush();

        $this->addReference('option-0', $option0);
        $this->addReference('option-1', $option1);
        $this->addReference('option-2', $option2);
        $this->addReference('option-3', $option3);
        $this->addReference('option-4', $option4);
        $this->addReference('option-5', $option5);
    }

    public function getDependencies(): array
    {
        return [
            MediaFixtures::class,
        ];
    }
}
