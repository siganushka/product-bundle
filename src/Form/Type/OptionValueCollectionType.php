<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form\Type;

use Siganushka\Contracts\Doctrine\SortableInterface;
use Siganushka\ProductBundle\Entity\OptionValue;
use Siganushka\ProductBundle\Form\OptionValueType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;

class OptionValueCollectionType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setNormalizer('entry_options', function (Options $options, array $entryOptions) {
            $entryOptions['block_prefix'] = sprintf('%s_entry', $options['block_prefix']);

            return $entryOptions;
        });

        $prototypeData = new OptionValue();
        $prototypeData->setSort(SortableInterface::DEFAULT_SORT);

        $resolver->setDefaults([
            'block_prefix' => 'siganushka_option_value_collection',
            'label' => 'option.values',
            'entry_type' => OptionValueType::class,
            'entry_options' => ['label' => false],
            'prototype_data' => $prototypeData,
            'allow_add' => true,
            'allow_delete' => true,
            'error_bubbling' => false,
            'by_reference' => false,
            'constraints' => new Count(['min' => 2, 'minMessage' => 'option.values.count.invalid']),
        ]);

        $resolver->setAllowedTypes('prototype_data', ['null', OptionValue::class]);
    }

    public function getParent()
    {
        return CollectionType::class;
    }
}
