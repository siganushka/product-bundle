<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;

class AddInventoryFieldListener implements EventSubscriberInterface
{
    public function formModifier(FormEvent $event): void
    {
        $form = $event->getForm();
        $isInventoryTracking = $event instanceof PostSubmitEvent ? $form->getData() : $event->getData();

        $parent = $form->getParent();
        if ($isInventoryTracking) {
            $parent->add('inventory', IntegerType::class, [
                'label' => 'product.variant.inventory',
                'constraints' => [
                    new NotBlank(),
                    new GreaterThanOrEqual(0),
                    new LessThanOrEqual(2147483647),
                ],
            ]);
        } else {
            $parent->remove('inventory');
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SET_DATA => 'formModifier',
            FormEvents::POST_SUBMIT => 'formModifier',
        ];
    }
}
