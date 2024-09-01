<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Siganushka\MediaBundle\ChannelRegistry;
use Siganushka\MediaBundle\Event\MediaSaveEvent;
use Siganushka\ProductBundle\Media\ProductImg;
use Siganushka\ProductBundle\Media\ProductOptionValueImg;
use Siganushka\ProductBundle\SiganushkaProductBundle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class MediaFixtures extends Fixture
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ChannelRegistry $channelRegistry,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $ref = new \ReflectionClass(SiganushkaProductBundle::class);
        $dir = \dirname($ref->getFileName());

        $mapping = [
            ProductImg::class => [
                $dir.'/DataFixtures/data/product-0.jpg',
                $dir.'/DataFixtures/data/product-1.jpg',
                $dir.'/DataFixtures/data/product-2.jpg',
                $dir.'/DataFixtures/data/product-3.jpg',
                $dir.'/DataFixtures/data/product-4.jpg',
                $dir.'/DataFixtures/data/product-5.jpg',
                $dir.'/DataFixtures/data/product-6.jpg',
                $dir.'/DataFixtures/data/product-7.jpg',
            ],
            ProductOptionValueImg::class => [
                $dir.'/DataFixtures/data/product-option-value-0.jpg',
                $dir.'/DataFixtures/data/product-option-value-1.jpg',
                $dir.'/DataFixtures/data/product-option-value-2.jpg',
                $dir.'/DataFixtures/data/product-option-value-3.jpg',
                $dir.'/DataFixtures/data/product-option-value-4.jpg',
            ],
        ];

        $index = 0;
        foreach ($mapping as $channelClass => $files) {
            $channel = $this->channelRegistry->getByClass($channelClass);
            foreach ($files as $file) {
                $target = \sprintf('%s/%s', sys_get_temp_dir(), pathinfo($file, \PATHINFO_BASENAME));

                $fs = new Filesystem();
                $fs->copy($file, $target);

                $event = MediaSaveEvent::createFromPath($channel, $target);
                $this->eventDispatcher->dispatch($event);

                $media = $event->getMedia();
                if (null === $media) {
                    continue;
                }

                $manager->persist($media);

                $this->addReference(\sprintf('media-%d', $index), $media);

                ++$index;
            }
        }

        $manager->flush();
    }
}
