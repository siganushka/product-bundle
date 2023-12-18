<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Siganushka\MediaBundle\ChannelRegistry;
use Siganushka\MediaBundle\Storage\StorageInterface;
use Siganushka\ProductBundle\Entity\Option;
use Siganushka\ProductBundle\Entity\OptionValue;
use Siganushka\ProductBundle\SiganushkaProductBundle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

class OptionFixtures extends Fixture
{
    private StorageInterface $storage;
    private ChannelRegistry $channelRegistry;

    public function __construct(StorageInterface $storage, ChannelRegistry $channelRegistry)
    {
        $this->storage = $storage;
        $this->channelRegistry = $channelRegistry;
    }

    public function load(ObjectManager $manager): void
    {
        $ref = new \ReflectionClass(SiganushkaProductBundle::class);

        $img1 = \dirname($ref->getFileName()).'/Resources/data/option-value-1.jpeg';
        $img2 = \dirname($ref->getFileName()).'/Resources/data/option-value-2.jpeg';
        $img3 = \dirname($ref->getFileName()).'/Resources/data/option-value-3.jpeg';
        $img4 = \dirname($ref->getFileName()).'/Resources/data/option-value-4.jpeg';
        $img5 = \dirname($ref->getFileName()).'/Resources/data/option-value-5.jpeg';

        $fs = new Filesystem();
        $fs->copy($img1, $img1 = \dirname($img1).'/'.uniqid());
        $fs->copy($img2, $img2 = \dirname($img2).'/'.uniqid());
        $fs->copy($img3, $img3 = \dirname($img3).'/'.uniqid());
        $fs->copy($img4, $img4 = \dirname($img4).'/'.uniqid());
        $fs->copy($img5, $img5 = \dirname($img5).'/'.uniqid());

        $img1 = new File($img1);
        $img2 = new File($img2);
        $img3 = new File($img3);
        $img4 = new File($img4);
        $img5 = new File($img5);

        $channel = $this->channelRegistry->get('generic');
        $mediaUrl1 = $this->storage->save($channel, $img1);
        $mediaUrl2 = $this->storage->save($channel, $img2);
        $mediaUrl3 = $this->storage->save($channel, $img3);
        $mediaUrl4 = $this->storage->save($channel, $img4);
        $mediaUrl5 = $this->storage->save($channel, $img5);

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
        $option3->addValue(new OptionValue(null, '不辣', $mediaUrl1));
        $option3->addValue(new OptionValue(null, '微辣', $mediaUrl2));
        $option3->addValue(new OptionValue(null, '中辣', $mediaUrl3));
        $option3->addValue(new OptionValue(null, '特辣', $mediaUrl4));
        $option3->addValue(new OptionValue(null, '变态辣', $mediaUrl5));

        $manager->persist($option1);
        $manager->persist($option2);
        $manager->persist($option3);
        $manager->flush();

        $this->addReference('option-1', $option1);
        $this->addReference('option-2', $option2);
        $this->addReference('option-3', $option3);
    }
}
