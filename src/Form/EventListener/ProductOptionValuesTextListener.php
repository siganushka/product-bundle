<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form\EventListener;

use Siganushka\ProductBundle\Entity\AbstractProductOptionValue;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ProductOptionValuesTextListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [FormEvents::SUBMIT => 'onSubmit'];
    }

    public function onSubmit(FormEvent $event): void
    {
        /** @var array<int, AbstractProductOptionValue> */
        $previousData = $event->getForm()->getNormData() ?? [];
        /** @var array<int, AbstractProductOptionValue> */
        $newData = $event->getData();

        foreach ($newData as $key => $value) {
            foreach ($previousData as $previousValue) {
                if ($previousValue->getName() === $value->getName()) {
                    $newData[$key] = $previousValue;
                    break;
                }
            }
        }

        $event->setData($newData);
    }
}
