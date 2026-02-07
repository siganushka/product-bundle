<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form\EventListener;

use Siganushka\ProductBundle\Entity\ProductOptionValue;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Event\SubmitEvent;
use Symfony\Component\Form\FormEvents;

class ProductOptionValuesTextListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [FormEvents::SUBMIT => 'onSubmit'];
    }

    public function onSubmit(SubmitEvent $event): void
    {
        /** @var array<int, ProductOptionValue> */
        $previousData = $event->getForm()->getNormData() ?? [];
        /** @var array<int, ProductOptionValue> */
        $newData = $event->getData();

        foreach ($newData as $key => $value) {
            foreach ($previousData as $previousValue) {
                if ($previousValue->getText() === $value->getText()) {
                    $newData[$key] = $previousValue;
                    break;
                }
            }
        }

        $event->setData($newData);
    }
}
