<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Siganushka\MediaBundle\Event\MediaSaveEvent;
use Siganushka\MediaBundle\Form\DataTransformer\ChannelToAliasTransformer;
use Siganushka\ProductBundle\Media\ProductImg;
use Siganushka\ProductBundle\Media\ProductOptionValueImg;
use Siganushka\ProductBundle\SiganushkaProductBundle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class MediaFixtures extends Fixture
{
    private EventDispatcherInterface $eventDispatcher;
    private ChannelToAliasTransformer $transformer;

    public function __construct(EventDispatcherInterface $eventDispatcher, ChannelToAliasTransformer $transformer)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->transformer = $transformer;
    }

    public function load(ObjectManager $manager): void
    {
        $ref = new \ReflectionClass(SiganushkaProductBundle::class);
        $dir = \dirname($ref->getFileName());

        $mapping = [
            ProductImg::class => [
                $dir.'/Resources/data/product-0.jpg',
                $dir.'/Resources/data/product-1.jpg',
                $dir.'/Resources/data/product-2.jpg',
                $dir.'/Resources/data/product-3.jpg',
                $dir.'/Resources/data/product-4.jpg',
                $dir.'/Resources/data/product-5.jpg',
                $dir.'/Resources/data/product-6.jpg',
                $dir.'/Resources/data/product-7.jpg',
            ],
            ProductOptionValueImg::class => [
                $dir.'/Resources/data/product-option-value-0.jpg',
                $dir.'/Resources/data/product-option-value-1.jpg',
                $dir.'/Resources/data/product-option-value-2.jpg',
                $dir.'/Resources/data/product-option-value-3.jpg',
                $dir.'/Resources/data/product-option-value-4.jpg',
            ],
        ];

        $index = 0;
        foreach ($mapping as $channelAlias => $files) {
            $channel = $this->transformer->reverseTransform($channelAlias);
            foreach ($files as $file) {
                $target = sprintf('%s/%s', sys_get_temp_dir(), pathinfo($file, \PATHINFO_BASENAME));

                $fs = new Filesystem();
                $fs->copy($file, $target);

                $event = new MediaSaveEvent($channel, new File($target));
                $this->eventDispatcher->dispatch($event);

                $media = $event->getMedia();
                $manager->persist($media);

                $this->addReference(sprintf('media-%d', $index), $media);

                ++$index;
            }
        }

        $manager->flush();
    }
}
