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
 * @ORM\Entity(repositoryClass=ProductOptionRepository::class)
 */
class ProductOption implements ResourceInterface, TimestampableInterface
{
    use ResourceTrait;
    use TimestampableTrait;

    /**
     * @ORM\ManyToOne(targetEntity=Product::class, inversedBy="options")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?Product $product = null;

    /**
     * @ORM\Column(type="string")
     */
    private ?string $name = null;

    /**
     * @ORM\OneToMany(targetEntity=ProductOptionValue::class, mappedBy="option", cascade={"all"}, orphanRemoval=true)
     * @ORM\OrderBy({"createdAt": "ASC", "id": "ASC"})
     *
     * @var Collection<int, ProductOptionValue>
     */
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

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
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

    public function addValue(ProductOptionValue $value): self
    {
        if (!$this->values->contains($value)) {
            $this->values[] = $value;
            $value->setOption($this);
        }

        return $this;
    }

    public function removeValue(ProductOptionValue $value): self
    {
        if ($this->values->removeElement($value)) {
            if ($value->getOption() === $this) {
                $value->setOption(null);
            }
        }

        return $this;
    }

    public function __clone()
    {
        $this->values->map(fn (ProductOptionValue $value) => $this->addValue(clone $value));
    }
}
