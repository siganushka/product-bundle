<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Siganushka\Contracts\Doctrine\ResourceInterface;
use Siganushka\Contracts\Doctrine\ResourceTrait;
use Siganushka\Contracts\Doctrine\SortableInterface;
use Siganushka\Contracts\Doctrine\SortableTrait;
use Siganushka\Contracts\Doctrine\TimestampableInterface;
use Siganushka\Contracts\Doctrine\TimestampableTrait;
use Siganushka\ProductBundle\Model\OptionValueCollection;
use Siganushka\ProductBundle\Repository\OptionRepository;

/**
 * @ORM\Entity(repositoryClass=OptionRepository::class)
 * @ORM\Table(name="`option`")
 */
class Option implements ResourceInterface, SortableInterface, TimestampableInterface
{
    use ResourceTrait;
    use SortableTrait;
    use TimestampableTrait;

    /**
     * @ORM\Column(type="string")
     */
    private ?string $name = null;

    /**
     * @ORM\OneToMany(targetEntity=OptionValue::class, mappedBy="option", cascade={"all"})
     * @ORM\OrderBy({"sorted": "DESC", "createdAt": "ASC", "id": "ASC"})
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
     * @return OptionValueCollection<int, OptionValue>
     */
    public function getValues(): OptionValueCollection
    {
        if ($this->values instanceof OptionValueCollection) {
            return $this->values;
        }

        return new OptionValueCollection($this->values->toArray());
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
            return (string) $this->name;
        }

        return sprintf(
            '%s (%s)',
            (string) $this->name,
            implode('/', $this->values->map(fn (OptionValue $value) => (string) $value)->toArray()),
        );
    }
}
