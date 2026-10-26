<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form;

use Doctrine\Common\Collections\Collection;
use Siganushka\ProductBundle\Entity\AbstractProductVariant;
use Siganushka\ProductBundle\Repository\ProductRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ProductVariantCollectionType extends AbstractType
{
    public function __construct(private readonly ProductRepository $repository)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('variants', CollectionType::class, [
                'label' => false,
                'entry_type' => ProductVariantType::class,
                'entry_options' => ['label' => false],
                'allow_add' => false,
                'allow_delete' => false,
                'error_bubbling' => false,
                'by_reference' => false,
                'constraints' => new Callback($this->validateVariants(...)),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => $this->repository->getClassName(),
        ]);
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
