<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Siganushka\Contracts\Doctrine\ResourceInterface;
use Siganushka\Contracts\Doctrine\ResourceTrait;
use Siganushka\ProductBundle\Repository\ProductOptionRepository;

/**
 * @ORM\Entity(repositoryClass=ProductOptionRepository::class)
 */
class ProductOption implements ResourceInterface
{
    use ResourceTrait;

    /**
     * @ORM\ManyToOne(targetEntity=Product::class, inversedBy="options")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?Product $product = null;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $name = null;

    /**
     * @ORM\OneToMany(targetEntity=ProductOptionValue::class, mappedBy="productOption", cascade={"all"})
     */
    private Collection $values;

    public function __construct()
    {
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

    public function setName(string $name): self
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
            $value->setProductOption($this);
        }

        return $this;
    }

    public function removeValue(ProductOptionValue $value): self
    {
        if ($this->values->removeElement($value)) {
            // set the owning side to null (unless already changed)
            if ($value->getProductOption() === $this) {
                $value->setProductOption(null);
            }
        }

        return $this;
    }
}
