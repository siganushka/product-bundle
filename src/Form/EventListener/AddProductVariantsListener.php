<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form\EventListener;

use Siganushka\ProductBundle\Entity\Product;
use Siganushka\ProductBundle\Entity\ProductVariant;
use Siganushka\ProductBundle\Form\ProductVariantType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class AddProductVariantsListener implements EventSubscriberInterface
{
    public function onPreSetData(FormEvent $event): void
    {
        $data = $event->getData();
        if (!$data instanceof Product) {
            return;
        }

        if (null === $data->getId()) {
            return;
        }

        $choices = $data->getVariantChoices();
        $choicesCount = \count($choices);

        $prototypeData = new ProductVariant();
        $prototypeData->setProduct($data);

        $form = $event->getForm();
        $form->add('variants', CollectionType::class, [
            'label' => 'product.variants',
            'entry_type' => ProductVariantType::class,
            'entry_options' => ['label' => false],
            'prototype_data' => $prototypeData,
            'allow_add' => true,
            'allow_delete' => true,
            'error_bubbling' => false,
            'by_reference' => false,
        ]);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SET_DATA => 'onPreSetData',
        ];
    }
}
