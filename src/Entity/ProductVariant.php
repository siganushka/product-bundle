<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Siganushka\Contracts\Doctrine\ResourceInterface;
use Siganushka\Contracts\Doctrine\ResourceTrait;
use Siganushka\ProductBundle\Repository\ProductVariantRepository;

/**
 * @ORM\Entity(repositoryClass=ProductVariantRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class ProductVariant implements ResourceInterface
{
    use ResourceTrait;

    /**
     * @ORM\ManyToOne(targetEntity=Product::class, inversedBy="variants")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?Product $product = null;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $name = null;

    /**
     * @ORM\ManyToMany(targetEntity=ProductOptionValue::class, inversedBy="productVariants")
     * @ORM\JoinTable(name="product_variant_option_value")
     */
    private Collection $optionValues;

    public function __construct()
    {
        $this->optionValues = new ArrayCollection();
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
    public function getOptionValues(): Collection
    {
        return $this->optionValues;
    }

    public function setOptionValues(array $optionValues): self
    {
        $this->optionValues = new ArrayCollection($optionValues);

        return $this;
    }

    public function getOptionValuesText(): string
    {
        $texts = $this->optionValues->map(fn (ProductOptionValue $optionValue) => $optionValue->getText());

        return implode('/', $texts->toArray());
    }

    public function addOptionValue(ProductOptionValue $optionValue): self
    {
        if (!$this->optionValues->contains($optionValue)) {
            $this->optionValues[] = $optionValue;
        }

        return $this;
    }

    public function removeOptionValue(ProductOptionValue $optionValue): self
    {
        $this->optionValues->removeElement($optionValue);

        return $this;
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function setNameIfNotSet(): void
    {
        if ($this->name) {
            return;
        }

        if (!$this->product) {
            return;
        }

        $variantName = $this->product->getName();
        if (!$variantName) {
            return;
        }

        $optionValueTexts = $this->optionValues->map(fn (ProductOptionValue $optionValue) => $optionValue->getText());
        if ($optionValueTexts) {
            $variantName .= sprintf('（%s）', implode('/', $optionValueTexts->toArray()));
        }

        $this->name = $variantName;
    }
}
