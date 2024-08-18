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
        $previousValues = $event->getForm()->getData() ?? [];
        /** @var array<int, ProductOptionValue> */
        $newValues = $event->getData();

        foreach ($newValues as $key => $value) {
            $newValues[$key] = $this->getComparedValue($previousValues, $value);
        }

        $event->setData($newValues);
    }

    /**
     * @param iterable<int, ProductOptionValue> $previousValues
     */
    private function getComparedValue(iterable $previousValues, ProductOptionValue $newValue): ProductOptionValue
    {
        foreach ($previousValues as $value) {
            if ($value->getText() === $newValue->getText()) {
                return $value;
            }
        }

        return $newValue;
    }
}
