<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Siganushka\Contracts\Doctrine\ResourceInterface;
use Siganushka\Contracts\Doctrine\ResourceTrait;
use Siganushka\ProductBundle\Repository\ProductOptionValueRepository;

/**
 * @ORM\Entity(repositoryClass=ProductOptionValueRepository::class)
 */
class ProductOptionValue implements ResourceInterface
{
    use ResourceTrait;

    /**
     * @ORM\ManyToOne(targetEntity=ProductOption::class, inversedBy="values")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?ProductOption $productOption = null;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $text = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $img = null;

    /**
     * @ORM\ManyToMany(targetEntity=ProductVariant::class, mappedBy="optionValues")
     */
    private Collection $productVariants;

    public function __construct(string $text = null, string $img = null)
    {
        $this->text = $text;
        $this->img = $img;
        $this->productVariants = new ArrayCollection();
    }

    public function getProductOption(): ?ProductOption
    {
        return $this->productOption;
    }

    public function setProductOption(?ProductOption $productOption): self
    {
        $this->productOption = $productOption;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getImg(): ?string
    {
        return $this->img;
    }

    public function setImg(?string $img): self
    {
        $this->img = $img;

        return $this;
    }

    /**
     * @return Collection<int, ProductVariant>
     */
    public function getProductVariants(): Collection
    {
        return $this->productVariants;
    }

    public function addProductVariant(ProductVariant $productVariant): self
    {
        if (!$this->productVariants->contains($productVariant)) {
            $this->productVariants[] = $productVariant;
            $productVariant->addOptionValue($this);
        }

        return $this;
    }

    public function removeProductVariant(ProductVariant $productVariant): self
    {
        if ($this->productVariants->removeElement($productVariant)) {
            $productVariant->removeOptionValue($this);
        }

        return $this;
    }
}
