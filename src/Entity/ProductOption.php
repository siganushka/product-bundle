<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Siganushka\Contracts\Doctrine\ResourceInterface;
use Siganushka\Contracts\Doctrine\ResourceTrait;
use Siganushka\Contracts\Doctrine\TimestampableInterface;
use Siganushka\Contracts\Doctrine\TimestampableTrait;
use Siganushka\ProductBundle\Repository\ProductOptionRepository;

/**
 * @template TProduct of Product = Product
 * @template TValue of ProductOptionValue = ProductOptionValue
 */
#[ORM\Entity(repositoryClass: ProductOptionRepository::class)]
class ProductOption implements ResourceInterface, TimestampableInterface
{
    use ResourceTrait;
    use TimestampableTrait;

    /**
     * @var TProduct|null
     */
    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'options')]
    protected ?Product $product = null;

    #[ORM\Column]
    protected ?string $name = null;

    /**
     * @var Collection<int, TValue>
     */
    #[ORM\OneToMany(targetEntity: ProductOptionValue::class, mappedBy: 'option', cascade: ['all'], orphanRemoval: true)]
    #[ORM\OrderBy(['createdAt' => 'ASC', 'id' => 'ASC'])]
    protected Collection $values;

    public function __construct(?string $name = null)
    {
        $this->name = $name;
        $this->values = new ArrayCollection();
    }

    /**
     * @return TProduct|null
     */
    public function getProduct(): ?Product
    {
        return $this->product;
    }

    /**
     * @param TProduct|null $product
     */
    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, TValue>
     */
    public function getValues(): Collection
    {
        return $this->values;
    }

    /**
     * @param TValue $value
     */
    public function addValue(ProductOptionValue $value): static
    {
        if (!$this->values->contains($value)) {
            $this->values[] = $value;
            $value->setOption($this);
        }

        return $this;
    }

    /**
     * @param TValue $value
     */
    public function removeValue(ProductOptionValue $value): static
    {
        if ($this->values->removeElement($value)) {
            if ($value->getOption() === $this) {
                $value->setOption(null);
            }
        }

        return $this;
    }

    /**
     * @see https://www.doctrine-project.org/projects/doctrine-orm/en/2.13/cookbook/implementing-wakeup-or-clone.html
     */
    public function __clone(): void
    {
        $previousValues = $this->values;

        $this->id = null;
        $this->values = new ArrayCollection();
        $previousValues->map(fn (ProductOptionValue $value) => $this->addValue(clone $value));

        unset($previousValues);
    }
}
