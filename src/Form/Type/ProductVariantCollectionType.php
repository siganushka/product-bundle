<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form\Type;

use Doctrine\Common\Collections\Collection;
use Siganushka\ProductBundle\Entity\AbstractProductVariant;
use Siganushka\ProductBundle\Form\ProductVariantType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ProductVariantCollectionType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'entry_type' => ProductVariantType::class,
            'entry_options' => ['label' => false],
            'allow_add' => false,
            'allow_delete' => false,
            'error_bubbling' => false,
            'by_reference' => false,
            'constraints' => new Callback($this->validateVariants(...)),
        ]);
    }

    public function getParent(): string
    {
        return CollectionType::class;
    }

    /**
     * @param Collection<int, AbstractProductVariant>|null $variants
     */
    private function validateVariants(?Collection $variants, ExecutionContextInterface $context): void
    {
        if (!$variants instanceof Collection) {
            return;
        }

        $enabled = $variants->filter(static fn (AbstractProductVariant $item) => $item->isEnabled());
        if (0 === $enabled->count()) {
            $context->buildViolation('At least one product variants has to be enabled.')
                ->addViolation()
            ;
        }
    }
}
