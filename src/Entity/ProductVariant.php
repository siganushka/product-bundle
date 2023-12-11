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
     */
    private ?Product $product = null;

    /**
     * @ORM\Column(type="string")
     */
    private ?string $name = null;

    /**
     * @ORM\Column(type="integer")
     */
    private ?int $price = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $inventory = null;

    /**
     * @ORM\ManyToMany(targetEntity=OptionValue::class)
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

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getInventory(): ?int
    {
        return $this->inventory;
    }

    public function setInventory(int $inventory): self
    {
        $this->inventory = $inventory;

        return $this;
    }

    /**
     * @return Collection<int, OptionValue>
     */
    public function getOptionValues(): Collection
    {
        return $this->optionValues;
    }

    public function addOptionValue(OptionValue $optionValue): self
    {
        if (!$this->optionValues->contains($optionValue)) {
            $this->optionValues[] = $optionValue;
        }

        return $this;
    }

    public function removeOptionValue(OptionValue $optionValue): self
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

        $optionValueTexts = $this->optionValues->map(fn (OptionValue $optionValue) => $optionValue->getText());
        if (!$optionValueTexts->isEmpty()) {
            $variantName .= sprintf('（%s）', implode('/', $optionValueTexts->toArray()));
        }

        $this->name = $variantName;
    }
}
