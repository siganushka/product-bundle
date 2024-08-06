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

#[ORM\Entity(repositoryClass: ProductOptionRepository::class)]
#[ORM\HasLifecycleCallbacks]
class ProductOption implements ResourceInterface, TimestampableInterface
{
    use ResourceTrait;
    use TimestampableTrait;

    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'options')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;

    #[ORM\Column]
    private ?string $name = null;

    /** @var Collection<int, ProductOptionValue> */
    #[ORM\OneToMany(targetEntity: ProductOptionValue::class, mappedBy: 'option', cascade: ['all'], orphanRemoval: true)]
    #[ORM\OrderBy(['createdAt' => 'ASC', 'id' => 'ASC'])]
    private Collection $values;

    public function __construct(string $name = null)
    {
        $this->name = $name;
        $this->values = new ArrayCollection();
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

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
     * @return Collection<int, ProductOptionValue>
     */
    public function getValues(): Collection
    {
        return $this->values;
    }

    public function addValue(ProductOptionValue $value): static
    {
        $fn = fn (int $_, ProductOptionValue $item): bool => $item->getText() === $value->getText();

        if (!$this->values->exists($fn)) {
            $this->values[] = $value;
            $value->setOption($this);
        }

        return $this;
    }

    public function removeValue(ProductOptionValue $value): static
    {
        if ($this->values->removeElement($value)) {
            if ($value->getOption() === $this) {
                $value->setOption(null);
            }
        }

        return $this;
    }

    #[ORM\PrePersist]
    public function assertNonEmptyValues(): void
    {
        if ($this->values->isEmpty()) {
            throw new \RuntimeException('The values cannot not be empty.');
        }
    }

    /**
     * @see https://www.doctrine-project.org/projects/doctrine-orm/en/2.13/cookbook/implementing-wakeup-or-clone.html
     */
    public function __clone()
    {
        $previousValues = $this->values;

        $this->values = new ArrayCollection();
        $previousValues->map(fn (ProductOptionValue $value) => $this->addValue(clone $value));

        unset($previousValues);
    }
}
