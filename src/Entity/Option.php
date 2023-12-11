<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Faker\Guesser\Name;
use Siganushka\Contracts\Doctrine\ResourceInterface;
use Siganushka\Contracts\Doctrine\ResourceTrait;
use Siganushka\ProductBundle\Repository\OptionRepository;

/**
 * @ORM\Entity(repositoryClass=OptionRepository::class)
 * @ORM\Table(name="`option`")
 */
class Option implements ResourceInterface
{
    use ResourceTrait;

    /**
     * @ORM\Column(type="string")
     */
    private ?string $name = null;

    /**
     * @ORM\OneToMany(targetEntity=OptionValue::class, mappedBy="option", cascade={"all"})
     */
    private Collection $values;

    /**
     * @ORM\ManyToMany(targetEntity=Product::class, mappedBy="options")
     */
    private Collection $products;

    public function __construct()
    {
        $this->values = new ArrayCollection();
        $this->products = new ArrayCollection();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, OptionValue>
     */
    public function getValues(): Collection
    {
        return $this->values;
    }

    public function getValuesAsString(): string
    {
        return implode('/', $this->values->map(fn (OptionValue $optionValue) => (string) $optionValue)->toArray());
    }

    public function addValue(OptionValue $value): self
    {
        if (!$this->values->contains($value)) {
            $this->values[] = $value;
            $value->setOption($this);
        }

        return $this;
    }

    public function removeValue(OptionValue $value): self
    {
        if ($this->values->removeElement($value)) {
            if ($value->getOption() === $this) {
                $value->setOption(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): self
    {
        if (!$this->products->contains($product)) {
            $this->products[] = $product;
            $product->addOption($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): self
    {
        if ($this->products->removeElement($product)) {
            $product->removeOption($this);
        }

        return $this;
    }

    public function __toString(): string
    {
        if ($this->values->isEmpty()) {
            return $this->name;
        }

        return sprintf(
            '%s (%s)',
            $this->name,
            implode('/', array_map(fn (OptionValue $value) => (string) $value, $this->values->toArray())),
        );
    }
}
