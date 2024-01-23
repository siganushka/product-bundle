<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form\EventListener;

use Siganushka\ProductBundle\Entity\Option;
use Siganushka\ProductBundle\Entity\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class AddProductOptionsListener implements EventSubscriberInterface
{
    public function onPreSetData(FormEvent $event): void
    {
        $data = $event->getData();
        if (!$data instanceof Product) {
            return;
        }

        $form = $event->getForm();
        $form->add('options', EntityType::class, [
            'label' => 'product.options',
            'class' => Option::class,
            'choice_label' => fn (Option $choice): string => (string) $choice,
            'disabled' => $data->getId() ? true : false,
            'multiple' => true,
            'expanded' => true,
        ]);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SET_DATA => 'onPreSetData',
        ];
    }
}
